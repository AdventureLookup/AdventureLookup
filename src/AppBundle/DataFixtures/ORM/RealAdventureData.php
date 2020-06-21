<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\Author;
use AppBundle\Entity\Edition;
use AppBundle\Entity\Environment;
use AppBundle\Entity\Item;
use AppBundle\Entity\Monster;
use AppBundle\Entity\Publisher;
use AppBundle\Entity\Setting;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class RealAdventureData implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     */
    public function load(ObjectManager $em)
    {
        $seed = time();
        $baseUrl = 'https://adventurelookup.com/api/adventures/';
        $adventures = [];
        for ($page = 1; count($adventures) < 200; ++$page) {
            $url = $baseUrl.'?page='.$page.'&sort=random&seed='.$seed;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $adventures = array_merge($adventures, (json_decode($result))->adventures);
        }

        $authors = [];
        $editions = [];
        $environments = [];
        $items = [];
        $publishers = [];
        $settings = [];
        $commonMonsters = [];
        $bossMonsters = [];

        foreach ($adventures as $jsonAdventure) {
            $adventure = new Adventure();
            $adventure
                ->setTitle($jsonAdventure->title)
                ->setDescription($jsonAdventure->description)
                ->setNumPages($jsonAdventure->num_pages)
                ->setFoundIn($jsonAdventure->found_in)
                ->setPartOf($jsonAdventure->part_of)
                ->setLink($jsonAdventure->official_url)
                ->setThumbnailUrl($jsonAdventure->thumbnail_url)
                ->setSoloable($jsonAdventure->soloable)
                ->setPregeneratedCharacters($jsonAdventure->has_pregenerated_characters)
                ->setTacticalMaps($jsonAdventure->has_tactical_maps)
                ->setHandouts($jsonAdventure->has_handouts)
                ->setYear($jsonAdventure->publication_year)
                ->setStartingLevelRange($jsonAdventure->starting_level_range)
                ->setMinStartingLevel($jsonAdventure->min_starting_level)
                ->setMaxStartingLevel($jsonAdventure->max_starting_level)
            ;

            if (null !== $jsonAdventure->edition) {
                if (!isset($editions[$jsonAdventure->edition])) {
                    $edition = new Edition();
                    $edition->setName($jsonAdventure->edition);
                    $edition->setPosition(count($editions));
                    $editions[$jsonAdventure->edition] = $edition;
                    $em->persist($edition);
                }
                $adventure->setEdition($editions[$jsonAdventure->edition]);
            }

            if (null !== $jsonAdventure->publisher) {
                if (!isset($publishers[$jsonAdventure->publisher])) {
                    $publisher = new Publisher();
                    $publisher->setName($jsonAdventure->publisher);
                    $publishers[$jsonAdventure->publisher] = $publisher;
                    $em->persist($publisher);
                }
                $adventure->setPublisher($publishers[$jsonAdventure->publisher]);
            }

            if (null !== $jsonAdventure->setting) {
                if (!isset($settings[$jsonAdventure->setting])) {
                    $setting = new Setting();
                    $setting->setName($jsonAdventure->setting);
                    $settings[$jsonAdventure->setting] = $setting;
                    $em->persist($setting);
                }
                $adventure->setSetting($settings[$jsonAdventure->setting]);
            }

            foreach ($jsonAdventure->authors as $jsonAuthor) {
                if (!isset($authors[$jsonAuthor])) {
                    $author = new Author();
                    $author->setName($jsonAuthor);
                    $authors[$jsonAuthor] = $author;
                    $em->persist($author);
                }
                $adventure->addAuthor($authors[$jsonAuthor]);
            }

            foreach ($jsonAdventure->environments as $jsonEnvironment) {
                if (!isset($environments[$jsonEnvironment])) {
                    $environment = new Environment();
                    $environment->setName($jsonEnvironment);
                    $environments[$jsonEnvironment] = $environment;
                    $em->persist($environment);
                }
                $adventure->addEnvironment($environments[$jsonEnvironment]);
            }

            foreach ($jsonAdventure->items as $jsonItem) {
                if (!isset($items[$jsonItem])) {
                    $item = new Item();
                    $item->setName($jsonItem);
                    $items[$jsonItem] = $item;
                    $em->persist($item);
                }
                $adventure->addItem($items[$jsonItem]);
            }

            foreach ($jsonAdventure->common_monsters as $jsonMonster) {
                if (!isset($commonMonsters[$jsonMonster])) {
                    $monster = new Monster();
                    $monster->setName($jsonMonster);
                    $monster->setIsUnique(false);
                    $commonMonsters[$jsonMonster] = $monster;
                    $em->persist($monster);
                }
                $adventure->addMonster($commonMonsters[$jsonMonster]);
            }
            foreach ($jsonAdventure->boss_monsters as $jsonMonster) {
                if (!isset($bossMonsters[$jsonMonster])) {
                    $monster = new Monster();
                    $monster->setName($jsonMonster);
                    $monster->setIsUnique(true);
                    $bossMonsters[$jsonMonster] = $monster;
                    $em->persist($monster);
                }
                $adventure->addMonster($bossMonsters[$jsonMonster]);
            }

            $em->persist($adventure);
        }

        $em->flush();
    }
}
