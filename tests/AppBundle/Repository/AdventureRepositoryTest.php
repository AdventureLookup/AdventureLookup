<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Author;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\Monster;
use AppBundle\Entity\Publisher;
use AppBundle\Entity\RelatedEntityInterface;
use AppBundle\Field\Field;
use AppBundle\Repository\AdventureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tests\WebTestCase;

class AdventureRepositoryTest extends WebTestCase
{
    /**
     * @var AdventureRepository
     */
    private $repository;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function setUp()
    {
        parent::setUp();

        $this->propertyAccessor = new PropertyAccessor();

        $this->repository = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Adventure::class);

        $fixture = new class() extends AbstractFixture {
            public function load(ObjectManager $em)
            {
                $author1 = new Author();
                $author1->setName('author1');
                $em->persist($author1);
                $author2 = new Author();
                $author2->setName('author2');
                $em->persist($author2);

                $publisher1 = new Publisher();
                $publisher1->setName('publisher1');
                $em->persist($publisher1);
                $publisher2 = new Publisher();
                $publisher2->setName('publisher2');
                $em->persist($publisher2);

                $commonMonster1 = new Monster();
                $commonMonster1->setName('common1');
                $commonMonster1->setIsUnique(false);
                $em->persist($commonMonster1);
                $commonMonster2 = new Monster();
                $commonMonster2->setName('common2');
                $commonMonster2->setIsUnique(false);
                $em->persist($commonMonster2);

                $bossMonster1 = new Monster();
                $bossMonster1->setName('boss1');
                $bossMonster1->setIsUnique(true);
                $em->persist($bossMonster1);
                $bossMonster2 = new Monster();
                $bossMonster2->setName('boss2');
                $bossMonster2->setIsUnique(true);
                $em->persist($bossMonster2);

                $adventure1 = new Adventure();
                $adventure1->setTitle('adventure1');
                $adventure1->setLink('link1');
                $adventure1->setAuthors(new ArrayCollection([$author1]));
                $adventure1->setPublisher($publisher1);
                $adventure1->setMonsters(new ArrayCollection([$commonMonster1, $commonMonster2, $bossMonster1, $bossMonster2]));
                $em->persist($adventure1);
                $adventure2 = new Adventure();
                $adventure2->setTitle('adventure2');
                $adventure2->setLink('link2');
                $adventure2->setAuthors(new ArrayCollection([$author2]));
                $adventure2->setPublisher($publisher2);
                $em->persist($adventure2);
                $adventure3 = new Adventure();
                $adventure3->setTitle('adventure3');
                $adventure3->setLink('link1');
                $adventure3->setAuthors(new ArrayCollection([$author1, $author2]));
                $adventure3->setPublisher($publisher1);
                $em->persist($adventure3);
                $adventure4 = new Adventure();
                $adventure4->setTitle('adventure4');
                $adventure4->setLink(null);
                $em->persist($adventure4);

                // Adventure 1: 2 unresolved, 1 resolved
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(false);
                $changeRequest->setAdventure($adventure1);
                $em->persist($changeRequest);
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(false);
                $changeRequest->setAdventure($adventure1);
                $em->persist($changeRequest);
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(true);
                $changeRequest->setAdventure($adventure1);
                $em->persist($changeRequest);
                // Adventure 2: 1 unresolved, 3 resolved
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(false);
                $changeRequest->setAdventure($adventure2);
                $em->persist($changeRequest);
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(true);
                $changeRequest->setAdventure($adventure2);
                $em->persist($changeRequest);
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(true);
                $changeRequest->setAdventure($adventure2);
                $em->persist($changeRequest);
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(true);
                $changeRequest->setAdventure($adventure2);
                $em->persist($changeRequest);
                // Adventure 3: 0 unresolved, 1 resolved
                $changeRequest = new ChangeRequest();
                $changeRequest->setResolved(true);
                $changeRequest->setAdventure($adventure3);
                $em->persist($changeRequest);
                // Adventure 4: 0 unresolved, 0 resolved
                $em->flush();
            }
        };
        $this->loadFixtures([get_class($fixture)]);
    }

    public function testGetFieldValueCounts()
    {
        $results = $this->repository->getFieldValueCounts('link');
        $this->assertSame([
            [
                'value' => 'link1',
                'count' => 2,
            ],
            [
                'value' => 'link2',
                'count' => 1,
            ],
        ], $results);
    }

    /**
     * @dataProvider updateSimpleFieldProvider
     */
    public function testUpdateSimpleField(Field $field, string $oldValue, string $newValue = null,
        int $expectedAffected, $expectedValues)
    {
        $affected = $this->repository->updateField($field, $oldValue, $newValue);
        $this->assertSame($expectedAffected, $affected);
        /** @var Adventure[] $adventures */
        $adventures = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(Adventure::class)
            ->findBy([], ['title' => 'ASC']);

        $propertyAccessor = new PropertyAccessor();
        foreach ($adventures as $i => $adventure) {
            $value = $propertyAccessor->getValue($adventure, $field->getName());
            $this->assertSame($expectedValues[$i], $value);
        }
    }

    public function updateSimpleFieldProvider()
    {
        $linkField = $this->createMock(Field::class);
        $linkField->method('getName')->willReturn('link');

        return [
            [$linkField, 'link1', 'link2', 2, [
                'link2', 'link2', 'link2', null,
            ]],
            [$linkField, 'link1', null, 2, [
                null, 'link2', null, null,
            ]],
            [$linkField, 'link42', 'link2', 0, [
                'link1', 'link2', 'link1', null,
            ]],
        ];
    }

    /**
     * @dataProvider updateRelatedFieldSingleProvider
     */
    public function testUpdateRelatedFieldSingle(Field $field, string $oldValue, string $newValue = null,
        int $expectedAffected, $expectedValues)
    {
        $adventures = $this->doTestUpdateRelatedField($field, $oldValue, $newValue, $expectedAffected);

        foreach ($adventures as $i => $adventure) {
            /** @var RelatedEntityInterface|null $value */
            $value = $this->propertyAccessor->getValue($adventure, $field->getName());
            if (null !== $value) {
                $value = $value->getId();
            }
            $this->assertSame($expectedValues[$i], $value);
        }
    }

    public function updateRelatedFieldSingleProvider()
    {
        $publisherField = $this->relatedField('publisher', Publisher::class, false);

        // field, old val, new val, num affected, [expected values]
        return [
            [$publisherField, 1, 2, 2, [2, 2, 2, null]],
            [$publisherField, 2, 1, 1, [1, 1, 1, null]],
            [$publisherField, 5, 4, 0, [1, 2, 1, null]],
        ];
    }

    /**
     * @dataProvider updateRelatedFieldMultipleProvider
     */
    public function testUpdateRelatedFieldMultiple(Field $field, string $oldValue, string $newValue = null,
        int $expectedAffected, $expectedValues)
    {
        $adventures = $this->doTestUpdateRelatedField($field, $oldValue, $newValue, $expectedAffected);

        foreach ($adventures as $i => $adventure) {
            $values = $this->propertyAccessor->getValue($adventure, $field->getName())
                ->map(function (RelatedEntityInterface $relatedEntity) {
                    return $relatedEntity->getId();
                })
                ->getValues();
            $this->assertSame($expectedValues[$i], $values);
        }
    }

    public function updateRelatedFieldMultipleProvider()
    {
        $authorField = $this->relatedField('authors', Author::class, true);
        $commonMonstersField = $this->relatedField('commonMonsters', Monster::class, true);
        $bossMonstersField = $this->relatedField('bossMonsters', Monster::class, true);

        return [
            [$authorField, 1, 2, 2, [
                [2], [2], [2], [],
            ]],
            [$authorField, 1, null, 2, [
                [], [2], [2], [],
            ]],
            [$authorField, 3, 6, 0, [
                [1], [2], [1, 2], [],
            ]],
            [$commonMonstersField, 1, 2, 1, [
                [2], [], [], [],
            ]],
            [$bossMonstersField, 1, 2, 0, [
                [3, 4], [], [], [],
            ]],
            [$bossMonstersField, 3, 4, 1, [
                [4], [], [], [],
            ]],
        ];
    }

    public function testGetWithMostUnresolvedChangeRequestsQuery()
    {
        $query = $this->repository->getWithMostUnresolvedChangeRequestsQuery();
        $this->assertInstanceOf(Query::class, $query);
        $this->assertSame([
            [
                'title' => 'adventure1',
                'slug' => 'adventure1',
                'changeRequestCount' => '2',
            ],
            [
                'title' => 'adventure2',
                'slug' => 'adventure2',
                'changeRequestCount' => '1',
            ],
        ], $query->execute());
    }

    /**
     * @return Field|\MockObject
     */
    private function relatedField(string $fieldName, string $class, bool $multiple)
    {
        $field = $this->createMock(Field::class);
        $field->method('getName')->willReturn($fieldName);
        $field->method('isRelatedEntity')->willReturn(true);
        $field->method('getRelatedEntityClass')->willReturn($class);
        $field->method('isMultiple')->willReturn($multiple);

        return $field;
    }

    /**
     * @param string $newValue
     *
     * @return Adventure[]
     */
    private function doTestUpdateRelatedField(Field $field, string $oldValue, string $newValue = null, int $expectedAffected): array
    {
        $affected = $this->repository->updateField($field, $oldValue, $newValue);
        $this->assertSame($expectedAffected, $affected);

        if ($affected > 0) {
            $em = $this->getContainer()->get('doctrine');
            $relatedRepository = $em->getRepository($field->getRelatedEntityClass());
            $this->assertNull($relatedRepository->find($oldValue));
        }

        return $this->repository->findBy([], ['title' => 'ASC']);
    }
}
