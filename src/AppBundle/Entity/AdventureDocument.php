<?php

namespace AppBundle\Entity;

class AdventureDocument
{
    private $id;

    private $title;

    private $slug;

    /**
     * @var float|null
     */
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
     * @var int
     */
    private $minStartingLevel;

    /**
     * @var int
     */
    private $maxStartingLevel;

    /**
     * @var int
     */
    private $startingLevelRange;

    /**
     * @var bool
     */
    private $soloable;

    /**
     * @var int
     */
    private $numPages;

    /**
     * @var bool
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
     * @var bool
     */
    private $tacticalMaps;

    /**
     * @var bool
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

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $numPositiveReviews;

    /**
     * @var int
     */
    private $numNegativeReviews;

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
        int $year = null,
        int $numPositiveReviews = 0,
        int $numNegativeReviews = 0,
        float $score = null)
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
        $this->year = $year;
        $this->numPositiveReviews = $numPositiveReviews;
        $this->numNegativeReviews = $numNegativeReviews;
    }

    /**
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
            $adventure->hasHandouts(),
            $adventure->getYear(),
            $adventure->getNumberOfThumbsUp(),
            $adventure->getNumberOfThumbsDown()
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return float
     */
    public function getScore()
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
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return int|null
     */
    public function getNumPositiveReviews()
    {
        return $this->numPositiveReviews;
    }

    /**
     * @return int|null
     */
    public function getNumNegativeReviews()
    {
        return $this->numNegativeReviews;
    }

    /**
     * @param $entity
     *
     * @return string|null
     */
    private static function getNameOrNull($entity)
    {
        return null === $entity ? null : $entity->getName();
    }
}
