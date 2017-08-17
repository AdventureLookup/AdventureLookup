<?php

namespace AppBundle\Service;


use AppBundle\Entity\AdventureDocument;
use AppBundle\Exception\FieldDoesNotExistException;
use AppBundle\Field\Field;
use AppBundle\Field\FieldProvider;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdventureSearch
{
    const ADVENTURES_PER_PAGE = 50;

    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    /**
     * @var FieldProvider
     */
    private $fieldProvider;

    /**
     * @var string
     */
    private $indexName;

    /**
     * @var string
     */
    private $typeName;

    public function __construct(FieldProvider $fieldProvider, ElasticSearch $elasticSearch)
    {
        $this->fieldProvider = $fieldProvider;
        $this->client = $elasticSearch->getClient();
        $this->indexName = $elasticSearch->getIndexName();
        $this->typeName = $elasticSearch->getTypeName();
    }

    /**
     * @param string $q
     * @param array $filters
     * @param int $page
     * @return array
     */
    public function search(string $q, array $filters, int $page)
    {
        if ($page < 1 ||  $page * self::ADVENTURES_PER_PAGE > 5000) {
            throw new BadRequestHttpException();
        }

        $matches = [];

        // First generate ES search query from free-text searchbar at the top.
        // This will only search string and text fields.
        $matches = $this->qMatches($q, $matches);

        // Now apply filters from the left-hand filterbar.
        $matches = $this->filterMatches($filters, $matches);

        // If we neither have a filter, nor any kind of free-text search, return all adventures.
        if (empty($matches)) {
            $matches = ['match_all' => new \stdClass()];
        }

        $result = $this->client->search([
            'index' => $this->indexName,
            'type' => $this->typeName,
            'body' => [
                'query' => [
                    // All queries must evaluate to true for a result to be returned.
                    'bool' => [
                        "must" => $matches
                    ]
                ],
                'from' => self::ADVENTURES_PER_PAGE * ($page - 1),
                'size' => self::ADVENTURES_PER_PAGE,
                // Also return aggregations for all fields, i.e. min/max for integer fields
                // or the most common strings for string fields.
                'aggs' => $this->fieldAggregations(),
            ],
        ]);

        $hits = $result['hits']['hits'];
        $adventureDocuments = $this->searchResultsToAdventureDocuments($hits);

        return [$adventureDocuments, $result['hits']['total'], $result['aggregations']];
    }

    public function similarTitles($title): array
    {
        if ($title === '') {
            return [];
        }

        $result = $this->client->search([
            'index' => $this->indexName,
            'type' => $this->typeName,
            'body' => [
                'query' => [
                    'match' => [
                        'title' => [
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

    /**
     * Given a field and an input query, return a list of values
     * which could possibly be what the user wants to insert.
     * If the query is empty, return the most common values.
     *
     * @param Field $field
     * @param string $q
     * @return array
     */
    public function autocompleteFieldContent(Field $field, string $q): array
    {
        $size = 20;
        if ($q === '') {
            return current($this->aggregateMostCommonValues([$field], $size));
        }

        $fieldName = $field->getName();
        $response = $this->client->search([
            'index' => $this->indexName,
            'type' => $this->typeName,
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
            $highlights = array_unique($hit['highlight'][$fieldName]);
            foreach ($highlights as $highlight) {
                if (!in_array($highlight, $results)) {
                    $results[] = $highlight;
                }
            }
        }

        return $results;
    }

    /**
     * @param Field[] $fields
     * @param int $size
     * @return array
     */
    private function aggregateMostCommonValues(array $fields, int $size): array
    {
        $aggregations = [];
        foreach ($fields as $field) {
            $elasticField = $field->getFieldNameForAggregation();
            if (!$elasticField) {
                // This field cannot be aggregated.
                continue;
            }
            $aggregations[$elasticField] = [
                'terms' => [
                    'field' => $elasticField,
                    'size' => $size
                ]
            ];
        }

        $response = $this->client->search([
            'index' => $this->indexName,
            'type' => $this->typeName,
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
     * @param array $hits
     * @return AdventureDocument[]
     */
    private function searchResultsToAdventureDocuments(array $hits): array
    {
        return array_map(function ($hit) {
            return new AdventureDocument(
                $hit['_id'],
                $hit['_source']['authors'],
                $hit['_source']['edition'],
                $hit['_source']['environments'],
                $hit['_source']['items'],
                $hit['_source']['publisher'],
                $hit['_source']['setting'],
                $hit['_source']['commonMonsters'],
                $hit['_source']['bossMonsters'],
                $hit['_source']['title'],
                $hit['_source']['description'],
                $hit['_source']['slug'],
                $hit['_source']['minStartingLevel'],
                $hit['_source']['maxStartingLevel'],
                $hit['_source']['startingLevelRange'],
                $hit['_source']['numPages'],
                $hit['_source']['foundIn'],
                $hit['_source']['partOf'],
                $hit['_source']['link'],
                $hit['_source']['thumbnailUrl'],
                $hit['_source']['soloable'],
                $hit['_source']['pregeneratedCharacters'],
                $hit['_source']['tacticalMaps'],
                $hit['_source']['handouts'],
                $hit['_score']
            );
        }, $hits);
    }

    /**
     * @return array
     */
    private function fieldAggregations(): array
    {
        $aggregations = [];
        $fields = $this->fieldProvider->getFields();
        foreach ($fields as $field) {
            $fieldName = $field->getFieldNameForAggregation();
            switch ($field->getType()) {
                case 'integer':
                    $aggregations['max_' . $field->getName()] = [
                        'max' => [
                            'field' => $fieldName
                        ],
                    ];
                    $aggregations['min_' . $field->getName()] = [
                        'min' => [
                            'field' => $fieldName
                        ],
                    ];
                    break;
                case 'boolean':
                    $aggregations['vals_' . $field->getName()] = [
                        'terms' => [
                            'field' => $fieldName
                        ]
                    ];
                    break;
                case 'string':
                    $aggregations['vals_' . $field->getName()] = [
                        'terms' => [
                            'field' => $fieldName,
                            // Return up to 1000 different values.
                            'size' => 1000
                        ]
                    ];
                    break;
                // Other field types are not supported
            }
        }
        return $aggregations;
    }

    /**
     * Find adventures matching the free-text search query
     *
     * @param string $q
     * @param $matches
     * @return array
     */
    private function qMatches(string $q, $matches): array
    {
        $fields = $this->fieldProvider
            ->getFields()
            ->filter(function (Field $field) { return $field->isFreetextSearchable(); })
            ->map(function (Field $field) { return $field->getName() . '^' . $field->getSearchBoost(); })
            ->getValues();

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
                    'fuzziness' => 'AUTO'
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
     * @param array $matches
     * @return array
     */
    private function filterMatches(array $filters, array $matches): array
    {
        // Iterate all user-provided filters
        foreach ($filters as $fieldName => $filter) {
            try {
                $field = $this->fieldProvider->getField($fieldName);
            } catch (FieldDoesNotExistException $e) {
                // The field does not exist. This normally never happens. Skip silently.
                continue;
            }

            $values = isset($filter['v']) ? (array)$filter['v'] : [];
            if (count($values) == 0) {
                // Apparently no filter value provided
                continue;
            }

            $filterMatches = [];
            foreach ($values as $key => $value) {
                if ($value === '') {
                    continue;
                }

                if ($field->getType() === 'integer' && in_array($key, ['min', 'max'], true) && is_numeric($value)) {
                    $filterMatches[] = [
                        'range' => [
                            $field->getName() => [
                                $key == 'min' ? 'gte' : 'lte' => $value,
                            ]
                        ]
                    ];
                } else {
                    $fieldNameForSearch = $fieldName;
                    if ($field->getType() == 'string') {
                        $fieldNameForSearch .= '.keyword';
                    }
                    $filterMatches[] = ['term' => [$fieldNameForSearch => $value]];
                }
            }

            if (count($filterMatches) > 0) {
                if ($field->getType() === 'integer') {
                    // Integer fields must use AND, because you want e.g. the page count to be between min AND max.
                    $matches[] = [
                        'bool' => [
                            'must' => $filterMatches
                        ]
                    ];
                } else {
                    $matches[] = [
                        'bool' => [
                            'should' => $filterMatches,
                            'minimum_should_match' => 1,
                        ]
                    ];
                }
            }
        }
        return $matches;
    }
}
