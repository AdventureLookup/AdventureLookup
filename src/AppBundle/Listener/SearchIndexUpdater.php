<?php

namespace AppBundle\Listener;


use AppBundle\Entity\Adventure;
use AppBundle\Entity\HasAdventuresInterface;
use AppBundle\Service\AdventureSerializer;
use AppBundle\Service\ElasticSearch;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Psr\Log\LoggerInterface;

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

    public function __construct(ElasticSearch $elasticSearch)
    {
        $this->serializer = new AdventureSerializer();
        $this->client = $elasticSearch->getClient();
        $this->indexName = $elasticSearch->getIndexName();
        $this->typeName = $elasticSearch->getTypeName();
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
            'postPersist',
            'postUpdate',
            'postRemove',
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
            if ($entity instanceof HasAdventuresInterface) {
                $entity->getAdventures()->getValues();
            }
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
        $this->handleRemoval($args);
    }

    private function handleInsertOrUpdate(LifecycleEventArgs $args)
    {
        $adventures = $this->getAffectedAdventures($args);
        foreach ($adventures as $adventure) {
            $this->updateSearchIndexForAdventure($adventure);
        }
    }

    private function handleRemoval(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        // Only delete the search index if the entity is an adventure.
        // Otherwise reindex the adventure.
        if ($entity instanceof Adventure) {
            $this->deleteSearchIndexForAdventure($entity);
        } else {
            $this->handleInsertOrUpdate($args);
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
        if ($entity instanceof HasAdventuresInterface) {
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
        $this->client->index([
            'index' => $this->indexName,
            'type' => $this->typeName,
            'id' => $adventure->getId(),
            'body' => $this->serializer->toElasticDocument($adventure)
        ]);
    }

    /**
     * Deletes the search index for the given adventure.
     * Fails silently if the index is already deleted.
     *
     * @param Adventure $adventure
     */
    private function deleteSearchIndexForAdventure(Adventure $adventure)
    {
        try {
            $this->client->delete([
                'index' => $this->indexName,
                'type' => $this->typeName,
                'id' => $adventure->getId(),
            ]);
        } catch (Missing404Exception $e) {
            // Apparently already deleted.
        }
    }
}
