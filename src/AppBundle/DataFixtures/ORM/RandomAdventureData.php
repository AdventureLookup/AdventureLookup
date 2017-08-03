<?php

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Adventure;
use AppBundle\Entity\Author;
use AppBundle\Entity\Edition;
use AppBundle\Entity\Environment;
use AppBundle\Entity\Item;
use AppBundle\Entity\Monster;
use AppBundle\Entity\NPC;
use AppBundle\Entity\Publisher;
use AppBundle\Entity\Setting;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Faker;

class RandomAdventureData implements FixtureInterface, ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [AuthorData::class, EditionData::class, EnvironmentData::class, ItemData::class, MonsterData::class,
            NPCData::class, PublisherData::class, SettingData::class];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $em
     */
    public function load(ObjectManager $em)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->container->get('doctrine');

        /** @var Author[] $authors */
        $authors = $doctrine->getRepository('AppBundle:Author')->findAll();
        /** @var Edition[] $editions */
        $editions = $doctrine->getRepository('AppBundle:Edition')->findAll();
        /** @var Environment[] $environments */
        $environments = $doctrine->getRepository('AppBundle:Environment')->findAll();
        /** @var Item[] $items */
        $items = $doctrine->getRepository('AppBundle:Item')->findAll();
        /** @var NPC[] $npcs */
        $npcs = $doctrine->getRepository('AppBundle:NPC')->findAll();
        /** @var Publisher[] $publishers */
        $publishers = $doctrine->getRepository('AppBundle:Publisher')->findAll();
        /** @var Setting[] $settings */
        $settings = $doctrine->getRepository('AppBundle:Setting')->findAll();
        /** @var Monster[] $monsters */
        $monsters = $doctrine->getRepository('AppBundle:Monster')->findAll();

        $faker = Faker\Factory::create();

        // Disable indexing temporarily.
        $doctrine->getManager()->getEventManager()->removeEventSubscriber(
            $this->container->get('search_index_updater')
        );

        for ($i = 0; $i < 200; $i++) {
            $adventure = new Adventure();
            $adventure
                ->setTitle($faker->catchPhrase)
                ->setDescription($faker->realText(2000))
                ->setNumPages($faker->numberBetween(1, 200))
                ->setFoundIn($faker->catchPhrase)
                ->setPartOf($faker->boolean() ? $faker->catchPhrase : null)
                ->setLink($faker->url)
                ->setThumbnailUrl($faker->imageUrl(260, 300))
                ->setSoloable($faker->boolean())
                ->setPregeneratedCharacters($faker->boolean())
                ->setTacticalMaps($faker->boolean())
                ->setHandouts($faker->boolean())
                ->setAuthors(new ArrayCollection($faker->randomElements($authors, $faker->numberBetween(1, 3))))
                ->setEdition($faker->randomElement($editions))
                ->setEnvironments(new ArrayCollection($faker->randomElements($environments, $faker->numberBetween(1, 2))))
                ->setItems(new ArrayCollection($faker->randomElements($items, $faker->numberBetween(0, 5))))
                ->setNpcs(new ArrayCollection($faker->randomElements($npcs, $faker->numberBetween(0, 6))))
                ->setPublisher($faker->randomElement($publishers))
                ->setSetting($faker->randomElement($settings))
                ->setMonsters(new ArrayCollection($faker->randomElements($monsters, $faker->numberBetween(0, 20))));

            if ($faker->boolean()) {
                $adventure->setStartingLevelRange($faker->randomElement([
                    'low', 'medium', 'high'
                ]));
            } else {
                $min = $faker->numberBetween(1, 10);
                $max = $faker->numberBetween($min + 1, 20);
                $adventure
                    ->setMinStartingLevel($min)
                    ->setMaxStartingLevel($max)
                ;
            }

            $em->persist($adventure);
        }
        $em->flush();
    }
}
