<?php

namespace AppBundle\Entity;


class AdventureDocument
{
    private $id;

    private $title;

    private $slug;

    private $score;

    private $info;

    public function __construct(int $id, string $title, string $slug, array $info, float $score = 0.0)
    {
        $this->id = $id;
        $this->title = $title;
        $this->slug = $slug;
        $this->score = $score;
        $this->info = $info;
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
    public function getSlug(): string
    {
        return $this->slug;
    }
}