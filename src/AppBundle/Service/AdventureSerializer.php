<?php

namespace AppBundle\Service;


use AppBundle\Entity\Adventure;
use AppBundle\Entity\Author;
use AppBundle\Entity\Environment;
use AppBundle\Entity\Item;
use AppBundle\Entity\Monster;
use AppBundle\Entity\NPC;

class AdventureSerializer
{
    public function toElasticDocument(Adventure $adventure): array
    {
        $ser = [
            'authors' => $adventure->getAuthors()->map(function (Author $author) { return $author->getName(); })->getValues(),
            'edition' => $this->getNameOrNull($adventure->getEdition()),
            'environments' => $adventure->getEnvironments()->map(function (Environment $environment) { return $environment->getName(); })->getValues(),
            'items' => $adventure->getItems()->map(function (Item $item) { return $item->getName(); })->getValues(),
            'npcs' => $adventure->getNpcs()->map(function (NPC $npc) { return $npc->getName(); })->getValues(),
            'publisher' => $this->getNameOrNull($adventure->getPublisher()),
            'setting' => $this->getNameOrNull($adventure->getSetting()),
            'monsters' => $adventure->getMonsters()->map(function (Monster $monster) { return $monster->getName(); })->getValues(),

            'title' => $adventure->getTitle(),
            'description' => $adventure->getDescription(),
            'slug' => $adventure->getSlug(),
            'minStartingLevel' => $adventure->getMinStartingLevel(),
            'maxStartingLevel' => $adventure->getMaxStartingLevel(),
            'startingLevelRange' => $adventure->getStartingLevelRange(),
            'numPages' => $adventure->getNumPages(),
            'foundIn' => $adventure->getFoundIn(),
            'link' => $adventure->getLink(),
            'thumbnailUrl' => $adventure->getThumbnailUrl(),
            'soloable' => $adventure->isSoloable(),
            'pregeneratedCharacters' => $adventure->hasPregeneratedCharacters(),
            'tacticalMaps' => $adventure->hasTacticalMaps(),
            'handouts' => $adventure->hasHandouts(),
        ];
        $fieldUtils = new FieldUtils();

        foreach($adventure->getInfo() as $info) {
            $tag = $info->getTag();
            $key = 'info_' . $tag->getId();
            if (!isset($ser[$key])) {
                $ser[$key] = [];
            }
            $content = $info->getContent();
            $ser[$key][] = $fieldUtils->serialize($tag->getType(), $content);
        }

        return $ser;
    }

    /**
     * @param $entity
     * @return null|string
     */
    private function getNameOrNull($entity)
    {
        return $entity === null ? null : $entity->getName();
    }
}
