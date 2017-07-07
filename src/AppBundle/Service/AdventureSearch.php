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
     * @param string $q
     * @param array $filters
     * @return AdventureDocument[]
     */
    public function search(string $q, array $filters)
    {
        $matches = [];
        $matches = $this->qMatches($q, $matches);
        $matches = $this->filterMatches($filters, $matches);
        if (empty($matches)) {
            $matches = ['match_all' => new \stdClass()];
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
                'size' => 1000,
                'aggs' => $this->fieldAggregations(),
            ],
            'explain' => true,
        ]);

        return [$this->searchResultsToAdventureDocuments($result), $result['aggregations']];
    }

    public function similarTitles($title): array
    {
        if ($title === '') {
            return [];
        }

        $fieldUtils = new FieldUtils();

        $result = $this->client->search([
            'index' => SearchIndexUpdater::INDEX,
            'type' => SearchIndexUpdater::TYPE,
            'body' => [
                'query' => [
                    'match' => [
                        $fieldUtils->getFieldNameById('title') => [
                            'query' => $title,
                            'operator' => 'and',
                        ]
                    ]
                ],
                '_source' => [
                    'title',
                    'slug'
                ],
                'size' => 10,
            ],
        ]);

        return array_map(function ($hit) {
            return $hit['_source'];
        }, $result['hits']['hits']);
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
            $aggregations[$fieldUtils->getFieldName($fieldEntity)] = [
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

    public function getStats()
    {
        return $this->client->search([
            'index' => SearchIndexUpdater::INDEX,
            'type' => SearchIndexUpdater::TYPE,
            'body' => [
                '_source' => false,
                'size' => 0,
                'aggs' => $this->fieldAggregations()
            ]
        ])['aggregations'];
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

    /**
     * @return array
     */
    private function fieldAggregations(): array
    {
        $fieldUtils = new FieldUtils();
        $fields = $this->em->getRepository(TagName::class)->findAll();
        $fields[] = $fieldUtils->getTitleField();
        $aggs = [];
        foreach ($fields as $field) {
            $fieldName = $fieldUtils->getFieldNameForAggregation($field);
            switch ($field->getType()) {
                case 'integer':
                    $aggs['max_' . $field->getId()] = [
                        'max' => [
                            'field' => $fieldName
                        ],
                    ];
                    $aggs['min_' . $field->getId()] = [
                        'min' => [
                            'field' => $fieldName
                        ],
                    ];
                    break;
                case 'boolean':
                    $aggs['vals_' . $field->getId()] = [
                        'terms' => [
                            'field' => $fieldName
                        ]
                    ];
                    break;
                case 'string':
                    $aggs['vals_' . $field->getId()] = [
                        'terms' => [
                            'field' => $fieldName,
                            'size' => 20
                        ]
                    ];
                    break;
            }
        }
        return $aggs;
    }

    /**
     * @param string $q
     * @param $matches
     * @return array
     */
    private function qMatches(string $q, $matches): array
    {
        $fields = $this->getFields();
        $fieldUtils = new FieldUtils();
        $fields = array_filter($fields, function (TagName $field) use ($fieldUtils) {
            return $fieldUtils->isPartOfQSearch($field->getType());
        });
        $fields = array_map(function (TagName $field) use ($fieldUtils) {
            return $fieldUtils->getFieldNameById($field->getId());
        }, $fields);
        $fields = array_values($fields);

        $terms = explode(',', $q);
        $qMatches = [];
        foreach ($terms as $term) {
            if (trim($term) == "") {
                continue;
            }
            $qMatches[] = [
                'multi_match' => [
                    'query' => $term,
                    'fields' => $fields,
                    'type' => 'phrase'
                ]
            ];
        }
        if (!empty($qMatches)) {
            $matches[] = [
                'bool' => [
                    'must' => $qMatches
                ]
            ];
        }
        return $matches;
    }

    /**
     * @param array $filters
     * @return array
     */
    private function filterMatches(array $filters, array $matches): array
    {
        $fieldUtils = new FieldUtils();
        $fields = $this->getFields();

        foreach ($filters as $id => $filter) {
            if ($id !== 'title' && !is_numeric($id)) {
                continue;
            }
            if ($filter['e'] != '1') {
                continue;
            }
            $values = isset($filter['v']) ? (array)$filter['v'] : [];
            if (count($values) == 0) {
                continue;
            }

            $booleanOperator = isset($filter['b']) ? $filter['b'] : 'AND';
            if (!in_array($booleanOperator, ['AND', 'OR'], true)) {
                $booleanOperator = 'AND';
            }

            $filterMatches = [];
            foreach ($values as $key => $value) {
                if ($value === '') {
                    continue;
                }
                $fieldName = $fieldUtils->getFieldNameById($id);
                $fieldEntity = $fields[$id];

                if (in_array($key, ['min', 'max'], true) && is_numeric($value)) {
                    $filterMatches[] = [
                        'range' => [
                            $fieldName => [
                                $key == 'min' ? 'gte' : 'lte' => $value,
                            ]
                        ]
                    ];
                #} else if (in_array($fieldEntity->getType(), ['string', 'text'])) {
                #    $filterMatches[] = ['match' => [$field => $value]];
                } else {
                    if ($fieldEntity->getType() == 'string') {
                        $fieldName .= '.keyword';
                    }
                    $filterMatches[] = ['term' => [$fieldName => $value]];
                }
            }

            if (count($filterMatches) > 0) {
                if ($booleanOperator == 'OR') {
                    $matches[] = [
                        'bool' => [
                            'should' => $filterMatches,
                            'minimum_should_match' => 1,
                        ]
                    ];
                } else {
                    $matches[] = [
                        'bool' => [
                            'must' => $filterMatches
                        ]
                    ];
                }
            }
        }
        return $matches;
    }

    /**
     * @return TagName[]
     */
    private function getFields(): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('f')
            ->from(TagName::class, 'f', 'f.id');
        /** @var TagName[] $fields */
        $fields = $qb->getQuery()->execute();
        $fields['title'] = (new FieldUtils())->getTitleField();

        return $fields;
    }
}