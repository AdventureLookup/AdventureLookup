<?php

namespace AppBundle\Listener;


use AppBundle\Entity\Adventure;
use AppBundle\Entity\RelatedEntityInterface;
use AppBundle\Service\AdventureSerializer;
use AppBundle\Service\ElasticSearch;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;

class SearchIndexUpdater implements EventSubscriber
{
    const INDEX = 'adventure';
    const TYPE = 'all';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var AdventureSerializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $indexName;

    /**
     * @var string
     */
    private $typeName;

    /**
     * @var int[]
     */
    private $adventureIdsToRemove;

    /**
     * If true, force immediate ElasticSearch refresh.
     * This is useful for tests, so they don't continue when the index isn't yet refreshed.
     * @var bool
     */
    private $isTestEnvironment;

    public function __construct(ElasticSearch $elasticSearch, AdventureSerializer $serializer, $environment)
    {
        $this->serializer = $serializer;
        $this->client = $elasticSearch->getClient();
        $this->indexName = $elasticSearch->getIndexName();
        $this->typeName = $elasticSearch->getTypeName();
        $this->adventureIdsToRemove = [];
        $this->isTestEnvironment = $environment === 'test';
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'preRemove',
            'postPersist',
            'postUpdate',
            'postRemove',
            'postFlush',
        ];
    }

    /**
     * Called right before the changes are flushed.
     *
     * Make sure to fetch the adventures for all associated entities which are going to be deleted.
     * We can't fetch them inside the postRemove handler, because the associated entities will have been removed
     * from the database at that point. Fetching them now by calling ->getValues() makes sure the
     * database is actually queried for the adventures and the collection isn't just proxying them.
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof RelatedEntityInterface) {
                $entity->getAdventures()->getValues();
            }
        }
    }

    /**
     * Keep track of all ids of adventures being removed. We need to save them for later, because the id of
     * a removed adventure is not available inside postRemove.
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Adventure) {
            $this->adventureIdsToRemove[] = $entity->getId();
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handleInsertOrUpdate($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->handleInsertOrUpdate($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Adventure) {
            // Do nothing - if this is an adventure being deleted, Doctrine sets its id to null.
            // We will delete the adventure in the postFlush listener - we saved the id in the preRemove listener.
        } else {
            // Don't delete the search index if the entity is not an adventure.
            // Simply reindex the adventure instead.
            $this->handleInsertOrUpdate($args);
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        foreach ($this->adventureIdsToRemove as $adventureId) {
            $this->deleteSearchIndexForAdventureId($adventureId);
        }
        // Make sure to reset the ids to remove in case another flush operation is following.
        $this->adventureIdsToRemove = [];
    }

    private function handleInsertOrUpdate(LifecycleEventArgs $args)
    {
        $adventures = $this->getAffectedAdventures($args);
        foreach ($adventures as $adventure) {
            if ($adventure->getId() === null) {
                // If the id is null, then this is a new related entity which references the main adventure which
                // doesn't yet have an id. We can simply skip it.
                continue;
            }
            $this->updateSearchIndexForAdventure($adventure);
        }
    }

    /**
     * Given the lifecycle event, find all adventures effected by it.
     *
     * @param LifecycleEventArgs $args
     * @return Adventure[]
     */
    private function getAffectedAdventures(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Adventure) {
            return [$entity];
        }
        if ($entity instanceof RelatedEntityInterface) {
            return $entity->getAdventures();
        }

        return [];
    }

    /**
     * Updates the search index for the given adventure.
     *
     * @param Adventure $adventure
     */
    public function updateSearchIndexForAdventure(Adventure $adventure)
    {
        $id = $adventure->getId();
        if (!is_numeric($id)) {
            throw new \RuntimeException('Trying to index an adventure without an id set! This should not have happened.');
        }
        $this->client->index([
            'index' => $this->indexName,
            'type' => $this->typeName,
            'id' => $id,
            'body' => $this->serializer->toElasticDocument($adventure),
            'refresh' => $this->isTestEnvironment,
        ]);
    }

    /**
     * Deletes the search index for the given adventure.
     * Fails silently if the index is already deleted.
     *
     * @param int $adventureId
     */
    private function deleteSearchIndexForAdventureId(int $adventureId)
    {
        try {
            $this->client->delete([
                'index' => $this->indexName,
                'type' => $this->typeName,
                'id' => $adventureId,
                'refresh' => $this->isTestEnvironment,
            ]);
        } catch (Missing404Exception $e) {
            // Apparently already deleted.
        }
    }
}
