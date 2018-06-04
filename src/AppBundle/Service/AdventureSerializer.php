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
     *
     * @param Adventure $adventure
     * @return array
     */
    public function toElasticDocument(Adventure $adventure): array
    {
        $doc= [];
        $doc['slug'] = $adventure->getSlug();

        foreach ($this->fieldProvider->getFields() as $field) {
            $value = $this->propertyAccessor->getValue(
                $adventure,
                $field->getName()
            );
            if ($value instanceof RelatedEntityInterface) {
                $value = $this->relatedEntityToName($value);
            } else if ($value instanceof Collection) {
                $value = $this->relatedEntitiesToNames($value);
            }

            $doc[$field->getName()] = $value;
        }
        return $doc;
    }

    /**
     * @param RelatedEntityInterface $entity
     * @return null|string
     */
    private function relatedEntityToName(RelatedEntityInterface $entity = null)
    {
        return $entity === null ? null : $entity->getName();
    }

    /**
     * @param Collection|RelatedEntityInterface[] $relatedEntities
     * @return string[]
     */
    private function relatedEntitiesToNames(Collection $relatedEntities): array
    {
        return $relatedEntities->map(function (RelatedEntityInterface $entity) {
            return $entity->getName();
        })->getValues();
    }
}
