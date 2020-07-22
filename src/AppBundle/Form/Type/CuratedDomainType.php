<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\CuratedDomain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CuratedDomainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('domain', TextType::class, [
                'help' => "
                    The domain, possibly including subdomains, to block or mark as verified.
                    Do not include http(s)://.
                    Valid examples are: 'google.com' (will also match 'foo.google.com'), 'bit.ly', 'foo.bar.de' (will also match 'a.foo.bar.de')",
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Blocked' => 'B',
                    'Verified' => 'V',
                ],
                'expanded' => true,
            ])
            ->add('reason', TextareaType::class, [
                'attr' => [
                    'rows' => 2,
                    'class' => 'autosize',
                ],
                'help' => 'The reason why this domain is blocked/verified. This information is PUBLIC and may be shown to the user when they try to use this domain.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CuratedDomain::class,
        ]);
    }
}
