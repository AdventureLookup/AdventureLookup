<?php

namespace AppBundle\Service;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\RelatedEntityInterface;
use AppBundle\Field\FieldProvider;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AdventureSerializer
{
    /**
     * @var FieldProvider
     */
    private $fieldProvider;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct(FieldProvider $fieldProvider)
    {
        $this->fieldProvider = $fieldProvider;
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * Converts an adventure entity into an indexable array.
     */
    public function toElasticDocument(Adventure $adventure): array
    {
        $doc = [];
        $doc['id'] = $adventure->getId();
        $doc['slug'] = $adventure->getSlug();
        $doc['createdAt'] = $adventure->getCreatedAt()->format('c');
        $doc['positiveReviews'] = $adventure->getNumberOfThumbsUp();
        $doc['negativeReviews'] = $adventure->getNumberOfThumbsDown();

        foreach ($this->fieldProvider->getFields() as $field) {
            $value = $this->propertyAccessor->getValue(
                $adventure,
                $field->getName()
            );
            if ($value instanceof RelatedEntityInterface) {
                $value = $this->relatedEntityToName($value);
            } elseif ($value instanceof Collection) {
                $value = $this->relatedEntitiesToNames($value);
            }

            $doc[$field->getName()] = $value;
        }

        return $doc;
    }

    /**
     * @param RelatedEntityInterface $entity
     *
     * @return string|null
     */
    private function relatedEntityToName(RelatedEntityInterface $entity = null)
    {
        return null === $entity ? null : $entity->getName();
    }

    /**
     * @param Collection|RelatedEntityInterface[] $relatedEntities
     *
     * @return string[]
     */
    private function relatedEntitiesToNames(Collection $relatedEntities): array
    {
        return $relatedEntities->map(function (RelatedEntityInterface $entity) {
            return $entity->getName();
        })->getValues();
    }
}
