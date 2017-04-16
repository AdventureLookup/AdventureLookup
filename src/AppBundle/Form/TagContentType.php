<?php

namespace AppBundle\Form;

use AppBundle\Entity\TagContent;
use AppBundle\Entity\TagName;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagContentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tag', EntityType::class, [
            'required' => true,
            'disabled' => true,
            'class' => TagName::class
        ]);
        switch ($options['type']) {
            default:
            case 'string':
                $builder->add('content', TextType::class, [
                    'required' => true,
                ]);
                break;
            case 'integer':
                $builder->add('content', IntegerType::class, [
                    'required' => true,
                ]);
                break;
            case 'text':
                $builder->add('content', TextareaType::class, [
                    'required' => true,
                    'attr' => [
                        'rows' => 20
                    ]
                ]);
                break;
        }
        $builder->add('approved', null, [
            'required' => false
        ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Save',
            'attr' => [
                'class' => 'btn btn-primary',
                'role' => 'button'
            ]
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TagContent::class
            ])
            ->setRequired(['type']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_tagcontent';
    }
}
