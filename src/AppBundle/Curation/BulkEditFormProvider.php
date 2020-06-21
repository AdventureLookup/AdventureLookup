<?php

namespace AppBundle\Curation;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Monster;
use AppBundle\Field\Field;
use AppBundle\Field\FieldProvider;
use AppBundle\Repository\AdventureRepository;
use AppBundle\Repository\RelatedEntityFieldValueCountsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BulkEditFormProvider
{
    const OLD_VALUE = 'oldValue';
    const NEW_VALUE = 'newValue';
    const JS_RETURN_CONFIRMATION = 'javascript:return confirm("Are you sure? This cannot be undone!")';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var AdventureRepository
     */
    private $adventureRepository;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FieldProvider
     */
    private $fieldProvider;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        FieldProvider $fieldProvider,
        RouterInterface $router
    ) {
        $this->em = $em;
        $this->adventureRepository = $em->getRepository(Adventure::class);
        $this->formFactory = $formFactory;
        $this->fieldProvider = $fieldProvider;
        $this->router = $router;
    }

    public function getFormsAndFields()
    {
        $formsAndFields = $this->fieldProvider->getFields()
            ->filter(function (Field $field) {
                return 'string' === $field->getType() && 'title' !== $field->getName();
            })
            ->map(function (Field $field) {
                if ($field->isRelatedEntity()) {
                    return [
                        'field' => $field,
                        'form' => $this->formForRelatedEntity($field),
                    ];
                }

                return [
                    'field' => $field,
                    'form' => $this->formForSimpleStringField($field),
                ];
            })
            ->toArray();

        usort($formsAndFields, function ($a, $b) {
            return $a['field']->getTitle() <=> $b['field']->getTitle();
        });

        return $formsAndFields;
    }

    private function formForSimpleStringField(Field $field): FormInterface
    {
        if ('string' === !$field->getType()) {
            // @codeCoverageIgnoreStart
            throw new \InvalidArgumentException('Field type must be string');
            // @codeCoverageIgnoreEnd
        }

        $formChoices = $this->getFieldChoicesForSimpleField($field->getName());
        $title = $field->getTitle();

        return $this->getFormBuilderFor($field)
            ->add(self::OLD_VALUE, ChoiceType::class, [
                'choices' => $formChoices,
                'label' => sprintf('Select all adventures where %s matches', $title),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add(self::NEW_VALUE, TextType::class, [
                'label' => sprintf('Replace selected %s value with', $title),
                'required' => false,
                'help' => sprintf('If you leave this empty, the selected adventures will have their %s set to nothing (NULL).', $title),
            ])
            ->add('submit', SubmitType::class, [
                'label' => sprintf('Save changes to %s', $title),
                'attr' => [
                    'class' => 'btn btn-warning',
                    'onclick' => self::JS_RETURN_CONFIRMATION,
                ],
            ])
            ->getForm();
    }

    private function formForRelatedEntity(Field $field): FormInterface
    {
        /** @var EntityRepository|RelatedEntityFieldValueCountsTrait $repository */
        $repository = $this->em->getRepository($field->getRelatedEntityClass());

        if (Monster::class === $field->getRelatedEntityClass()) {
            if ('commonMonsters' === $field->getName()) {
                $formChoices = $this->getFieldChoicesForRelatedField($repository, 'name', 'tbl.isUnique = FALSE');
            } elseif ('bossMonsters' === $field->getName()) {
                $formChoices = $this->getFieldChoicesForRelatedField($repository, 'name', 'tbl.isUnique = TRUE');
            } else {
                throw new \LogicException(sprintf('Unknown monster field %s', $field->getName()));
            }
        } else {
            $formChoices = $this->getFieldChoicesForRelatedField($repository, 'name');
        }
        $title = $field->getTitle();

        return $this->getFormBuilderFor($field)
            ->add(self::OLD_VALUE, ChoiceType::class, [
                'label' => sprintf('Select all adventures where %s matches', $title),
                'choices' => $formChoices,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add(self::NEW_VALUE, ChoiceType::class, [
                'label' => sprintf('Replace selected %s value with', $title),
                'choices' => $formChoices,
                'required' => false,
                'placeholder' => sprintf('Set empty (NULL). The %s entry itself will not be removed.', $title),
                'help' => sprintf('If you leave this empty, the selected adventures will have their %s set to nothing (NULL).', $title),
            ])
            ->add('submit', SubmitType::class, [
                'label' => sprintf('Save changes to %s', $title),
                'attr' => [
                    'class' => 'btn btn-warning',
                    'onclick' => self::JS_RETURN_CONFIRMATION,
                ],
            ])
            ->getForm();
    }

    private function getFieldChoicesForSimpleField(string $fieldName): array
    {
        $valuesAndCounts = $this->adventureRepository->getFieldValueCounts($fieldName);
        $formChoices = [];
        foreach ($valuesAndCounts as $valueAndCount) {
            $formChoices[sprintf(
                '%s (used %s times)',
                $valueAndCount['value'],
                $valueAndCount['count']
            )] = $valueAndCount['value'];
        }

        return $formChoices;
    }

    /**
     * @param EntityRepository|RelatedEntityFieldValueCountsTrait $repository
     */
    private function getFieldChoicesForRelatedField(EntityRepository $repository, string $fieldName, string $additionalWhereCondition = null): array
    {
        $valuesAndCounts = $repository->getFieldValueCounts($fieldName, $additionalWhereCondition);
        $formChoices = [];
        foreach ($valuesAndCounts as $valueAndCount) {
            $formChoices[sprintf(
                '%s (used %s times)',
                $valueAndCount['value'],
                $valueAndCount['count']
            )] = $valueAndCount['id'];
        }

        return $formChoices;
    }

    private function getFormBuilderFor(Field $field): FormBuilderInterface
    {
        return $this->formFactory->createNamedBuilder($field->getName())
            ->setAction($this->router->generate('curation_do_bulk_edit_adventures'));
    }
}
