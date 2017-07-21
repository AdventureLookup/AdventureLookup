<?php

namespace AppBundle\Form;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Author;
use AppBundle\Entity\Edition;
use AppBundle\Entity\Environment;
use AppBundle\Entity\Item;
use AppBundle\Entity\Monster;
use AppBundle\Entity\NPC;
use AppBundle\Entity\Publisher;
use AppBundle\Entity\Setting;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'help' => 'Description of the module.',
                'required' => false,
                'attr' => [
                    'rows' => 10
                ]
            ])
            ->add('authors', EntityType::class, [
                'help' => 'Names of people with writing or story credits on the module. Do not include editors or designers.',
                'required' => false,
                'class' => Author::class,
                'multiple' => true,
                'choice_label' => 'name'
            ])
            ->add('edition', EntityType::class, [
                'help' => 'The system the game was designed for and the edition of that system if there is one.',
                'required' => false,
                'class' => Edition::class,
                'multiple' => false,
                'choice_label' => 'name',
                'label' => 'System / Edition'
            ])
            ->add('environments', EntityType::class, [
                'help' => 'The different types of environments the module will take place in.',
                'required' => false,
                'class' => Environment::class,
                'multiple' => true,
                'choice_label' => 'name'
            ])
            ->add('items', EntityType::class, [
                'help' => 'The notable magic or non-magic items that are obtained in the module. Only include named items, don\'t include a +1 sword.',
                'required' => false,
                'class' => Item::class,
                'multiple' => true,
                'choice_label' => 'name',
                'label' => 'Notable Items'
            ])
            ->add('npcs', EntityType::class, [
                'help' => 'Names of notable NPCs',
                'required' => false,
                'class' => NPC::class,
                'multiple' => true,
                'choice_label' => 'name'
            ])
            ->add('publisher', EntityType::class, [
                'help' => 'Publisher of the module.',
                'required' => false,
                'class' => Publisher::class,
                'multiple' => false,
                'choice_label' => 'name'
            ])
            ->add('setting', EntityType::class, [
                'help' => 'The narrative universe the module is set in.',
                'required' => false,
                'class' => Setting::class,
                'multiple' => false,
                'choice_label' => 'name',
            ])
            ->add('monsters', EntityType::class, [
                'help' => 'The various types of creatures featured in the module.',
                'required' => false,
                'class' => Monster::class,
                'multiple' => true,
                'choice_label' => 'name'
            ])
            ->add('minStartingLevel', NumberType::class, [
                'help' => 'The minimum level characters are expected to be when taking part in the module.',
                'required' => false,
            ])
            ->add('maxStartingLevel', NumberType::class, [
                'help' => 'The maximum level characters are expected to be when taking part in the module.',
                'required' => false,
            ])
            ->add('startingLevelRange', TextType::class, [
                'required' => false,
                'help' => 'In case no min. / max. starting levels but rather low/medium/high are given.',
            ])
            ->add('numPages', NumberType::class, [
                'required' => false,
                'help' => 'Total page count of all written material in the module or at least primary string.',
                'label' => 'Length (# of Pages)'
            ])
            ->add('foundIn', TextType::class, [
                'required' => false,
                'help' => 'The magazine, site, etc. the adventure can be found in.'
            ])
            ->add('link', UrlType::class, [
                'required' => false,
                'help' => 'Links to legitimate sites where the module can be procured.'
            ])
            ->add('thumbnailUrl', UrlType::class, [
                'required' => false,
                'help' => 'URL of the thumbnail image.'
            ])
            ->add('soloable', ChoiceType::class, [
                'help' => 'Whether or not this is suited to be played solo.',
                'required' => false,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'placeholder' => 'Unknown',
                'label' => 'Suitable for Solo Play',
                'expanded' => true,
            ])
            ->add('pregeneratedCharacters', ChoiceType::class, [
                'help' => 'Whether or not this contains character sheets.',
                'required' => false,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'placeholder' => 'Unknown',
                'label' => 'Includes Pregenerated Characters',
                'expanded' => true,
            ])
            ->add('tacticalMaps', ChoiceType::class, [
                'help' => 'Whether or not tactical maps are provided.',
                'required' => false,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'placeholder' => 'Unknown',
                'label' => 'Tactical Maps',
                'expanded' => true,
            ])
            ->add('handouts', ChoiceType::class, [
                'help' => 'Whether or not handouts are provided.',
                'required' => false,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'placeholder' => 'Unknown',
                'label' => 'Handouts',
                'expanded' => true,
            ])
        ;
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
