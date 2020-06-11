<?php

namespace Tests\AppBundle\Field;

use AppBundle\Exception\FieldDoesNotExistException;
use AppBundle\Field\FieldProvider;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class FieldProviderTest extends TestCase
{
    /**
     * @var FieldProvider
     */
    private $fieldProvider;

    public function setUp(): void
    {
        $this->fieldProvider = new FieldProvider();
    }

    public function testGetFields()
    {
        $fields = $this->fieldProvider->getFields();
        $this->assertInstanceOf(Collection::class, $fields);
        $this->assertCount(23, $fields);
        foreach ($fields as $name => $field) {
            $this->assertSame($name, $field->getName());
        }
    }

    public function testGetFieldsAvailbleAsFilter()
    {
        $fields = $this->fieldProvider->getFieldsAvailableAsFilter();
        $this->assertInstanceOf(Collection::class, $fields);
        $this->assertCount(19, $fields);
    }

    public function testGetInvalidField()
    {
        $this->expectException(FieldDoesNotExistException::class);
        $this->fieldProvider->getField('invalid-name');
    }

    public function testGetField()
    {
        $field = $this->fieldProvider->getField('title');
        $this->assertSame('title', $field->getName());
    }
}
