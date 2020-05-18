<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Author;
use AppBundle\Repository\RelatedEntityFieldValueCountsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\WebTestCase;

class RelatedEntityFieldValueCountsTraitTest extends WebTestCase
{
    /**
     * @var RelatedEntityFieldValueCountsTrait
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Author::class);
    }

    /**
     * @dataProvider fieldNameAndConditionAndExpectedResultProvider
     */
    public function testUnresolvedChangeRequestsQuery(
        string $fieldName,
        string $condition = null,
        array $expectedResult
    ) {
        $fixture = new class() extends AbstractFixture {
            public function load(ObjectManager $em)
            {
                $author1 = new Author();
                $author1->setName('author1');
                $em->persist($author1);
                $author2 = new Author();
                $author2->setName('author2');
                $em->persist($author2);
                $author3 = new Author();
                $author3->setName('author3');
                $em->persist($author3);
                $adventure1 = new Adventure();
                $adventure1->setTitle('adventure1');
                $em->persist($adventure1);
                $adventure2 = new Adventure();
                $adventure2->setTitle('adventure2');
                $adventure2->setAuthors(new ArrayCollection([$author1, $author2]));
                $em->persist($adventure2);
                $adventure3 = new Adventure();
                $adventure3->setTitle('adventure3');
                $adventure3->setAuthors(new ArrayCollection([$author1]));
                $em->persist($adventure3);
                $em->flush();
            }
        };
        $this->loadFixtures([get_class($fixture)]);

        $result = $this->repository->getFieldValueCounts($fieldName, $condition);
        $this->assertSame($expectedResult, $result);
    }

    public function fieldNameAndConditionAndExpectedResultProvider()
    {
        return [
            ['name', null, [
                [
                    'value' => 'author1',
                    'id' => 1,
                    'count' => 2,
                ],
                [
                    'value' => 'author2',
                    'id' => 2,
                    'count' => 1,
                ],
            ]],
            ['id', null, [
                [
                    'value' => 1,
                    'id' => 1,
                    'count' => 2,
                ],
                [
                    'value' => 2,
                    'id' => 2,
                    'count' => 1,
                ],
            ]],
            ['name', "tbl.name <> 'author1'", [
                [
                    'value' => 'author2',
                    'id' => 2,
                    'count' => 1,
                ],
            ]],
        ];
    }
}
