<?php


namespace AppBundle\Service;

use Elasticsearch\ClientBuilder;

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
     * @var \Elasticsearch\Client
     */
    private $client;

    public function __construct(array $config)
    {
        $this->indexName = $config['index_name'];
        $this->typeName = $config['type_name'];
        $this->client = ClientBuilder::create()->build();
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
     * @return \Elasticsearch\Client
     */
    public function getClient(): \Elasticsearch\Client
    {
        return $this->client;
    }
}
