<?php

namespace AppBundle\Listener;


use AppBundle\Entity\Adventure;
use AppBundle\Entity\TagContent;
use AppBundle\Entity\TagName;
use AppBundle\Service\AdventureSerializer;
use AppBundle\Service\FieldUtils;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
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
        $entity = $args->getEntity();
        if ($entity instanceof TagName) {
            return $this->addMapping($entity);
        }
        return $this->updateSearchIndex($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof TagName) {
            return $this->removeMappingAndData($entity);
        }
        return $this->deleteSearchIndex($args);
    }

    private function updateSearchIndex(LifecycleEventArgs $args)
    {
        $adventure = $this->getAdventure($args);
        if (!$adventure) {
            return;
        }

        $this->update($adventure);
    }

    private function deleteSearchIndex(LifecycleEventArgs $args)
    {
        $adventure = $this->getAdventure($args);
        if (!$adventure || !$adventure->getId()) {
            return;
        }

        $client = ClientBuilder::create()->build();

        try {
            $response = $client->delete([
                'index' => self::INDEX,
                'type' => self::TYPE,
                'id' => $adventure->getId(),
            ]);
        } catch (Missing404Exception $e) {

        }

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

    /**
     * @param Adventure $adventure
     */
    public function update(Adventure $adventure)
    {
        $response = $this->client->index([
            'index' => self::INDEX,
            'type' => self::TYPE,
            'id' => $adventure->getId(),
            'body' => $this->serializer->toElasticDocument($adventure)
        ]);

        // @TODO: Log errors
    }

    private function addMapping(TagName $field)
    {
        $fieldUtils = new FieldUtils();

        $response = $this->client->indices()->putMapping([
            'index' => self::INDEX,
            'type' => self::TYPE,
            'body' => [
                'properties' => [
                    $fieldUtils->getFieldName($field) => $fieldUtils->generateMappingFor($field->getType())
                ]
            ]
        ]);

        // @TODO: Log errors
    }

    private function removeMappingAndData(TagName $field)
    {
        $fieldUtils = new FieldUtils();

        // Remove data
        $response = $this->client->updateByQuery([
            'index' => self::INDEX,
            'type' => self::TYPE,
            'body' => [
                'script' => [
                    'inline' => 'ctx._source.remove("' . $fieldUtils->getFieldName($field) . '")',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            'exists' => [
                                'field' => $fieldUtils->getFieldName($field)
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        // Remove mapping
        // Apparently, it is impossible to delete a mapping.
        // https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-delete-mapping.html

        // @TODO: Log errors
    }
}