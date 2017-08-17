<?php


namespace AppBundle\Field;

class Field
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

    public function __construct(string $name, string $type, bool $multiple, bool $freetextSearchable, string $title, string $description = null, int $searchBoost = 1, string $relatedEntityClass = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->multiple = $multiple;
        $this->title = $title;
        $this->description = $description;
        $this->freetextSearchable = $freetextSearchable;
        $this->searchBoost = $searchBoost;
        $this->relatedEntityClass = $relatedEntityClass;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @return bool
     */
    public function isFreetextSearchable(): bool
    {
        return $this->freetextSearchable;
    }

    /**
     * @return int
     */
    public function getSearchBoost(): int
    {
        return $this->searchBoost;
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getFieldNameForAggregation(): string
    {
        $field = $this->getName();
        if (in_array($this->getType(), ['string', 'url'], true)) {
            $field .= '.keyword';
        }

        return $field;
    }

    /**
     * @return bool
     */
    public function isRelatedEntity(): bool
    {
        return $this->relatedEntityClass !== null;
    }

    /**
     * @return string
     */
    public function getRelatedEntityClass(): string
    {
        return $this->relatedEntityClass;
    }
}
