<?php

namespace Tests\AppBundle\Field;

use AppBundle\Field\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    const NAME = 'field name';
    const TYPE = 'field type';
    const MULTIPLE = false;
    const SEARCHABLE = false;
    const FILTERABLE = false;
    const TITLE = 'Field Title';
    const DESCRIPTION = 'Field Description';
    const SEARCH_BOOST = 42;
    const RELATED_ENTITY_CLASS = 'some related class';

    public function testDefaults()
    {
        $field = new Field(self::NAME, self::TYPE, self::MULTIPLE, self::SEARCHABLE, self::FILTERABLE, self::TITLE);

        $this->assertSame(self::NAME, $field->getName());
        $this->assertSame(self::NAME, $field->getFieldNameForAggregation());
        $this->assertSame(self::TYPE, $field->getType());
        $this->assertSame(self::MULTIPLE, $field->isMultiple());
        $this->assertSame(self::SEARCHABLE, $field->isFreetextSearchable());
        $this->assertSame(self::FILTERABLE, $field->isAvailableAsFilter());
        $this->assertSame(self::TITLE, $field->getTitle());
        $this->assertSame(null, $field->getDescription());
        $this->assertSame(1, $field->getSearchBoost());
        $this->assertSame(false, $field->isRelatedEntity());
        $this->assertSame(null, $field->getRelatedEntityClass());
    }

    public function testWithoutDefaults()
    {
        $field = new Field(self::NAME, self::TYPE, self::MULTIPLE, self::SEARCHABLE, self::FILTERABLE, self::TITLE,
            self::DESCRIPTION, self::SEARCH_BOOST, self::RELATED_ENTITY_CLASS);

        $this->assertSame(self::NAME, $field->getName());
        $this->assertSame(self::NAME, $field->getFieldNameForAggregation());
        $this->assertSame(self::TYPE, $field->getType());
        $this->assertSame(self::MULTIPLE, $field->isMultiple());
        $this->assertSame(self::SEARCHABLE, $field->isFreetextSearchable());
        $this->assertSame(self::FILTERABLE, $field->isAvailableAsFilter());
        $this->assertSame(self::TITLE, $field->getTitle());
        $this->assertSame(self::DESCRIPTION, $field->getDescription());
        $this->assertSame(self::SEARCH_BOOST, $field->getSearchBoost());
        $this->assertSame(true, $field->isRelatedEntity());
        $this->assertSame(self::RELATED_ENTITY_CLASS, $field->getRelatedEntityClass());
    }

    public function testJSONSerialization()
    {
        $field = new Field(self::NAME, self::TYPE, self::MULTIPLE, self::SEARCHABLE, self::FILTERABLE, self::TITLE,
        self::DESCRIPTION, self::SEARCH_BOOST, self::RELATED_ENTITY_CLASS);

        $this->assertEquals([
            'name' => self::NAME,
            'type' => self::TYPE,
            'multiple' => self::MULTIPLE,
            'title' => self::TITLE,
            'description' => self::DESCRIPTION,
            'availableAsFilter' => self::FILTERABLE,
        ], json_decode(json_encode($field), true));
    }

    /**
     * @dataProvider aggregationFieldNameDataProvider
     */
    public function testGetFieldNameForAggregation(string $type, string $expectedAggregationFieldName)
    {
        $field = new Field(self::NAME, $type, self::MULTIPLE, self::SEARCHABLE, self::FILTERABLE, self::TITLE);

        $this->assertSame(self::NAME, $field->getName());
        $this->assertSame($expectedAggregationFieldName, $field->getFieldNameForAggregation());
    }

    public function aggregationFieldNameDataProvider()
    {
        return [
            ['string', self::NAME.'.keyword'],
            ['url', self::NAME.'.keyword'],
            ['text', self::NAME],
            ['something-else', self::NAME],
        ];
    }
}
