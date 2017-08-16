<?php


namespace AppBundle\Curation;

use AppBundle\Entity\Adventure;
use AppBundle\Field\Field;
use AppBundle\Field\FieldProvider;
use AppBundle\Repository\AdventureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class BulkEditFormHandler
{
    const OLD_VALUE = 'oldValue';
    const NEW_VALUE = 'newValue';
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

    public function formForSimpleStringField(Field $field): FormInterface
    {
        if (!$field->getType() === 'string') {
            throw new \InvalidArgumentException('Field type must be string');
        }

        $valuesAndCounts = $this->adventureRepository->getFieldValueCounts($field);

        $formChoices = [];
        foreach ($valuesAndCounts as $valueAndCount) {
            $formChoices[sprintf('%s (used %s times)', $valueAndCount['value'], $valueAndCount['count'])] = $valueAndCount['value'];
        }

        $formBuilder = $this->formFactory->createBuilder(FormType::class);
        $formBuilder->setAction($this->router->generate('curation_do_bulk_edit_adventures'));

        $title = $field->getTitle();
        $formBuilder
            ->add(self::OLD_VALUE, ChoiceType::class, [
                'choices' => $formChoices,
                'label' => sprintf('Select all adventures where %s matches', $title),
            ])
            ->add(self::NEW_VALUE, TextType::class, [
                'label' => sprintf('Replace selected %s value with', $title),
                'required' => false,
                'help' => sprintf('If you leave this empty, the selected adventures will have their %s set to nothing (NULL).', $field->getTitle()),
            ])
            ->add('submit', SubmitType::class, [
                'label' => sprintf('Save changes to %s', $title),
                'attr' => [
                    'class' => 'btn btn-warning',
                ]
            ]);

        return $formBuilder->getForm();
    }

    public function handleSimpleStringField(Request $request, Field $field): int
    {
        $form = $this->formForSimpleStringField($field);

        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return -1;
        }

        $oldValue = $form->get(self::OLD_VALUE)->getData();
        $newValue = $form->get(self::NEW_VALUE)->getData();
        if (strlen($newValue) === 0) {
            $newValue = null;
        }

        return $this->adventureRepository->updateField($field, $oldValue, $newValue);
    }
}
