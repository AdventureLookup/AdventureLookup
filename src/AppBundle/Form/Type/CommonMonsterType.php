<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Monster;
use AppBundle\Entity\MonsterType as MonsterTypeEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommonMonsterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('types', EntityType::class, [
                'help' => 'Select all types that apply',
                'choice_label' => 'name',
                'required' => true,
                'class' => MonsterTypeEntity::class,
                'multiple' => true,
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Monster::class
        ]);
    }
}
