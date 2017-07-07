<?php

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\TagContent;
use AppBundle\Entity\TagName;
use AppBundle\Service\FieldUtils;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TagData implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Adventure title is always there.

        // General information
        $this->createTag(
            'Author', 'Names of people with writing or story credits on the module', 'Ed Greenwood', 'string', [], $manager
        );
        $this->createTag(
            'System / Edition', 'The system the game was designed for and the edition of that system if there is one.', 'Pathfinder, 4th Edition', 'string', explode(', ', "OD&D, AD&D, BECMI. AD&D 2, 3rd Edition, 3.5, Pathfinder, 4th Edition, 4th Essentials, 5th Edition, OSR, DCC"), $manager, true, true
        );
        $this->createTag('Publisher', 'Publisher of the module', 'WotC, Goodman Games', 'string', explode(', ', 'TSR, WotC, Paizo, Goodman Games, Necromancer Games, Judge\'s Guild'), $manager, true, true
        );
        $this->createTag(
            'Setting', 'The narrative universe the module is set in.', 'Forgotten Realms, Dark Sun', 'string', explode(', ', 'Greyhawk, Blackmoor, Dragonlance, Forgotten Realms, Mystara, Dark Sun, Birthright, Eberron, Ravenloft'), $manager
        );
        $this->createTag('Min. Starting Level', 'The minimum level characters are expected to be when taking part in the module', '5', 'integer', [], $manager, true
        );
        $this->createTag('Max. Starting Level', 'The maximum level characters are expected to be when taking part in the module', '17', 'integer', [], $manager
        );
        $this->createTag('Level Range', 'In case no min. / max. starting levels but rather low/medium/high are given', 'low, medium, high', 'string', ['low', 'medium', 'high'], $manager
        );
        $this->createTag('Suitable for Solo Play', 'Whether or not this is suited to be played solo.', '1 or 0', 'boolean', [], $manager);
        $this->createTag('Length (# of Pages)', 'Total page count of all written material in the module or at least primary string', '192', 'integer', [], $manager
        );
        $this->createTag('Includes Pregenerated Characters', 'Whether or not this contains character sheets.', '1 or 0', 'boolean', [], $manager);
        $this->createTag(
            'Environment', 'The different types of environments the module will take place in', 'Dungeon, Wilderness, Swamp, City, Town, Ship, Underdark, Underwater, Stronghold, Planes', 'string', explode(', ', 'Dungeon, Wilderness, Swamp, City, Town, Ship, Underdark, Underwater, Stronghold, Planes'), $manager, true, true
        );
        $this->createTag(
            'Link', 'Links to legitimate sites where the module can be procured. Make sure to include http:// oder https:// !', 'dmsguild.com / drivethrurpg links', 'url', [], $manager
        );
        $this->createTag(
            'Thumbnail', 'URL of the thumbnail image. Make sure to include http:// oder https:// !', '', 'url', [], $manager
        );
        $this->createTag('Description', 'Description of the module', 'The master of Ravenloft is having guests for dinner, and you are invited.', 'text', [], $manager
        );

        // DM details
        $this->createTag('Notable Items', 'The notable magic or non-magic items that are obtained in the module. Only include named items, don\'t include a +1 sword.', 'Decanter of Endless Water, Sword of Kas, Elven Cloak', 'string', [], $manager
        );
        $this->createTag('Monsters', 'The various types of creatures featured in the module', 'Skeleton, Orc, Blue Dragon, Hill Giant', 'string', [], $manager
        );
        $this->createTag('Tactical Maps', 'Whether or not tactical maps are provided', '1 or 0', 'boolean', [], $manager, true, true
        );
        $this->createTag('Handouts', 'Whether or not handouts are provided', '1 or 0', 'boolean', [], $manager, true, true
        );

        $manager->flush();
    }

    private function createTag($title, $desc, $example, $type, $defaults = [], ObjectManager $manager, bool $showInSearchResults = false, bool $useAsFilter = false)
    {
        $field = new TagName();
        $field->setTitle($title)
            ->setApproved(true)
            ->setType($type)
            ->setDescription($desc)
            ->setExample($example)
            ->setUseAsFilter($useAsFilter)
            ->setShowInSearchResults($showInSearchResults);

        //$fieldUtils = new FieldUtils();
        //foreach ($defaults as $default) {
        //    $fieldContent = new TagContent();
        //    $fieldContent->setTag($field);
        //    $fieldContent->setApproved(true);
        //    $fieldContent->setContent($fieldUtils->serialize($type, $default));
        //    $manager->persist($fieldContent);
        //}
        if (count($defaults) > 0) {
            $field->setExample(implode(', ', $defaults));
        }

        $manager->persist($field);
    }
}