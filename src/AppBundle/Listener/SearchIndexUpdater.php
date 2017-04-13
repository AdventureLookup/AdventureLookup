<?php

namespace AppBundle\Listener;


use AppBundle\Entity\Adventure;
use AppBundle\Entity\TagContent;
use AppBundle\Service\AdventureSerializer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->client = ClientBuilder::create()->build();
        $this->serializer = new AdventureSerializer();
        $this->logger = $logger;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postUpdate',
            'postPersist',
            'postRemove'
        ];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        return $this->updateSearchIndex($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        return $this->updateSearchIndex($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        return $this->deleteSearchIndex($args);
    }

    private function updateSearchIndex(LifecycleEventArgs $args)
    {
        $adventure = $this->getAdventure($args);
        if (!$adventure) {
            return;
        }

        $response = $this->client->index([
            'index' => self::INDEX,
            'type' => self::TYPE,
            'id' => $adventure->getId(),
            'body' => $this->serializer->toElasticDocument($adventure)
        ]);

        // @TODO: Log errors
    }

    private function deleteSearchIndex(LifecycleEventArgs $args)
    {
        $adventure = $this->getAdventure($args);
        if (!$adventure) {
            return;
        }

        $client = ClientBuilder::create()->build();

        $response = $client->delete([
            'index' => self::INDEX,
            'type' => self::TYPE,
            'id' => $adventure->getId(),
        ]);

        // @TODO: Log errors
    }

    /**
     * @param LifecycleEventArgs $args
     * @return Adventure|null|object
     */
    private function getAdventure(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!($entity instanceof TagContent) && (!$entity instanceof Adventure)) {
            return null;
        }
        if ($entity instanceof TagContent) {
            $entity = $entity->getAdventure();
        }
        return $entity;
    }
}