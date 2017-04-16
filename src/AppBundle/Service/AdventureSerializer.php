<?php
/**
 * Created by PhpStorm.
 * User: cmfcm
 * Date: 13.04.2017
 * Time: 19:06
 */

namespace AppBundle\Service;


use AppBundle\Entity\Adventure;

class AdventureSerializer
{
    public function toElasticDocument(Adventure $adventure): array
    {
        $ser = [
            'title' => $adventure->getTitle(),
            'slug' => $adventure->getSlug(),
        ];

        foreach($adventure->getInfo() as $info) {
            $tag = $info->getTag();
            $key = 'info_' . $tag->getId();
            if (!isset($ser[$key])) {
                $ser[$key] = [];
            }
            $content = $info->getContent();
            switch ($tag->getType()) {
                case 'boolean':
                    $ser[$key][] = (bool)$content;
                    break;
                /** @noinspection PhpMissingBreakStatementInspection */
                default:
                case 'string':
                case 'text':
                    $ser[$key][] = $content;
                    break;
                case 'integer':
                    $ser[$key][] = (int)$content;
                    break;
            }
        }

        return $ser;
    }
}