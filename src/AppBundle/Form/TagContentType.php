<?php

namespace AppBundle\Form;

use AppBundle\Entity\TagContent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $builder
            ->add('tag', null, [
                'required' => true,
                'disabled' => true
            ])
            ->add('content', TextType::class, [
                'required' => true,
            ]);
        if ($options['isEdit']) {
            $builder->add('approved', null, [
                'required' => false
            ]);
        }

        if (!$options['isEdit']) {
            $builder->add('saveAndAdd', SubmitType::class, [
                'label' => 'Save and add more information',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'role' => 'button'
                ]
            ]);
        }
        $builder->add('save', SubmitType::class, [
            'label' => 'Save and return to adventure',
            'attr' => [
                'class' => 'btn ' . ($options['isEdit'] ? 'btn-primary' : 'btn-secondary'),
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
            ->setRequired(['isEdit'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_tagcontent';
    }


}
