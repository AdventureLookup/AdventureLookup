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
     * @var string[]
     */
    private $authors;

    /**
     * @var string
     */
    private $edition;

    /**
     * @var string[]
     */
    private $environments;

    /**
     * @var string[]
     */
    private $items;

    /**
     * @var string[]
     */
    private $npcs;

    /**
     * @var string
     */
    private $publisher;

    /**
     * @var string
     */
    private $setting;

    /**
     * @var string[]
     */
    private $monsters;

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
    private $startingLevelRange;

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
     * @var boolean
     */
    private $tacticalMaps;

    /**
     * @var boolean
     */
    private $handouts;

    /**
     * @var string
     */
    private $foundIn;

    public function __construct(
        int $id,
        array $authors,
        string $edition = null,
        array $environments,
        array $items,
        array $npcs,
        string $publisher = null,
        string $setting = null,
        array $monsters,
        string $title,
        string $description = null,
        string $slug,
        int $minStartingLevel = null,
        int $maxStartingLevel = null,
        string $startingLevelRange = null,
        int $numPages = null,
        string $foundIn = null,
        string $link = null,
        string $thumbnailUrl = null,
        bool $soloable = null,
        bool $pregeneratedCharacters = null,
        bool $tacticalMaps = null,
        bool $handouts = null,
        array $info = [],
        float $score = 0.0)
    {
        $this->id = $id;
        $this->authors = $authors;
        $this->edition = $edition;
        $this->environments = $environments;
        $this->items = $items;
        $this->npcs = $npcs;
        $this->publisher = $publisher;
        $this->setting = $setting;
        $this->monsters = $monsters;
        $this->title = $title;
        $this->description = $description;
        $this->slug = $slug;
        $this->score = $score;
        $this->info = $info;
        $this->minStartingLevel = $minStartingLevel;
        $this->maxStartingLevel = $maxStartingLevel;
        $this->startingLevelRange = $startingLevelRange;
        $this->numPages = $numPages;
        $this->foundIn = $foundIn;
        $this->link = $link;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->soloable = $soloable;
        $this->pregeneratedCharacters = $pregeneratedCharacters;
        $this->tacticalMaps = $tacticalMaps;
        $this->handouts = $handouts;

        $map = [
            'Author' => 'author',
            'System / Edition' => 'system',
            'Publisher' => 'publisher',
            'Environment' => 'environments',
            'Notable Items' => 'notableItems',
            'Monsters' => 'monsters',
            'Villains' => 'villains',
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

        return new static(
            $adventure->getId(),
            $adventure->getAuthors()->map(function (Author $author) { return $author->getName(); })->getValues(),
            static::getNameOrNull($adventure->getEdition()),
            $adventure->getEnvironments()->map(function (Environment $environment) { return $environment->getName(); })->getValues(),
            $adventure->getItems()->map(function (Item $item) { return $item->getName(); })->getValues(),
            $adventure->getNpcs()->map(function (NPC $npc) { return $npc->getName(); })->getValues(),
            static::getNameOrNull($adventure->getPublisher()),
            static::getNameOrNull($adventure->getSetting()),
            $adventure->getMonsters()->map(function (Monster $monster) { return $monster->getName(); })->getValues(),
            $adventure->getTitle(),
            $adventure->getDescription(),
            $adventure->getSlug(),
            $adventure->getMinStartingLevel(),
            $adventure->getMaxStartingLevel(),
            $adventure->getStartingLevelRange(),
            $adventure->getNumPages(),
            $adventure->getFoundIn(),
            $adventure->getLink(),
            $adventure->getThumbnailUrl(),
            $adventure->isSoloable(),
            $adventure->hasPregeneratedCharacters(),
            $adventure->hasTacticalMaps(),
            $adventure->hasHandouts(),
            $info
        );
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
     * @return string[]
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * @return string
     */
    public function getEdition()
    {
        return $this->edition;
    }

    /**
     * @return string[]
     */
    public function getEnvironments(): array
    {
        return $this->environments;
    }

    /**
     * @return string[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return string[]
     */
    public function getNpcs(): array
    {
        return $this->npcs;
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
    public function getStartingLevelRange()
    {
        return $this->startingLevelRange;
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
    public function hasPregeneratedCharacters()
    {
        return $this->pregeneratedCharacters;
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
     * @return string
     */
    public function getFoundIn()
    {
        return $this->foundIn;
    }

    /**
     * @param $entity
     * @return null|string
     */
    private static function getNameOrNull($entity)
    {
        return $entity === null ? null : $entity->getName();
    }
}
