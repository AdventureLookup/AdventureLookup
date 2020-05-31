<?php

namespace Tests\AppBundle\Curation;

use AppBundle\Curation\BulkEditFormProvider;
use AppBundle\Entity\Adventure;
use AppBundle\Entity\Monster;
use AppBundle\Field\Field;
use AppBundle\Field\FieldProvider;
use AppBundle\Repository\AdventureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;

class BulkEditFormProviderTest extends TestCase
{
    const RELATED_ENTITY_CLASS = 'A\Related\Entity\Class';
    const EDIT_ROUTE = '/some/edit/route';
    /**
     * @var BulkEditFormProvider
     */
    private $formProvider;

    /**
     * @var AdventureRepository|MockObject
     */
    private $repository;

    /**
     * @var EntityManagerInterface|MockObject
     */
    private $em;

    /**
     * @var FormFactoryInterface|MockObject
     */
    private $formFactory;

    /**
     * @var RouterInterface|MockObject
     */
    private $router;

    /**
     * @var FieldProvider|MockObject
     */
    private $fieldProvider;

    /**
     * @var FormBuilderInterface|MockObject
     */
    private $formBuilder;

    public function setUp()
    {
        $this->repository = $this->createMock(AdventureRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getRepository')->willReturnMap([
            [Adventure::class, $this->repository],
            [self::RELATED_ENTITY_CLASS, $this->repository],
            [Monster::class, $this->repository],
        ]);

        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
        $this->formBuilder->expects($this->any())
            ->method('setAction')
            ->with(self::EDIT_ROUTE)
            ->willReturnSelf();
        $this->formBuilder->method('add')->willReturnSelf();
        $this->formBuilder->method('getForm')->willReturn($this->createMock(FormInterface::class));
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->formFactory->method('createNamedBuilder')->willReturn($this->formBuilder);

        $this->fieldProvider = $this->createMock(FieldProvider::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->router->method('generate')
            ->with($this->equalTo('curation_do_bulk_edit_adventures'))
            ->willReturn(self::EDIT_ROUTE);

        $this->formProvider = new BulkEditFormProvider(
            $this->em,
            $this->formFactory,
            $this->fieldProvider,
            $this->router
        );
    }

    /**
     * @dataProvider nonSupportedFieldsProvider
     */
    public function testEmptyForNonSupportedFieldTypes($fields)
    {
        $this->fieldProvider->method('getFields')->willReturn($fields);
        $this->assertEmpty($this->formProvider->getFormsAndFields());
    }

    public function testSimpleField()
    {
        $field = $this->field('string', 'field1');
        $this->fieldProvider->method('getFields')->willReturn(new ArrayCollection([$field]));
        $this->repository->method('getFieldValueCounts')
            ->with('field1')
            ->willReturn([
                [
                    'value' => 'field value 1',
                    'count' => 42,
                ],
                [
                    'value' => 'field value 2',
                    'count' => 96,
                ],
            ]);
        $this->formBuilder->expects($this->exactly(3))
            ->method('add')
            ->withConsecutive(
                [
                    BulkEditFormProvider::OLD_VALUE,
                    ChoiceType::class,
                    $this->callback(function ($options) {
                        return $options['choices'] === [
                            'field value 1 (used 42 times)' => 'field value 1',
                            'field value 2 (used 96 times)' => 'field value 2',
                        ];
                    }),
                ],
                [BulkEditFormProvider::NEW_VALUE, TextType::class, $this->anything()],
                [$this->anything(), SubmitType::class, $this->anything()]
        );
        $formsAndFields = $this->formProvider->getFormsAndFields();
        $this->assertCount(1, $formsAndFields);
        $this->assertSame($field, $formsAndFields[0]['field']);
        $this->assertInstanceOf(FormInterface::class, $formsAndFields[0]['form']);
    }

    /**
     * @dataProvider relatedFieldsProvider
     */
    public function testRelatedField($fieldName, $condition, $class)
    {
        $field = $this->field('string', $fieldName, $class);
        $this->fieldProvider->method('getFields')->willReturn(new ArrayCollection([$field]));
        $this->repository->method('getFieldValueCounts')
            ->with($this->equalTo('name'), $this->equalTo($condition))
            ->willReturn([
                [
                    'value' => 'field value 1',
                    'count' => 42,
                    'id' => 5,
                ],
                [
                    'value' => 'field value 2',
                    'count' => 96,
                    'id' => 77,
                ],
            ]);
        $expectedChoices = [
            'field value 1 (used 42 times)' => 5,
            'field value 2 (used 96 times)' => 77,
        ];
        $this->formBuilder->expects($this->exactly(3))
            ->method('add')
            ->withConsecutive(
                [
                    BulkEditFormProvider::OLD_VALUE,
                    ChoiceType::class,
                    $this->callback(function ($options) use ($expectedChoices) {
                        return $options['choices'] === $expectedChoices;
                    }),
                ],
                [
                    BulkEditFormProvider::NEW_VALUE,
                    ChoiceType::class,
                    $this->callback(function ($options) use ($expectedChoices) {
                        return $options['choices'] === $expectedChoices;
                    }),
                ],
                [$this->anything(), SubmitType::class, $this->anything()]
            );
        $formsAndFields = $this->formProvider->getFormsAndFields();
        $this->assertCount(1, $formsAndFields);
        $this->assertSame($field, $formsAndFields[0]['field']);
        $this->assertInstanceOf(FormInterface::class, $formsAndFields[0]['form']);
    }

    public function testFieldsAreSorted()
    {
        $this->fieldProvider->method('getFields')->willReturn(new ArrayCollection([
            $this->field('string', 'field3'),
            $this->field('string', 'field1'),
            $this->field('string', 'field2'),
            $this->field('string', 'field4'),
        ]));
        $formsAndFields = $this->formProvider->getFormsAndFields();
        $this->assertCount(4, $formsAndFields);
        $this->assertSame('field1', $formsAndFields[0]['field']->getName());
        $this->assertSame('field2', $formsAndFields[1]['field']->getName());
        $this->assertSame('field3', $formsAndFields[2]['field']->getName());
        $this->assertSame('field4', $formsAndFields[3]['field']->getName());
    }

    public function nonSupportedFieldsProvider()
    {
        return [
            [new ArrayCollection()],
            [new ArrayCollection([
                $this->field('text'),
                $this->field('url'),
                $this->field('integer'),
                $this->field('something'),
                $this->field('string', 'title'),
            ])],
        ];
    }

    public function relatedFieldsProvider()
    {
        return [
            ['field1', null, self::RELATED_ENTITY_CLASS],
            ['commonMonsters', 'tbl.isUnique = 0', Monster::class],
            ['bossMonsters', 'tbl.isUnique = 1', Monster::class],
        ];
    }

    private function field(string $type, string $name = 'someField', string $relatedClass = null)
    {
        $field = $this->createMock(Field::class);
        $field->method('getType')->willReturn($type);
        $field->method('getName')->willReturn($name);
        $field->method('getTitle')->willReturn($name);
        $field->method('isRelatedEntity')->willReturn(null !== $relatedClass);
        $field->method('getRelatedEntityClass')->willReturn($relatedClass);

        return $field;
    }
}
