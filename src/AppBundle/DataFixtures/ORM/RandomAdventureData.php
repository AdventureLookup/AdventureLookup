<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Author;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\Edition;
use AppBundle\Entity\Environment;
use AppBundle\Entity\Item;
use AppBundle\Entity\Monster;
use AppBundle\Entity\Publisher;
use AppBundle\Entity\Review;
use AppBundle\Entity\Setting;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ReflectionClass;
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
            PublisherData::class, SettingData::class, ];
    }

    /**
     * Load data fixtures with the passed EntityManager
     */
    public function load(ObjectManager $em)
    {
        $isHeroku = 'heroku' === $this->container->getParameter('kernel.environment');

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
        /** @var Publisher[] $publishers */
        $publishers = $doctrine->getRepository('AppBundle:Publisher')->findAll();
        /** @var Setting[] $settings */
        $settings = $doctrine->getRepository('AppBundle:Setting')->findAll();
        /** @var Monster[] $monsters */
        $monsters = $doctrine->getRepository('AppBundle:Monster')->findAll();

        $faker = Faker\Factory::create();
        $faker->addProvider(new \Mmo\Faker\PicsumProvider($faker));

        $reviewCreatedByProperty = (new ReflectionClass(Review::class))->getProperty('createdBy');
        $reviewCreatedByProperty->setAccessible(true);

        // Create less adventures on Heroku. The free tier database only allows 10000 rows
        // and 200 adventures use more than 10000 rows.
        $count = $isHeroku ? 50 : 200;
        for ($i = 0; $i < $count; ++$i) {
            $adventure = new Adventure();
            $adventure
                ->setTitle($faker->unique->catchPhrase)
                ->setDescription($faker->realText(2000))
                ->setNumPages($faker->numberBetween(1, 200))
                ->setFoundIn($faker->catchPhrase)
                ->setPartOf($faker->boolean() ? $faker->catchPhrase : null)
                ->setLink($faker->url)
                ->setThumbnailUrl($faker->picsumUrl(260, 300))
                ->setSoloable($faker->boolean())
                ->setPregeneratedCharacters($faker->boolean())
                ->setTacticalMaps($faker->boolean())
                ->setHandouts($faker->boolean())
                ->setAuthors(new ArrayCollection($faker->randomElements($authors, $faker->numberBetween(1, 3))))
                ->setEdition($faker->randomElement($editions))
                ->setEnvironments(new ArrayCollection($faker->randomElements($environments, $faker->numberBetween(1, 2))))
                ->setItems(new ArrayCollection($faker->randomElements($items, $faker->numberBetween(0, 5))))
                ->setPublisher($faker->randomElement($publishers))
                ->setYear($faker->numberBetween(1980, 2020))
                ->setSetting($faker->randomElement($settings))
                ->setMonsters(new ArrayCollection($faker->randomElements($monsters, $faker->numberBetween(0, 20))));

            if ($faker->boolean(20)) {
                $n = $faker->numberBetween(1, 5);
                for ($j = 0; $j < $n; ++$j) {
                    $changeRequest = new ChangeRequest();
                    $changeRequest
                        ->setComment($faker->realText($faker->numberBetween(20, 500)))
                        ->setResolved($faker->boolean())
                        ->setAdventure($adventure);
                    if ($faker->boolean(50)) {
                        $changeRequest->setFieldName($faker->randomElement([
                            'link', 'description', 'title', 'minStartingLevel',
                        ]));
                    }
                    if ($faker->boolean(30)) {
                        $changeRequest->setCuratorRemarks($faker->realText($faker->numberBetween(20, 200)));
                    }
                    $em->persist($changeRequest);
                }
            }

            if ($faker->boolean(80)) {
                $n = $faker->numberBetween(1, 20);
                for ($j = 0; $j < $n; ++$j) {
                    $review = new Review($adventure);
                    if ($faker->boolean) {
                        $review->setThumbsUp();
                    } else {
                        $review->setThumbsDown();
                    }
                    if ($faker->boolean(70)) {
                        $review->setComment($faker->realText($faker->numberBetween(20, 500)));
                    }

                    $reviewCreatedByProperty->setValue($review, $j.'-'.$faker->userName);

                    $em->persist($review);
                }
            }

            if ($faker->boolean()) {
                $adventure->setStartingLevelRange($faker->randomElement([
                    'low', 'medium', 'high',
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
            if ($isHeroku && 9 === $i % 10) {
                // Flush more often on Heroku to not run into the memory limit.
                $em->flush();
            }
        }
        $em->flush();
    }
}
