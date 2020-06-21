<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Author;
use AppBundle\Entity\Edition;
use AppBundle\Entity\Publisher;
use AppBundle\Field\Field;
use AppBundle\Field\FieldProvider;
use AppBundle\Service\AdventureSerializer;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class AdventureSerializerTest extends TestCase
{
    const ID = 42;
    const TITLE = 'a title';
    const SLUG = 'a-title';
    const MIN_STARTING_LEVEL = 5;
    const TACTICAL_MAPS = true;
    const LINK = 'http://example.org';
    const AUTHOR_1 = 'an author 1';
    const AUTHOR_2 = 'an author 2';
    const PUBLISHER = 'a publisher';
    const NUM_POSITIVE_REVIEWS = 34;
    const NUM_NEGATIVE_REVIEWS = 8;

    /**
     * @var AdventureSerializer
     */
    private $serializer;

    /**
     * @var FieldProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldProvider;

    private $CREATED_AT;

    public function setUp(): void
    {
        $this->fieldProvider = $this->createMock(FieldProvider::class);
        $this->serializer = new AdventureSerializer($this->fieldProvider);
        $this->CREATED_AT = new \DateTime();
    }

    public function testAlwaysSerializesSlugCreatedAtAndNumberOfReviews()
    {
        $this->fieldProvider->method('getFields')->willReturn(new ArrayCollection([]));
        $adventure = $this->createMock(Adventure::class);
        $adventure->method('getId')->willReturn(self::ID);
        $adventure->method('getSlug')->willReturn(self::SLUG);
        $adventure->method('getCreatedAt')->willReturn($this->CREATED_AT);
        $adventure->method('getNumberOfThumbsUp')->willReturn(self::NUM_POSITIVE_REVIEWS);
        $adventure->method('getNumberOfThumbsDown')->willReturn(self::NUM_NEGATIVE_REVIEWS);

        $doc = $this->serializer->toElasticDocument($adventure);
        $this->assertSame([
            'id' => self::ID,
            'slug' => self::SLUG,
            'createdAt' => $this->CREATED_AT->format('c'),
            'positiveReviews' => self::NUM_POSITIVE_REVIEWS,
            'negativeReviews' => self::NUM_NEGATIVE_REVIEWS,
        ], $doc);
    }

    public function testSerializeSimpleFields()
    {
        $this->fieldProvider->method('getFields')->willReturn(new ArrayCollection([
            new Field('title', 'string', false, false, false, 'title'),
            new Field('link', 'url', false, false, false, 'link'),
            new Field('foundIn', 'url', false, false, true, 'foundIn'),
            new Field('minStartingLevel', 'integer', false, false, true, 'minStartingLevel'),
            new Field('tacticalMaps', 'boolean', false, false, true, 'tacticalMaps'),
        ]));

        $adventure = $this->createMock(Adventure::class);
        $adventure->method('getId')->willReturn(self::ID);
        $adventure->method('getTitle')->willReturn(self::TITLE);
        $adventure->method('getSlug')->willReturn(self::SLUG);
        $adventure->method('getCreatedAt')->willReturn($this->CREATED_AT);
        $adventure->method('getNumberOfThumbsUp')->willReturn(self::NUM_POSITIVE_REVIEWS);
        $adventure->method('getNumberOfThumbsDown')->willReturn(self::NUM_NEGATIVE_REVIEWS);
        $adventure->method('getMinStartingLevel')->willReturn(self::MIN_STARTING_LEVEL);
        $adventure->method('hasTacticalMaps')->willReturn(self::TACTICAL_MAPS);
        $adventure->method('getLink')->willReturn(self::LINK);

        $doc = $this->serializer->toElasticDocument($adventure);
        $this->assertSame([
            'id' => self::ID,
            'slug' => self::SLUG,
            'createdAt' => $this->CREATED_AT->format('c'),
            'positiveReviews' => self::NUM_POSITIVE_REVIEWS,
            'negativeReviews' => self::NUM_NEGATIVE_REVIEWS,
            'title' => self::TITLE,
            'link' => self::LINK,
            'foundIn' => null,
            'minStartingLevel' => self::MIN_STARTING_LEVEL,
            'tacticalMaps' => self::TACTICAL_MAPS,
        ], $doc);
    }

    public function testSerializeRelatedEntities()
    {
        $this->fieldProvider->method('getFields')->willReturn(new ArrayCollection([
            new Field('title', 'string', false, false, false, 'title'),
            new Field('authors', 'string', true, false, true, 'authors', null, 1, Author::class),
            new Field('publisher', 'string', false, false, true, 'publisher', null, 1, Publisher::class),
            new Field('edition', 'string', false, false, true, 'edition', null, 1, Edition::class),
        ]));

        $author1 = new Author();
        $author1->setName(self::AUTHOR_1);
        $author2 = new Author();
        $author2->setName(self::AUTHOR_2);
        $publisher = (new Publisher())->setName(self::PUBLISHER);

        $adventure = $this->createMock(Adventure::class);
        $adventure->method('getId')->willReturn(self::ID);
        $adventure->method('getTitle')->willReturn(self::TITLE);
        $adventure->method('getSlug')->willReturn(self::SLUG);
        $adventure->method('getCreatedAt')->willReturn($this->CREATED_AT);
        $adventure->method('getNumberOfThumbsUp')->willReturn(self::NUM_POSITIVE_REVIEWS);
        $adventure->method('getNumberOfThumbsDown')->willReturn(self::NUM_NEGATIVE_REVIEWS);
        $adventure->method('getAuthors')->willReturn(
            new ArrayCollection([$author1, $author2])
        );
        $adventure->method('getPublisher')->willReturn(
            $publisher
        );
        $adventure->method('getEdition')->willReturn(null);

        $doc = $this->serializer->toElasticDocument($adventure);
        $this->assertSame([
            'id' => self::ID,
            'slug' => self::SLUG,
            'createdAt' => $this->CREATED_AT->format('c'),
            'positiveReviews' => self::NUM_POSITIVE_REVIEWS,
            'negativeReviews' => self::NUM_NEGATIVE_REVIEWS,
            'title' => self::TITLE,
            'authors' => [self::AUTHOR_1, self::AUTHOR_2],
            'publisher' => self::PUBLISHER,
            'edition' => null,
        ], $doc);
    }
}
