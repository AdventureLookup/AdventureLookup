<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rating', CheckboxType::class, [
                'label_attr' => [
                    'class' => 'd-none',
                ],
                'attr' => [
                    'class' => 'd-none',
                ],
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'autosize',
                    'rows' => '5',
                    'placeholder' => 'If you like, click here to type a few words about your experience with this adventure.',
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
