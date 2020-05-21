<?php


namespace Tests\AppBundle\Service;

use AppBundle\Service\ElasticSearch;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ElasticSearchTest extends TestCase
{
    const INDEX_NAME = 'some_index';
    const TYPE_NAME = 'some_type';

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
        $clientBuilder->method('build')->willReturn($this->client);
        $config = [
            'index_name' => self::INDEX_NAME,
            'type_name' => self::TYPE_NAME,
        ];

        $this->elasticSearch = new ElasticSearch($clientBuilder, $logger, $config);
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
