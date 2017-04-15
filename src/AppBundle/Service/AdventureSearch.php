<?php

namespace AppBundle\Service;


use AppBundle\Entity\AdventureDocument;
use AppBundle\Listener\SearchIndexUpdater;
use Elasticsearch\ClientBuilder;

class AdventureSearch
{
    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->build();
    }

    /**
     * @return AdventureDocument[]
     */
    public function all()
    {
        $result = $this->client->search([
            'index' => SearchIndexUpdater::INDEX,
            'type' => SearchIndexUpdater::TYPE,
            'body' => [
                'query' => [
                    'match_all' => new \stdClass()
                ],
                'size' => 10000
            ]
        ]);

        return $this->searchResultsToAdventureDocuments($result);
    }

    /**
     * @param string $q
     * @return AdventureDocument[]
     */
    public function searchAll(string $q)
    {
        $terms = explode(',', $q);

        $queries = [];
        foreach($terms as $term) {
            if (trim($term) == "") {
                continue;
            }
            $queries[] = [
                'multi_match' => [
                    'query' => $term,
                    'fields' => ['title', 'info_*'],
                    'lenient' => true,
                    'type' => 'phrase'
                ]
            ];
        }

        $result = $this->client->search([
            'index' => SearchIndexUpdater::INDEX,
            'type' => SearchIndexUpdater::TYPE,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $queries
                    ],
                ],
                'min_score' => 1,
                'size' => 100
            ]
        ]);

        return $this->searchResultsToAdventureDocuments($result);
    }

    /**
     * @param array $filters
     * @return AdventureDocument[]
     */
    public function searchFilter(array $filters)
    {
        $matches = [];
        foreach ($filters as $id => $filter) {
            if ($id !== 'title' && !is_numeric($id)) {
                continue;
            }
            $content = $filter['c'];
            if ($content === "") {
                continue;
            }

            $field = is_integer($id) ? 'info_' . (int)$id : 'title';
            $operator = $filter['o'];

            if (in_array($operator, ['gte', 'gt', 'lt', 'lte'])) {
                $matches[] = ['range' => [$field => [$operator => $content]]];
            } else if ($operator == 'eq') {
                $matches[] = ['term' => [$field => $content]];
            } else {
                $matches[] = ['match' => [$field => $content]];
            }
        }
        if (empty($matches)) {
            return $this->all();
        }


        $result = $this->client->search([
            'index' => SearchIndexUpdater::INDEX,
            'type' => SearchIndexUpdater::TYPE,
            'body' => [
                'query' => [
                    'bool' => [
                        "must" => $matches
                    ]
                ],
                'min_score' => 1,
                'size' => 100
            ]
        ]);

        return $this->searchResultsToAdventureDocuments($result);
    }

    /**
     * @param array $result
     * @return AdventureDocument[]
     */
    private function searchResultsToAdventureDocuments(array $result): array
    {
        return array_map(function ($hit) {
            return new AdventureDocument($hit['_id'], $hit['_source']['title'], $hit['_source']['slug'], [], $hit['_score']);
        }, $result['hits']['hits']);
    }
}