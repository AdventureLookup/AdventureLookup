<?php

namespace AppBundle\Service;


use AppBundle\Entity\Adventure;

class AdventureSerializer
{
    public function toElasticDocument(Adventure $adventure): array
    {
        $ser = [
            'setting' => $adventure->getSetting()->getName(),
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
}
