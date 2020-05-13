<?php


namespace Tests\AppBundle\Service;

use AppBundle\Field\FieldProvider;
use AppBundle\Service\AdventureSearch;
use AppBundle\Service\ElasticSearch;
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

    public function setUp()
    {
        $this->fieldProvider = $this->createMock(FieldProvider::class);
        $this->elasticSearch = $this->createMock(ElasticSearch::class);
        $this->search = new AdventureSearch($this->fieldProvider, $this->elasticSearch);
    }

    public function testRequestToSearchParams()
    {
        $request = Request::create("");
        $this->assertEquals(["", [], 1, ""], $this->search->requestToSearchParams($request));

        $request = Request::create("/?page=10");
        $this->assertEquals(["", [], 10, ""], $this->search->requestToSearchParams($request));

        $request = Request::create("/?q=foo");
        $this->assertEquals(["foo", [], 1, ""], $this->search->requestToSearchParams($request));

        $request = Request::create("/?f[edition][v]=DND&f[numPages][min]=2");
        $this->assertEquals(["", [
            "edition" => ["v" => "DND"],
            "numPages" => ["min" => "2"]
        ], 1, ""], $this->search->requestToSearchParams($request));

        // Invalid filter should not break anything
        $request = Request::create("/?f=2");
        $this->assertEquals(["", [], 1, ""], $this->search->requestToSearchParams($request));

        $request = Request::create("/?sortBy=title");
        $this->assertEquals(["", [], 1, "title"], $this->search->requestToSearchParams($request));
    }
}
