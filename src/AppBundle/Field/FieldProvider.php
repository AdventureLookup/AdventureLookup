<?php


namespace AppBundle\Field;

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
                'The title of the adventure.'
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
                'Names of people with writing or story credits on the module. Do not include editors or designers.'
            ),
            'edition' => new Field(
                'edition',
                'string',
                false,
                true,
                'System / Edition',
                'The system the game was designed for and the edition of that system if there is one.'
            ),
            'environments' => new Field(
                'environments',
                'string',
                true,
                true,
                'Environments',
                'The different types of environments the module will take place in.'
            ),
            'items' => new Field(
                'items',
                'string',
                true,
                true,
                'Notable Items',
                "The notable magic or non-magic items that are obtained in the module. Only include named items, don't include a +1 sword."),
            'npcs' => new Field(
                'npcs',
                'string',
                true,
                true,
                'NPCs',
                'Names of notable NPCs'
            ),
            'publisher' => new Field(
                'publisher',
                'string',
                false,
                true,
                'Publisher',
                'Publisher of the adventure.'
            ),
            'setting' => new Field(
                'setting',
                'string',
                false,
                true,
                'Setting',
                'The narrative universe the module is set in.'
            ),
            'monsters' => new Field(
                'monsters',
                'string',
                true,
                true,
                'Monsters',
                'The various types of creatures featured in the module.'
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
                'Level Range',
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
                'The place the adventure can be found in.'
            ),
            'partOf' => new Field(
                'partOf',
                'string',
                false,
                true,
                'Part Of',
                'The series of adventures that the module is a part of, if applicable.'
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
