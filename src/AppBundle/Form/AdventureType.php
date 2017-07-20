<?php

namespace AppBundle\Form;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Setting;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdventureType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'help' => 'The title of the adventure. You can add more information in just a second.',
                'required' => true
            ])
            ->add('setting', EntityType::class, [
                'help' => 'The narrative universe the module is set in.',
                'required' => true,
                'class' => Setting::class,
                'multiple' => false,
                'choice_label' => 'name'
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adventure::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_adventure';
    }
}
