<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Author;
use AppBundle\Entity\Edition;
use AppBundle\Entity\Environment;
use AppBundle\Entity\Item;
use AppBundle\Entity\Monster;
use AppBundle\Entity\Publisher;
use AppBundle\Entity\Setting;
use AppBundle\Service\AffiliateLinkHandler;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class AdventureType extends AbstractType
{
    /**
     * @var string[]
     */
    private $affiliateDomains = [];

    public function __construct(AffiliateLinkHandler $affiliateLinkHandler)
    {
        $this->affiliateDomains = $affiliateLinkHandler->getDomains();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'help' => 'The title of the adventure.',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'help' => 'Description of the module.',
                'required' => false,
                'attr' => [
                    'rows' => 10,
                ],
            ]);
        $this->createAppendableEntityCollection(
            $builder,
            'authors',
            Author::class,
            AuthorType::class,
            'addAuthor',
            'Authors',
            'Names of people with writing or story credits on the module. Do not include editors or designers.'
        );
        $builder
            ->add('edition', EntityType::class, [
                'help' => 'The system the game was designed for and the edition of that system if there is one.',
                'required' => false,
                'class' => Edition::class,
                'multiple' => false,
                'choice_label' => 'name',
                'label' => 'System / Edition',
                'attr' => [
                    'data-autocomplete' => true,
                ],
            ])
            ->add('environments', EntityType::class, [
                'help' => 'The different types of environments the module will take place in.',
                'required' => false,
                'class' => Environment::class,
                'multiple' => true,
                'choice_label' => 'name',
                'attr' => [
                    'data-autocomplete' => true,
                ],
            ]);
        $this->createAppendableEntityCollection(
            $builder,
            'items',
            Item::class,
            ItemType::class,
            'addItem',
            'Items',
            'The notable magic or non-magic items that are obtained in the module. Only include named items, don\'t include a +1 sword.'
        );
        $builder
            ->add('publisher', EntityType::class, [
                'help' => 'Publisher of the module.',
                'required' => false,
                'class' => Publisher::class,
                'multiple' => false,
                'choice_label' => 'name',
                'attr' => [
                    'data-autocomplete' => true,
                ],
            ])
            ->add('year', NumberType::class, [
                'required' => false,
                'help' => 'The year this adventure was first published.',
                'label' => 'Publication Year',
            ])
            ->add('setting', EntityType::class, [
                'help' => 'The narrative universe the module is set in.',
                'required' => false,
                'class' => Setting::class,
                'multiple' => false,
                'choice_label' => 'name',
                'attr' => [
                    'data-autocomplete' => true,
                ],
            ]);
        $this->createAppendableEntityCollection(
            $builder,
            'commonMonsters',
            Monster::class,
            CommonMonsterType::class,
            'addMonster',
            'Common Monsters',
            'The common monsters featured in the module.',
            function (EntityRepository $er) {
                $qb = $er->createQueryBuilder('e');

                return $qb->where('e.isUnique = FALSE');
            }
        );
        $this->createAppendableEntityCollection(
            $builder,
            'bossMonsters',
            Monster::class,
            BossMonsterType::class,
            'addMonster',
            'Boss Monsters',
            'The boss monsters and villains featured in the module.',
            function (EntityRepository $er) {
                $qb = $er->createQueryBuilder('e');

                return $qb->where('e.isUnique = TRUE');
            }
        );
        $builder
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
                'attr' => [
                    'data-autocomplete' => true,
                ],
            ])
            ->add('numPages', NumberType::class, [
                'required' => false,
                'help' => 'Total page count of all written material in the module or at least primary string.',
                'label' => 'Length (# of Pages)',
            ])
            ->add('foundIn', TextType::class, [
                'required' => false,
                'help' => 'If the adventure is part of a larger product, like a magazine or anthology, list it here.',
                'attr' => [
                    'data-autocomplete' => true,
                ],
            ])
            ->add('partOf', TextType::class, [
                'required' => false,
                'help' => 'The series of adventures that the module is a part of, if applicable.',
                'attr' => [
                    'data-autocomplete' => true,
                ],
            ])
            ->add('link', UrlType::class, [
                'required' => false,
                'help' => 'Links to legitimate sites where the module can be procured. Using affiliate links is not allowed. Links to the following domains will automatically be transformed into affiliate links: '.implode(', ', $this->affiliateDomains),
                'default_protocol' => 'https',
            ])
            ->add('thumbnailUrl', UrlType::class, [
                'required' => false,
                'help' => 'URL of the thumbnail image.',
                'default_protocol' => 'https',
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
                'help' => 'Whether or not battle mats are provided.',
                'required' => false,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'placeholder' => 'Unknown',
                'label' => 'Battle Mats',
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
            'data_class' => Adventure::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_adventure';
    }

    private function createAppendableEntityCollection(FormBuilderInterface $builder, string $fieldName, string $entity, string $form, string $method, string $title, string $help, callable $queryBuilder = null)
    {
        if (null === $queryBuilder) {
            $queryBuilder = function (EntityRepository $er) {
                return $er->createQueryBuilder('e');
            };
        }
        $builder
            // First create the normal dropdown for existing entities
            ->add($fieldName, EntityType::class, [
                'help' => $help,
                'required' => false,
                'class' => $entity,
                'multiple' => true,
                'choice_label' => 'name',
                'label' => $title,
                'attr' => [
                    'data-allow-add' => true,
                ],
                'query_builder' => $queryBuilder,
            ])
            ->get($fieldName)
            // Drop any submitted options starting with 'n'.
            // This drops all new entities from the dropdown.
            // Existing entities will always have numeric ids and therefore not be dropped.
            ->addViewTransformer(new CallbackTransformer(function ($data) { return $data; }, function ($choices) {
                return array_filter($choices, function ($choice) {
                    return 'n' !== $choice[0];
                });
            }));
        $builder
            // Add an empty collection of entities.
            // This will hold all newly created entities.
            ->add($fieldName.'-new', CollectionType::class, [
                'entry_type' => $form,
                'allow_add' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'style' => 'background: #cdcdcd',
                ],
                'label' => 'New '.$title.' (added to list above)',
                'entry_options' => [
                    'label_attr' => [
                        'class' => 'd-none',
                    ],
                ],
                'constraints' => [
                    new Valid(),
                ],
            ])
            // Once the form is submitted and validation has passed, associate the new entities
            // with the adventure. They will be persisted once the changes to the adventure are
            // added to the database.
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $formEvent) use ($fieldName, $method) {
                /** @var Adventure $adventure */
                $adventure = $formEvent->getData();
                /** @var Monster[]|Collection $newEntities */
                $newEntities = $formEvent->getForm()->get($fieldName.'-new')->getData();
                foreach ($newEntities as $entity) {
                    if (null !== $entity) {
                        $adventure->$method($entity);
                    }
                }
            }, -200)
        ;
    }
}
