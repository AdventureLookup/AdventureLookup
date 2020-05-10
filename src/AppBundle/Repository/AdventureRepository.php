<?php

namespace AppBundle\Repository;

use AppBundle\Entity\RelatedEntityInterface;
use AppBundle\Field\Field;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\Query;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AdventureRepository extends EntityRepository
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct($em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);

        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * Get all distinct values and their usage counts for a certain field. Will ignore NULL values
     *
     * @param string $field
     *
     * @return array Array of arrays containing the 'value' and 'count'
     */
    public function getFieldValueCounts(string $field): array
    {
        $field = 'tbl.' . $field;

        $qb = $this->createQueryBuilder('tbl');
        $results = $qb
            ->select($field)
            ->addSelect($qb->expr()->count($field))
            ->where($qb->expr()->isNotNull($field))
            ->groupBy($field)
            ->orderBy($qb->expr()->asc($field))
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($result) {
            return [
                'value' => current($result),
                'count' => (int)$result[1],
            ];
        }, $results);
    }

    /**
     * @return Query
     */
    public function getWithMostUnresolvedChangeRequestsQuery()
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->join('a.changeRequests', 'c')
            ->where($qb->expr()->eq('c.resolved', $qb->expr()->literal(false)))
            ->select('a.title,a.slug')
            ->addSelect('COUNT(c.id) AS changeRequestCount')
            ->groupBy('a.id')
            ->orderBy($qb->expr()->desc('changeRequestCount'))
            ->getQuery();
    }

    /**
     * Updates $field of all adventures where $field = $oldValue to $newValue
     *
     * @param Field $field
     * @param string $oldValue
     * @param string|null $newValue
     * @return int The number of affected adventures
     */
    public function updateField(Field $field, string $oldValue, string $newValue = null): int
    {
        if ($field->isRelatedEntity()) {
            $adventures = $this->updateRelatedField($field, $oldValue, $newValue);
        } else {
            $adventures = $this->updateSimpleField($field, $oldValue, $newValue);
        }
        $em = $this->getEntityManager();
        $em->flush();

        return count($adventures);
    }

    /**
     * @param Field $field
     * @param string $oldValue
     * @param string $newValue
     * @return array
     */
    private function updateSimpleField(Field $field, string $oldValue, string $newValue = null): array
    {
        $adventures = $this->findBy([$field->getName() => $oldValue]);
        foreach ($adventures as $adventure) {
            $this->propertyAccessor->setValue($adventure, $field->getName(), $newValue);
        }

        return $adventures;
    }

    /**
     * @param Field $field
     * @param string $oldValue
     * @param string $newValue
     * @return array
     */
    private function updateRelatedField(Field $field, string $oldValue, string $newValue = null): array
    {
        $oldValue = (int) $oldValue;
        $em = $this->getEntityManager();

        $qb = $this->createQueryBuilder('a');
        $relationName = $fieldName = $field->getName();
        if (in_array($relationName, ['commonMonsters', 'bossMonsters'], true)) {
            $relationName = 'monsters';
        }
        $qb
            ->join('a.' . $relationName, 'r')
            ->where($qb->expr()->eq('r.id', ':oldValue'))
            ->setParameter('oldValue', $oldValue);
        if ($fieldName === 'commonMonsters') {
            $qb->andWhere($qb->expr()->eq('r.isUnique', false));
        } else if ($fieldName === 'bossMonsters') {
            $qb->andWhere($qb->expr()->eq('r.isUnique', true));
        }
        $adventures = $qb->getQuery()->execute();
        foreach ($adventures as $adventure) {
            if (!$field->isMultiple()) {
                if ($newValue === null) {
                    $newRelatedEntity = null;
                } else {
                    $newRelatedEntity = $em->getReference($field->getRelatedEntityClass(), (int)$newValue);
                }
                $this->propertyAccessor->setValue($adventure, $fieldName, $newRelatedEntity);
            } else {
                /** @var ArrayCollection|RelatedEntityInterface[]|RelatedEntityInterface $currentRelatedEntities */
                $currentRelatedEntities = $this->propertyAccessor->getValue($adventure, $fieldName);

                if ($newValue === null) {
                    $newRelatedEntities = $currentRelatedEntities->filter(function (RelatedEntityInterface $relatedEntity) use ($oldValue) {
                        return $relatedEntity->getId() !== $oldValue;
                    });
                } else {
                    $newRelatedEntity = $em->getReference($field->getRelatedEntityClass(), (int)$newValue);
                    /** @var ArrayCollection|RelatedEntityInterface[] $newRelatedDuplicatedEntities */
                    $newRelatedDuplicatedEntities = $currentRelatedEntities->map(function (RelatedEntityInterface $relatedEntity) use ($oldValue, $newRelatedEntity) {
                        if ($relatedEntity->getId() !== $oldValue) {
                            return $relatedEntity;
                        } else {
                            return $newRelatedEntity;
                        }
                    })->toArray();

                    // Now we need to make sure to remove any duplicates
                    $newRelatedEntities = [];
                    foreach ($newRelatedDuplicatedEntities as $newRelatedDuplicatedEntity) {
                        $newRelatedEntities[$newRelatedDuplicatedEntity->getId()] = $newRelatedDuplicatedEntity;
                    }
                }
                $this->propertyAccessor->setValue($adventure, $fieldName, $newRelatedEntities);
            }
        }

        if (count($adventures) > 0) {
            // Mark old related entity for removal.
            $oldRelatedEntity = $em->getReference($field->getRelatedEntityClass(), $oldValue);
            $em->remove($oldRelatedEntity);
        }

        return $adventures;
    }
}
