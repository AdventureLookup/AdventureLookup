<?php

namespace AppBundle\Service;


use AppBundle\Entity\AdventureDocument;
use AppBundle\Entity\TagName;
use AppBundle\Listener\SearchIndexUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\ClientBuilder;

class AdventureSearch
{
    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->client = ClientBuilder::create()->build();
        $this->em = $em;
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
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('f')
            ->from(TagName::class, 'f', 'f.id');
        /** @var TagName[] $fields */
        $fields = $qb->getQuery()->execute();

        $fieldUtils = new FieldUtils();

        $matches = [];
        foreach ($filters as $id => $filter) {
            if ($id !== 'title' && !is_numeric($id)) {
                continue;
            }
            $content = $filter['c'];
            if ($content === "") {
                continue;
            }

            $field = $fieldUtils->getFieldNameById($id);
            $operator = $filter['o'];

            if (in_array($operator, ['gte', 'gt', 'lt', 'lte'])) {
                $matches[] = ['range' => [$field => [$operator => $content]]];
            } else if ($operator == 'eq') {
                $fieldEntity = $fields[$id];
                if ($fieldEntity->getType() == 'string') {
                    $field .= '.keyword';
                }
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

    public function autocompleteFieldContent(TagName $field, string $q): array
    {
        if ($q === '') {
            return current($this->aggregateMostCommonValues([$field], 10));
        }
        // Using the completion suggester returns duplicate documents...
        //$fieldName = 'info_' . $field->getId() . '_s';
        //$response = $this->client->suggest([
        //    'index' => SearchIndexUpdater::INDEX,
        //    'body' => [
        //        'suggest' => [
        //            'prefix' => $q,
        //            'completion' => [
        //                'field' => $fieldName,
        //                'fuzzy' => new \stdClass()
        //            ]
        //        ],
        //    ]
        //]);
        //$results = [
        //    'total' => count($response['suggest'][0]['options']),
        //    'results' => []
        //];
        //foreach($response['suggest'][0]['options'] as $suggestion) {
        //    $results['results'][] = $suggestion['text'];
        //}
        //return $results;

        // Old version using match_phrase_prefix
        $fieldUtils = new FieldUtils();

        $fieldName = $fieldUtils->getFieldName($field);
        $size = 10;

        $response = $this->client->search([
            'index' => SearchIndexUpdater::INDEX,
            'type' => SearchIndexUpdater::TYPE,
            'body' => [
                'query' => [
                    'match_phrase_prefix' => [
                        $fieldName => $q
                    ]
                ],
                'size' => $size,
                '_source' => false,
                "highlight" => [
                    'pre_tags' => [''],
                    'post_tags' => [''],
                    'fields' => [
                        $fieldName => new \stdClass()
                    ]
                ],
            ]
        ]);

        $results = [];
        foreach($response['hits']['hits'] as $hit) {
            if (!isset($hit['highlight'])) {
                continue;
            }
            $highlights = array_unique($hit['highlight'][$fieldUtils->getFieldName($field)]);
            foreach ($highlights as $highlight) {
                if (!in_array($highlight, $results)) {
                    $results[] = $highlight;
                }
            }
        }

        return $results;
    }

    /**
     * @param TagName[] $fields
     * @param int $max
     * @return array
     */
    public function aggregateMostCommonValues(array $fields, int $max = 3): array
    {
        $aggregations = [];
        $fieldUtils = new FieldUtils();
        foreach ($fields as $fieldEntity) {
            $elasticField = $fieldUtils->getFieldNameForAggregation($fieldEntity);
            if (!$elasticField) {
                // This field cannot be aggregated.
                continue;
            }
            $aggregations[$fieldUtils->getFieldNameById($fieldEntity)] = [
                'terms' => [
                    'field' => $elasticField,
                    'size' => $max
                ]
            ];
        }

        $response = $this->client->search([
            'index' => SearchIndexUpdater::INDEX,
            'type' => SearchIndexUpdater::TYPE,
            'body' => [
                'size' => 0,
                'aggregations' => $aggregations
            ],
            'request_cache' => true,
        ]);

        $results = [];
        foreach ($response['aggregations'] as $field => $aggregation) {
            $results[$field] = array_column($aggregation['buckets'], 'key');
        }

        return $results;
    }

    /**
     * @param array $result
     * @return AdventureDocument[]
     */
    private function searchResultsToAdventureDocuments(array $result): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('f')
            ->from(TagName::class, 'f', 'f.id');
        $fields = $qb->getQuery()->execute();

        return array_map(function ($hit) use ($fields) {
            $infos = $hit['_source'];
            unset($infos['slug']);
            unset($infos['title']);

            $infoArr = [];
            foreach ($infos as $id => $info) {
                $id = substr($id, strlen('info_'));
                $infoArr[$id] = [
                    'meta' => $fields[$id],
                    'contents' => $info
                ];
            }

            return new AdventureDocument($hit['_id'], $hit['_source']['title'], $hit['_source']['slug'], $infoArr, $hit['_score']);
        }, $result['hits']['hits']);
    }
}