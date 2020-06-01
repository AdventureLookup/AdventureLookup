<?php

namespace Tests\AppBundle\Service;

use AppBundle\Field\Field;
use AppBundle\Field\FieldProvider;
use AppBundle\Service\AdventureSearch;
use AppBundle\Service\ElasticSearch;
use AppBundle\Service\TimeProvider;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function setUp()
    {
        $this->fieldProvider = $this->createMock(FieldProvider::class);
        $this->fieldProvider->method('getFields')->willReturn(new ArrayCollection([
            new Field('numPages', 'integer', false, false, ''),
            new Field('soloable', 'boolean', false, false, ''),
            new Field('edition', 'string', false, false, ''),
        ]));
        $this->elasticSearch = $this->createMock(ElasticSearch::class);
        $this->timeProvider = $this->createMock(TimeProvider::class);
        $this->timeProvider->method('millis')->willReturn(123);
        $this->search = new AdventureSearch($this->fieldProvider, $this->elasticSearch, $this->timeProvider);
    }

    public function testRequestToSearchParams()
    {
        $EMPTY_NUM_PAGES = [
            'v' => [
                'min' => '',
                'max' => '',
            ],
        ];
        $EMPTY_SOLOABLE = [
            'v' => '',
        ];
        $EMPTY_EDITION = [
            'v' => [],
        ];

        $request = Request::create('');
        $this->assertEquals([
            '',
            ['numPages' => $EMPTY_NUM_PAGES, 'soloable' => $EMPTY_SOLOABLE, 'edition' => $EMPTY_EDITION],
            1,
            '',
            '123',
        ], $this->search->requestToSearchParams($request));

        $request = Request::create('/?page=10');
        $this->assertEquals(['', ['numPages' => $EMPTY_NUM_PAGES, 'soloable' => $EMPTY_SOLOABLE, 'edition' => $EMPTY_EDITION], 10, '', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?q=foo');
        $this->assertEquals(['foo', ['numPages' => $EMPTY_NUM_PAGES, 'soloable' => $EMPTY_SOLOABLE, 'edition' => $EMPTY_EDITION], 1, '', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?numPages-min=2&edition=foo&soloable=1');
        $this->assertEquals(['', [
            'numPages' => ['v' => ['min' => '2', 'max' => '']],
            'soloable' => ['v' => '1'],
            'edition' => ['v' => ['foo']],
        ], 1, '', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?soloable=ok');
        $this->assertEquals(['', ['numPages' => $EMPTY_NUM_PAGES, 'soloable' => $EMPTY_SOLOABLE, 'edition' => $EMPTY_EDITION], 1, '', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?sortBy=title');
        $this->assertEquals(['', ['numPages' => $EMPTY_NUM_PAGES, 'soloable' => $EMPTY_SOLOABLE, 'edition' => $EMPTY_EDITION], 1, 'title', '123'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?seed=foo');
        $this->assertEquals(['', ['numPages' => $EMPTY_NUM_PAGES, 'soloable' => $EMPTY_SOLOABLE, 'edition' => $EMPTY_EDITION], 1, '', 'foo'], $this->search->requestToSearchParams($request));

        $request = Request::create('/?numPages-min=-5');
        $this->assertEquals(['', ['numPages' => $EMPTY_NUM_PAGES, 'soloable' => $EMPTY_SOLOABLE, 'edition' => $EMPTY_EDITION], 1, '', '123'], $this->search->requestToSearchParams($request));
    }

    public function testIsValidIntFilterValue()
    {
        $isValidIntFilterValue = self::getMethod(AdventureSearch::class, 'isValidIntFilterValue');
        $this->assertTrue($isValidIntFilterValue->invokeArgs($this->search, ['0']));
        $this->assertTrue($isValidIntFilterValue->invokeArgs($this->search, ['42']));
        $this->assertTrue($isValidIntFilterValue->invokeArgs($this->search, [2 ** 20]));
        $this->assertFalse($isValidIntFilterValue->invokeArgs($this->search, ['02']));
        $this->assertFalse($isValidIntFilterValue->invokeArgs($this->search, ['-2']));
        $this->assertFalse($isValidIntFilterValue->invokeArgs($this->search, [2 ** 32]));
    }

    public function testParseStringFilterValue()
    {
        $parseStringFilterValue = self::getMethod(AdventureSearch::class, 'parseStringFilterValue');
        $this->assertEquals([], $parseStringFilterValue->invokeArgs($this->search, ['']));
        $this->assertEquals([], $parseStringFilterValue->invokeArgs($this->search, ['~']));
        $this->assertEquals(['foo'], $parseStringFilterValue->invokeArgs($this->search, ['foo']));
        $this->assertEquals(['foo'], $parseStringFilterValue->invokeArgs($this->search, ['~foo~']));
        $this->assertEquals(['foo~bar', 'baz'], $parseStringFilterValue->invokeArgs($this->search, ['foo~~bar~baz']));
        $this->assertEquals(['foo~~bar~baz'], $parseStringFilterValue->invokeArgs($this->search, ['foo~~~~bar~~baz']));
    }

    private static function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
