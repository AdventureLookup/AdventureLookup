<?php

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Adventure;
use AppBundle\Entity\TagContent;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Faker;

class RandomAdventuresData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine');

        $tags = $em->getRepository('AppBundle:TagName')->findAll();

        $faker = Faker\Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $adventure = new Adventure();
            $adventure->setTitle($faker->company);

            foreach ($tags as $tag) {
                $info = new TagContent();
                $info->setAdventure($adventure);
                $info->setTag($tag);
                if ($tag->getType() == 'integer') {
                    $info->setContent($faker->year);
                } else if ($tag->getType() == 'boolean') {
                    $info->setContent((string)$faker->boolean);
                } else {
                    $info->setContent($faker->text(200));
                }
                $manager->persist($info);
            }

            $manager->persist($adventure);
        }
        $manager->flush();
    }
}