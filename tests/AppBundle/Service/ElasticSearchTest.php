<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\ElasticSearch;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ElasticSearchTest extends TestCase
{
    const HOST = 'localhost:9200';
    const INDEX_NAME = 'some_index';

    /**
     * @var ElasticSearch
     */
    private $elasticSearch;

    /**
     * @var Client|\PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    public function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->client = $this->createMock(Client::class);
        $clientBuilder = $this->createMock(ClientBuilder::class);
        $clientBuilder
            ->expects($this->once())
            ->method('setLogger')
            ->with($logger)
            ->willReturnSelf();
        $clientBuilder
            ->expects($this->once())
            ->method('setHosts')
            ->with([self::HOST])
            ->willReturnSelf();
        $clientBuilder->method('build')->willReturn($this->client);
        $this->elasticSearch = new ElasticSearch($clientBuilder, $logger, self::HOST, self::INDEX_NAME);
    }

    public function testGetIndexName()
    {
        $this->assertSame(self::INDEX_NAME, $this->elasticSearch->getIndexName());
    }

    public function testGetClient()
    {
        $this->assertSame($this->client, $this->elasticSearch->getClient());
    }
}
