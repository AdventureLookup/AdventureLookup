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
    private $example;

    public function __construct(string $name, string $type, bool $multiple, bool $freetextSearchable, string $title, string $description = null, string $example = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->multiple = $multiple;
        $this->title = $title;
        $this->description = $description;
        $this->example = $example;
        $this->freetextSearchable = $freetextSearchable;
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
    public function getExample()
    {
        return $this->example;
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
}
