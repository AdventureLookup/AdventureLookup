<?php

namespace AppBundle\Form;

use AppBundle\Entity\TagName;
use AppBundle\Service\FieldUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagNameType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class);

        if (!$options['isEdit']) {
            $fieldUtils = new FieldUtils();
            $builder->add('type', ChoiceType::class, [
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => $fieldUtils->getFieldNameDescriptions(),
                'help' => 'Please choose carefully. You will not be able to change the field type later on! Please note: Each field can be added to adventures multiple times. That\'s why the appropriate field type for a list of monsters would be \'string\': Each monster name itself fits perfectly in a simple string.'
            ]);
        }
        $builder->add('description', TextType::class, [
                'help' => 'Give some more insight into what exactly shall be saved in the field.'
            ])
            ->add('example', TextType::class, [
                'help' => 'Give some example of a field value that would be saved in the field.'
            ])
            ->add('showInSearchResults', CheckboxType::class, [
                'required' => false,
                'help' => 'Whether or not this field is showed directly in the search results.'
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TagName::class
            ])
            ->setRequired(['isEdit']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_tagname';
    }


}
