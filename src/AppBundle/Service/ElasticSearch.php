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
     * @var Client
     */
    private $client;

    public function __construct(ClientBuilder $clientBuilder, LoggerInterface $logger, array $config)
    {
        $this->client = $clientBuilder->setLogger($logger)->build();
        $this->indexName = $config['index_name'];
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
