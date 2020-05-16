<?php


namespace AppBundle\Service;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

class ElasticSearch
{
    /**
     * @var string
     */
    private $indexName;

    /**
     * @var string
     */
    private $typeName;

    /**
     * @var Client
     */
    private $client;

    public function __construct(ClientBuilder $clientBuilder, LoggerInterface $logger, array $config)
    {
        $this->client = $clientBuilder->setLogger($logger)->build();
        $this->indexName = $config['index_name'];
        $this->typeName = $config['type_name'];
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->typeName;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
