<?php

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\TagName;
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
        // General information
        $this->createTag(
            'Author',
            'Names of people with writing or story credits on the module',
            'Ed Greenwood',
            'string',
            $manager
        );

        $this->createTag(
            'Designer',
            'Names of people with design credits on the module',
            'Mike Mearls, Jeremy Crawford ',
            'string',
            $manager
        );
        $this->createTag(
            'Cover Artist',
            'Names of any artists involved with the cover artwork for the module',
            'Raymond Swanland',
            'string',
            $manager
        );
        $this->createTag('Interior Artist',
            'Names of any artists involved with the page artwork for the module',
            'Tom Babbey, Daren Bader, John-Paul Balmet',
            'string',
            $manager
        );
        $this->createTag('Map Artist',
            'Names of any artists involved in creating maps for the module',
            'Mike Schley',
            'string',
            $manager
        );
        $this->createTag('Publisher',
            'Publisher of the module',
            'Wizards of the Coast, Sword & Sorcery',
            'string',
            $manager,
            true
        );
        $this->createTag('# of Pages',
            'Total page count of all written material in the module or at least primary string',
            '192',
            'integer',
            $manager
        );
        $this->createTag('Year of Release',
            'Year the module was published',
            '2016',
            'integer',
            $manager
        );
        $this->createTag(
            'Language',
            'List of any languages the module is available in',
            'English, Italian, German',
            'string',
            $manager
        );
        $this->createTag(
            'Availability',
            'What is the current avilability of the module',
            'In Print, Out of Print, Print on Demand, Digital',
            'string',
            $manager
        );
        $this->createTag(
            'Available Formats',
            'The different formats the module is available in',
            'PDF, Epub, Paper',
            'string',
            $manager
        );
        $this->createTag(
            'Link',
            'Links to legitimate sites where the module can be procured',
            'dmsguild.com/drivethrurpg links',
            'string',
            $manager
        );

        // Adventure setting
        $this->createTag(
            'System / Edition',
            'The system the game was designed for and the edition of that system if there is one.',
            'D&D 3.5, Pathfinder, 13th Age',
            'string',
            $manager,
            true
        );
        $this->createTag(
            'Setting',
            'The narrative universe the module is set in.',
            'Forgotten Realms, Dark Sun',
            'string',
            $manager,
            true
        );
        $this->createTag(
            'Region',
            'The region within the modules universe that it takes place',
            'Sword Coast, Great Salt Flats',
            'string',
            $manager
        );
        $this->createTag(
            'Environment',
            'The different types of environs the module will take place in',
            'Urban, Forest, Swamp, Cavern, Dungeon, Temple',
            'string',
            $manager,
            true
        );
        $this->createTag(
            'Adventure Set / Storyline',
            'The set of modules or narrative the module is part of',
            'Rise of the Runelords/Rise of Tiamat/Heroic Standalone',
            'string',
            $manager
        );
        $this->createTag('Description',
            'Description of the module',
            'The master of Ravenloft is having guests for dinner, and you are invited.',
            'text',
            $manager
        );
        $this->createTag('Magic Level',
            'How prevalent is magic and access to magic items within the module',
            'Low Magic, High Magic, Magitech',
            'string',
            $manager
        );
        $this->createTag('Alignment',
            'The alignment the participating characters expected to have',
            'Chaotic, Evil, Any',
            'string',
            $manager
        );
        $this->createTag('Race / Social Class',
            'The race or social standing chatacters are expected to belong to',
            'Elves, Dwarves, Nobles, Aristocracy',
            'string',
            $manager
        );
        $this->createTag('Min. Starting Level',
            'The minimum level characters are expected to be when taking part in the module',
            '5',
            'integer',
            $manager,
            true
        );
        $this->createTag('Final level',
            'The expected final level the characters at the end of the module',
            '17',
            'integer',
            $manager
        );
        $this->createTag('Level progression',
            'The means by which the players advance in level during the module',
            'Milestones, XP',
            'string',
            $manager
        );
        $this->createTag('Min. # of PCs',
            'How many players of the specified levels the module is balanced against',
            '4',
            'integer',
            $manager,
            true
        );
        $this->createTag('Max. # of PCs',
            'How many players of the specified levels the module is balanced against',
            '6',
            'integer',
            $manager
        );

        // DM details
        $this->createTag('Items',
            'The various notable magic items or non-magic items that are obtained in the module',
            'Horn of Wonders, Crown of Andor',
            'string',
            $manager
        );
        $this->createTag('NPCs',
            'The various types of NPCs featuring in the module',
            'Raistlin Majere, King Osric',
            'string',
            $manager
        );
        $this->createTag('Creatures',
            'The various types of creatures featuring in the module',
            'Skeletons, Orcs, Goblins',
            'string',
            $manager
        );
        $this->createTag('Tactical Maps',
            'Whether or not tactical maps are provided',
            '1 or 0',
            'boolean',
            $manager
        );
        $this->createTag('Handouts',
            'Whether or not handouts are provided',
            '1 or 0',
            'boolean',
            $manager
        );

        $manager->flush();
    }

    private function createTag($title, $desc, $example, $type, ObjectManager $manager, bool $showInSearchResults = false)
    {
        $tag = new TagName();
        $tag->setTitle($title)
            ->setApproved(true)
            ->setType($type)
            ->setDescription($desc)
            ->setExample($example)
            ->setShowInSearchResults($showInSearchResults);

        $manager->persist($tag);
    }
}