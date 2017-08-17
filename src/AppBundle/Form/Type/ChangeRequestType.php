<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\ChangeRequest;
use AppBundle\Field\FieldProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeRequestType extends AbstractType
{
    /**
     * @var FieldProvider
     */
    private $fieldProvider;

    public function __construct(FieldProvider $fieldProvider)
    {
        $this->fieldProvider = $fieldProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];
        foreach($this->fieldProvider->getFields() as $field) {
            $choices[$field->getTitle()] = $field->getName();
        }

        $builder
            ->add('fieldName', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'No specific field',
                'help' => 'If your change request is about a specific field, select it above.',
                'choices' => $choices
            ])
            ->add('comment', TextareaType::class, [
                'required' => true,
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChangeRequest::class
        ]);
    }
}
