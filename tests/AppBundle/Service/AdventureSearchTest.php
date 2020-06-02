<?php

namespace Tests\AppBundle\Service;

use AppBundle\Field\FieldProvider;
use AppBundle\Service\AdventureSearch;
use AppBundle\Service\ElasticSearch;
use AppBundle\Service\TimeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class AdventureSearchTest extends TestCase
{
    /**
     * @var AdventureSearch
     */
    private $search;

    /**
     * @var FieldProvider|MockObject
     */
    private $fieldProvider;

    /**
     * @var ElasticSearch|MockObject
     */
    private $elasticSearch;

    /**
     * @var TimeProvider|MockObject
     */
    private $timeProvider;

    public function setUp(): void
    {
        $this->fieldProvider = $this->createMock(FieldProvider::class);
        $this->elasticSearch = $this->createMock(ElasticSearch::class);
        $this->timeProvider = $this->createMock(TimeProvider::class);
        $this->timeProvider->method('millis')->willReturn(123);
        $this->search = new AdventureSearch($this->fieldProvider, $this->elasticSearch, $this->timeProvider);
    }

    public function testRequestToSearchParams()
    {
        $request = Request::create('');
        $this->assertEquals(['', [], 1, '', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?page=10');
        $this->assertEquals(['', [], 10, '', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?q=foo');
        $this->assertEquals(['foo', [], 1, '', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?f[edition][v]=DND&f[numPages][min]=2');
        $this->assertEquals(['', [
            'edition' => ['v' => 'DND'],
            'numPages' => ['min' => '2'],
        ], 1, '', '123'], $this->search->requestToSearchParams($request));

        // Invalid filter should not break anything
        $request = Request::create('/?f=2');
        $this->assertEquals(['', [], 1, '', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?sortBy=title');
        $this->assertEquals(['', [], 1, 'title', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?seed=foo');
        $this->assertEquals(['', [], 1, '', 'foo'], $this->search->requestToSearchParams($request));
    }
}
