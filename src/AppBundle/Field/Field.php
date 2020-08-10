<?php

namespace AppBundle\Field;

class Field implements \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $multiple;

    /**
     * @var bool
     */
    private $freetextSearchable;

    /**
     * @var bool
     */
    private $availableAsFilter;

    /**
     * @var int
     */
    private $searchBoost;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $relatedEntityClass;

    private int $filterbarSort;

    public function __construct(string $name, string $type, bool $multiple, bool $freetextSearchable, bool $availableAsFilter, string $title, string $description = null, int $searchBoost = 1, int $filterbarSort = 0, string $relatedEntityClass = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->multiple = $multiple;
        $this->title = $title;
        $this->description = $description;
        $this->freetextSearchable = $freetextSearchable;
        $this->availableAsFilter = $availableAsFilter;
        $this->searchBoost = $searchBoost;
        $this->relatedEntityClass = $relatedEntityClass;
        $this->filterbarSort = $filterbarSort;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function isFreetextSearchable(): bool
    {
        return $this->freetextSearchable;
    }

    public function isAvailableAsFilter(): bool
    {
        return $this->availableAsFilter;
    }

    public function getSearchBoost(): int
    {
        return $this->searchBoost;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getFilterbarSort(): int
    {
        return $this->filterbarSort;
    }

    public function getFieldNameForAggregation(): string
    {
        $field = $this->getName();
        if (in_array($this->getType(), ['string', 'url'], true)) {
            $field .= '.keyword';
        }

        return $field;
    }

    public function isRelatedEntity(): bool
    {
        return null !== $this->relatedEntityClass;
    }

    /**
     * @return string|null
     */
    public function getRelatedEntityClass()
    {
        return $this->relatedEntityClass;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'multiple' => $this->multiple,
            'title' => $this->title,
            'description' => $this->description,
            'availableAsFilter' => $this->availableAsFilter,
            'filterbarSort' => $this->filterbarSort,
        ];
    }
}
