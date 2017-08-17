<?php


namespace AppBundle\Field;

use AppBundle\Entity\Author;
use AppBundle\Entity\Edition;
use AppBundle\Entity\Environment;
use AppBundle\Entity\Item;
use AppBundle\Entity\Monster;
use AppBundle\Entity\Publisher;
use AppBundle\Entity\Setting;
use AppBundle\Exception\FieldDoesNotExistException;
use Doctrine\Common\Collections\ArrayCollection;

class FieldProvider
{
    /**
     * @var Field[]|ArrayCollection
     */
    private $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection([
            'title' => new Field(
                'title',
                'string',
                false,
                true,
                'Title',
                'The title of the adventure.',
                3
            ),
            'description' => new Field(
                'description',
                'text',
                false,
                true,
                'Description',
                'Description of the adventure.'
            ),
            'authors' => new Field(
                'authors',
                'string',
                true,
                true,
                'Authors',
                'Names of people with writing or story credits on the module. Do not include editors or designers.',
                1,
                Author::class
            ),
            'edition' => new Field(
                'edition',
                'string',
                false,
                true,
                'System / Edition',
                'The system the game was designed for and the edition of that system if there is one.',
                1,
                Edition::class
            ),
            'environments' => new Field(
                'environments',
                'string',
                true,
                true,
                'Environments',
                'The different types of environments the module will take place in.',
                1,
                Environment::class
            ),
            'items' => new Field(
                'items',
                'string',
                true,
                true,
                'Notable Items',
                "The notable magic or non-magic items that are obtained in the module. Only include named items, don't include a +1 sword.",
                1,
                Item::class
            ),
            'publisher' => new Field(
                'publisher',
                'string',
                false,
                true,
                'Publisher',
                'Publisher of the adventure.',
                1,
                Publisher::class
            ),
            'setting' => new Field(
                'setting',
                'string',
                false,
                true,
                'Setting',
                'The narrative universe the module is set in.',
                1,
                Setting::class
            ),
            'commonMonsters' => new Field(
                'commonMonsters',
                'string',
                true,
                true,
                'Common Monsters',
                'The common monsters featured in the module.',
                1,
                Monster::class
            ),
            'bossMonsters' => new Field(
                'bossMonsters',
                'string',
                true,
                true,
                'Boss Monsters',
                'The boss monsters and villains featured in the module.',
                1,
                Monster::class
            ),


            'numPages' => new Field(
                'numPages',
                'integer',
                false,
                false,
                'Length (# of Pages)',
                'Total page count of all written material in the module or at least primary string.'
            ),
            'minStartingLevel' => new Field(
                'minStartingLevel',
                'integer',
                false,
                false,
                'Min. Starting Level',
                'The minimum level characters are expected to be when taking part in the module.'
            ),
            'maxStartingLevel' => new Field(
                'maxStartingLevel',
                'integer',
                false,
                false,
                'Max. Starting Level',
                'The maximum level characters are expected to be when taking part in the module.'
            ),
            'startingLevelRange' => new Field(
                'startingLevelRange',
                'string',
                false,
                false,
                'Starting Level Range',
                'In case no min. / max. starting levels but rather low/medium/high are given.'
            ),


            'soloable' => new Field(
                'soloable',
                'boolean',
                false,
                false,
                'Suitable for Solo Play'
            ),
            'pregeneratedCharacters' => new Field(
                'pregeneratedCharacters',
                'boolean',
                false,
                false,
                'Includes Pregenerated Characters'
            ),
            'handouts' => new Field(
                'handouts',
                'boolean',
                false,
                false,
                'Handouts'
            ),
            'tacticalMaps' => new Field(
                'tacticalMaps',
                'boolean',
                false,
                false,
                'Tactical Maps'
            ),

            'foundIn' => new Field(
                'foundIn',
                'string',
                false,
                true,
                'Found In',
                'If the adventure is part of a larger product, like a magazine or anthology, list it here.'
            ),
            'partOf' => new Field(
                'partOf',
                'string',
                false,
                true,
                'Part Of',
                'The series of adventures that the module is a part of, if applicable.'
            ),

            'link' => new Field(
                'link',
                'url',
                false,
                false,
                'Link',
                'Links to legitimate sites where the module can be procured.'
            ),
            'thumbnailUrl' => new Field(
                'thumbnailUrl',
                'url',
                false,
                false,
                'Thumbnail URL',
                'URL of the thumbnail image.'
            ),
        ]);
    }

    /**
     * @return Field[]|ArrayCollection
     */
    public function getFields(): ArrayCollection
    {
        return $this->fields;
    }

    /**
     * @param $name
     * @return Field
     */
    public function getField($name): Field
    {
        if (!$this->fields->containsKey($name)) {
            throw new FieldDoesNotExistException(sprintf('Field with id "%s" does not exist!', $name));
        }

        return $this->fields->get($name);
    }
}
