<?php

namespace AppBundle\Entity;


class AdventureDocument
{
    private $id;

    private $title;

    private $slug;

    private $score;

    private $info;

    /**
     * @var string
     */
    private $system;

    /**
     * @var string
     */
    private $publisher;

    /**
     * @var string
     */
    private $setting;

    /**
     * @var integer
     */
    private $minStartingLevel;

    /**
     * @var integer
     */
    private $maxStartingLevel;

    /**
     * @var integer
     */
    private $levelRange;

    /**
     * @var boolean
     */
    private $soloable;

    /**
     * @var integer
     */
    private $numPages;

    /**
     * @var boolean
     */
    private $pregeneratedCharacters;

    /**
     * @var string[]
     */
    private $environments;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $thumbnailUrl;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string[]
     */
    private $notableItems;

    /**
     * @var string[]
     */
    private $monsters;

    /**
     * @var boolean
     */
    private $tacticalMaps;

    /**
     * @var boolean
     */
    private $handouts;

    /**
     * @var string[]
     */
    private $villains;

    /**
     * @var string
     */
    private $foundIn;

    public function __construct(int $id, string $title, string $slug, array $info, float $score = 0.0)
    {
        $this->id = $id;
        $this->title = $title;
        $this->slug = $slug;
        $this->score = $score;
        $this->info = $info;

        $map = [
            'Author' => 'author',
            'System / Edition' => 'system',
            'Publisher' => 'publisher',
            'Setting' => 'setting',
            'Min. Starting Level' => 'minStartingLevel',
            'Max. Starting Level' => 'maxStartingLevel',
            'Level Range' => 'levelRange',
            'Suitable for Solo Play' => 'soloable',
            'Length (# of Pages)' => 'numPages',
            'Includes Pregenerated Characters' => 'pregeneratedCharacters',
            'Environment' => 'environments',
            'Link' => 'link',
            'Thumbnail' => 'thumbnailUrl',
            'Description' => 'description',
            'Notable Items' => 'notableItems',
            'Monsters' => 'monsters',
            'Tactical Maps' => 'tacticalMaps',
            'Handouts' => 'handouts',
            'Villains' => 'villains',
            'Found in ' => 'foundIn'
        ];

        foreach ($info as $infoObj) {
            $fieldTitle = $infoObj['meta']->getTitle();
            if (isset($map[$fieldTitle])) {
                $fieldName = $map[$fieldTitle];
                $this->$fieldName = $infoObj['contents'];
            }
        }
    }

    public static function fromAdventure(Adventure $adventure)
    {
        $info = [];
        foreach ($adventure->getInfo() as $fieldContent) {
            $key = $fieldContent->getTag()->getId();
            if (!isset($info[$key])) {
                $info[$key] = [
                    'meta' => $fieldContent->getTag(),
                    'contents' => [],
                ];
            }
            $info[$key]['contents'][] = $fieldContent;
        }

        return new static($adventure->getId(), $adventure->getTitle(), $adventure->getSlug(), $info);
    }

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return float
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @return string
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * @return int
     */
    public function getMinStartingLevel()
    {
        return $this->minStartingLevel;
    }

    /**
     * @return int
     */
    public function getMaxStartingLevel()
    {
        return $this->maxStartingLevel;
    }

    /**
     * @return int
     */
    public function getLevelRange()
    {
        return $this->levelRange;
    }

    /**
     * @return bool
     */
    public function isSoloable()
    {
        return $this->soloable;
    }

    /**
     * @return int
     */
    public function getNumPages()
    {
        return $this->numPages;
    }

    /**
     * @return bool
     */
    public function isPregeneratedCharacters()
    {
        return $this->pregeneratedCharacters;
    }

    /**
     * @return \string[]
     */
    public function getEnvironments()
    {
        return $this->environments;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnailUrl;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \string[]
     */
    public function getNotableItems()
    {
        return $this->notableItems;
    }

    /**
     * @return \string[]
     */
    public function getMonsters()
    {
        return $this->monsters;
    }

    /**
     * @return bool
     */
    public function isTacticalMaps()
    {
        return $this->tacticalMaps;
    }

    /**
     * @return bool
     */
    public function isHandouts()
    {
        return $this->handouts;
    }

    /**
     * @return \string[]
     */
    public function getVillains()
    {
        return $this->villains;
    }

    /**
     * @return string
     */
    public function getFoundIn()
    {
        return $this->foundIn;
    }
}