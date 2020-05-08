<?php

namespace AppBundle\Entity;


class AdventureDocument implements \JsonSerializable
{
    private $id;

    private $title;

    private $slug;

    private $score;

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
    private $commonMonsters;

    /**
     * @var string[]
     */
    private $bossMonsters;

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

    /**
     * @var string
     */
    private $partOf;

    public function __construct(
        int $id,
        array $authors,
        string $edition = null,
        array $environments,
        array $items,
        string $publisher = null,
        string $setting = null,
        array $commonMonsters,
        array $bossMonsters,
        string $title,
        string $description = null,
        string $slug,
        int $minStartingLevel = null,
        int $maxStartingLevel = null,
        string $startingLevelRange = null,
        int $numPages = null,
        string $foundIn = null,
        string $partOf = null,
        string $link = null,
        string $thumbnailUrl = null,
        bool $soloable = null,
        bool $pregeneratedCharacters = null,
        bool $tacticalMaps = null,
        bool $handouts = null,
        float $score = 0.0)
    {
        $this->id = $id;
        $this->authors = $authors;
        $this->edition = $edition;
        $this->environments = $environments;
        $this->items = $items;
        $this->publisher = $publisher;
        $this->setting = $setting;
        $this->commonMonsters = $commonMonsters;
        $this->bossMonsters = $bossMonsters;
        $this->title = $title;
        $this->description = $description;
        $this->slug = $slug;
        $this->score = $score;
        $this->minStartingLevel = $minStartingLevel;
        $this->maxStartingLevel = $maxStartingLevel;
        $this->startingLevelRange = $startingLevelRange;
        $this->numPages = $numPages;
        $this->foundIn = $foundIn;
        $this->partOf = $partOf;
        $this->link = $link;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->soloable = $soloable;
        $this->pregeneratedCharacters = $pregeneratedCharacters;
        $this->tacticalMaps = $tacticalMaps;
        $this->handouts = $handouts;
    }

    /**
     * @param Adventure $adventure
     * @return static
     */
    public static function fromAdventure(Adventure $adventure)
    {
        return new static(
            $adventure->getId(),
            $adventure->getAuthors()->map(function (Author $author) { return $author->getName(); })->getValues(),
            static::getNameOrNull($adventure->getEdition()),
            $adventure->getEnvironments()->map(function (Environment $environment) { return $environment->getName(); })->getValues(),
            $adventure->getItems()->map(function (Item $item) { return $item->getName(); })->getValues(),
            static::getNameOrNull($adventure->getPublisher()),
            static::getNameOrNull($adventure->getSetting()),
            $adventure->getCommonMonsters()->map(function (Monster $monster) { return $monster->getName(); })->getValues(),
            $adventure->getBossMonsters()->map(function (Monster $monster) { return $monster->getName(); })->getValues(),
            $adventure->getTitle(),
            $adventure->getDescription(),
            $adventure->getSlug(),
            $adventure->getMinStartingLevel(),
            $adventure->getMaxStartingLevel(),
            $adventure->getStartingLevelRange(),
            $adventure->getNumPages(),
            $adventure->getFoundIn(),
            $adventure->getPartOf(),
            $adventure->getLink(),
            $adventure->getThumbnailUrl(),
            $adventure->isSoloable(),
            $adventure->hasPregeneratedCharacters(),
            $adventure->hasTacticalMaps(),
            $adventure->hasHandouts()
        );
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
     * @return string[]
     */
    public function getCommonMonsters()
    {
        return $this->commonMonsters;
    }

    /**
     * @return string[]
     */
    public function getBossMonsters()
    {
        return $this->bossMonsters;
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
     * @return string
     */
    public function getPartOf()
    {
        return $this->partOf;
    }

    /**
     * @param $entity
     * @return null|string
     */
    private static function getNameOrNull($entity)
    {
        return $entity === null ? null : $entity->getName();
    }

    public function jsonSerialize()
    {
        // Warning: All fields listed here are publicly exposed via the API.
        // Do not list any fields containing user information.
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "slug" => $this->slug,
            "authors" => $this->authors,
            "edition" => $this->edition,
            "environments" => $this->environments,
            "items" => $this->items,
            "publisher" => $this->publisher,
            "setting" => $this->setting,
            "common_monsters" => $this->commonMonsters,
            "boss_monsters" => $this->bossMonsters,
            "min_starting_level" => $this->minStartingLevel,
            "max_starting_level" => $this->maxStartingLevel,
            "starting_level_range" => $this->startingLevelRange,
            "num_pages" => $this->numPages,
            "found_in" => $this->foundIn,
            "part_of" => $this->partOf,
            "official_url" => $this->link,
            "thumbnail_url" => $this->thumbnailUrl,
            "soloable" => $this->soloable,
            "has_pregenerated_characters" => $this->pregeneratedCharacters,
            "has_tactical_maps" => $this->tacticalMaps,
            "has_handouts" => $this->handouts
        ];
    }
}
