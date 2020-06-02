<?php

namespace AppBundle\Service;

use AppBundle\Entity\AdventureDocument;
use AppBundle\Exception\FieldDoesNotExistException;
use AppBundle\Field\Field;
use AppBundle\Field\FieldProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdventureSearch
{
    const ADVENTURES_PER_PAGE = 20;

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
     * @var TimeProvider
     */
    private $timeProvider;

    public function __construct(FieldProvider $fieldProvider, ElasticSearch $elasticSearch, TimeProvider $timeProvider)
    {
        $this->fieldProvider = $fieldProvider;
        $this->client = $elasticSearch->getClient();
        $this->indexName = $elasticSearch->getIndexName();
        $this->timeProvider = $timeProvider;
    }

    /**
     * @return array
     */
    public function requestToSearchParams(Request $request)
    {
        $q = $request->get('q', '');
        $sortBy = $request->get('sortBy', '');
        // Use a timestamp with millisecond precision as the seed when none is provided.
        // We deliberately do not use time() for two reasons
        // 1. We use Date.now() in JS, which also returns a timestamp in milliseconds
        // 2. Simply using time() to get a timestamp in seconds sometimes leads to the same seed
        //    when you refresh the browser too quickly.
        $seed = (string) $request->get('seed', $this->timeProvider->millis());
        $page = (int) $request->get('page', 1);
        $filters = $request->get('f', []);
        if (!is_array($filters)) {
            $filters = [];
        }

        return [$q, $filters, $page, $sortBy, $seed];
    }

    /**
     * @param string $seed random seed used when adventures have to be sorted randomly
     *
     * @return array
     */
    public function search(string $q, array $filters, int $page, string $sortBy, string $seed)
    {
        if ($page < 1 || $page * self::ADVENTURES_PER_PAGE > 5000) {
            throw new BadRequestHttpException();
        }

        $matches = [];

        // First generate ES search query from free-text searchbar at the top.
        // This will only search string and text fields.
        $matches = $this->qMatches($q, $matches);

        // Now apply filters from the sidebar.
        $matches = $this->filterMatches($filters, $matches);

        // If we neither have a filter, nor any kind of free-text search, return all adventures.
        if (empty($matches)) {
            $matches = ['match_all' => new \stdClass()];
        }

        $query = [
            // All matches must evaluate to true for a result to be returned.
            'bool' => [
                'must' => $matches,
            ],
        ];

        switch ($sortBy) {
            case 'title':
                $sort = 'title.keyword';
            break;
            case 'numPages-desc':
                $sort = ['numPages' => 'desc'];
            break;
            case 'numPages-asc':
                $sort = ['numPages' => 'asc'];
            break;
            case 'createdAt-asc':
                $sort = ['createdAt' => 'asc'];
            break;
            case 'createdAt-desc':
                $sort = ['createdAt' => 'desc'];
            break;
            case 'reviews':
                // We use the Wilson Score instead of the average of positive and negative reviews
                // https://www.elastic.co/de/blog/better-than-average-sort-by-best-rating-with-elasticsearch
                $sort = [
                    '_script' => [
                        'order' => 'desc',
                        'type' => 'number',
                        'script' => [
                            'inline' => "
                                long p = doc['positiveReviews'].value;
                                long n = doc['negativeReviews'].value;
                                return p + n > 0 ? ((p + 1.9208) / (p + n) - 1.96 * Math.sqrt((p * n) / (p + n) + 0.9604) / (p + n)) / (1 + 3.8416 / (p + n)) : 0;
                            ",
                        ],
                    ],
                ];
            break;
            default:
                $sort = ['_score'];
            break;
        }

        if ('random' === $sortBy) {
            // Sorting in a random order cannot be done using the 'sort' parameter, but requires adjusting the query
            // to use the random_score function for scoring.
            // https://www.elastic.co/guide/en/elasticsearch/reference/5.5/query-dsl-function-score-query.html
            $query = [
                'function_score' => [
                    'query' => $query,
                    'random_score' => [
                        'seed' => $seed,
                    ],
                ],
            ];
        }

        $result = $this->client->search([
            'index' => $this->indexName,
            'body' => [
                'query' => $query,
                'from' => self::ADVENTURES_PER_PAGE * ($page - 1),
                'size' => self::ADVENTURES_PER_PAGE,
                // Also return aggregations for all fields, i.e. min/max for integer fields
                // or the most common strings for string fields.
                'aggs' => $this->fieldAggregations(),
                'sort' => $sort,
            ],
        ]);

        $adventureDocuments = array_map(function ($hit) {
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
                $hit['_source']['year'],
                $hit['_source']['positiveReviews'],
                $hit['_source']['negativeReviews'],
                $hit['_score']
            );
        }, $result['hits']['hits']);
        $totalResults = $result['hits']['total']['value'];
        $hasMoreResults = $totalResults > $page * self::ADVENTURES_PER_PAGE;

        return [$adventureDocuments, $totalResults, $hasMoreResults, $result['aggregations']];
    }

    public function similarTitles($title): array
    {
        if ('' === $title) {
            return [];
        }

        $result = $this->client->search([
            'index' => $this->indexName,
            'body' => [
                'query' => [
                    'match' => [
                        'title' => [
                            'query' => $title,
                            'operator' => 'and',
                            'fuzziness' => 'AUTO',
                        ],
                    ],
                ],
                '_source' => [
                    'title',
                    'slug',
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
     */
    public function autocompleteFieldContent(Field $field, string $q): array
    {
        $size = 20;
        if ('' === $q) {
            return current($this->aggregateMostCommonValues([$field], $size));
        }

        $fieldName = $field->getName();
        $response = $this->client->search([
            'index' => $this->indexName,
            'body' => [
                'query' => [
                    'match_phrase_prefix' => [
                        $fieldName => $q,
                    ],
                ],
                'size' => $size,
                '_source' => false,
                'highlight' => [
                    'pre_tags' => [''],
                    'post_tags' => [''],
                    'fields' => [
                        $fieldName => new \stdClass(),
                    ],
                ],
            ],
        ]);

        $results = [];
        foreach ($response['hits']['hits'] as $hit) {
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
                    'size' => $size,
                ],
            ];
        }

        $response = $this->client->search([
            'index' => $this->indexName,
            'body' => [
                'size' => 0,
                'aggregations' => $aggregations,
            ],
            'request_cache' => true,
        ]);

        $results = [];
        foreach ($response['aggregations'] as $field => $aggregation) {
            $results[$field] = array_column($aggregation['buckets'], 'key');
        }

        return $results;
    }

    private function fieldAggregations(): array
    {
        $aggregations = [];
        $fields = $this->fieldProvider->getFields();
        foreach ($fields as $field) {
            $fieldName = $field->getFieldNameForAggregation();
            switch ($field->getType()) {
                case 'integer':
                    $aggregations['max_'.$field->getName()] = [
                        'max' => [
                            'field' => $fieldName,
                        ],
                    ];
                    $aggregations['min_'.$field->getName()] = [
                        'min' => [
                            'field' => $fieldName,
                        ],
                    ];
                    break;
                case 'boolean':
                    $aggregations['vals_'.$field->getName()] = [
                        'terms' => [
                            'field' => $fieldName,
                        ],
                    ];
                    break;
                case 'string':
                    $aggregations['vals_'.$field->getName()] = [
                        'terms' => [
                            'field' => $fieldName,
                            // Return up to 1000 different values.
                            'size' => 1000,
                        ],
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
     * @param $matches
     */
    private function qMatches(string $q, $matches): array
    {
        // Get a list of freetext searchable fields and their individual boost values.
        $fields = $this->fieldProvider
            ->getFields()
            ->filter(function (Field $field) {
                return $field->isFreetextSearchable();
            })
            ->map(function (Field $field) {
                return $field->getName().'^'.$field->getSearchBoost();
            })
            ->getValues();

        // Implicitly, everything the user types in the search bar is ANDed together.
        // A search for 'galactic ghouls' should result in adventures that contain
        // both terms. If the user really wants to search for 'galactic OR ghouls',
        // the have to separate the terms by ' OR '.
        // The order of terms is irrelevant: Searching for 'galactic ghouls' leads
        // to the same results as searching for 'ghouls galactic'. We could look
        // into supporting quoting terms ('"galactic ghouls"') later, which would
        // NOT match adventures with 'ghouls galactic' or adventures with 'galactic'
        // and 'ghouls' in different fields.
        $clauses = explode(' OR ', $q);
        $orMatches = [];
        foreach ($clauses as $clause) {
            $terms = explode(' ', $clause);
            // All terms that are part of this clause have to be ANDed together.
            // Given the search query 'galactic ghouls', we don't care if both
            // 'galactic' and 'ghouls' appear in the same field (e.g., the title)
            // or appear on their own in different fields (e.g., 'galactic' in
            // the title and 'ghouls' in the description). That is why we can't
            // simply use a single 'multi_match' query with the operator set to
            // 'and' like this:
            // ['multi_match' => [
            //     'query' => 'galactic ghouls',
            //     'fields' => $fields,
            //     'type' => 'most_fields'
            //     'fuzziness' => 'AUTO',
            //     'prefix_length' => 2,
            //      'operator' => 'and'
            // ]]
            // This query would only return results where both terms appear in
            // the same field. We also can't use 'cross_fields' (instead of
            // 'most_fields'): While that allows terms to be distributed across
            // fields, it doesn't allow using fuzziness.
            //
            // That is why we create a multi_match query per term and AND them
            // together using a 'bool => 'must' query.
            $termMatches = [];
            foreach ($terms as $term) {
                if ('' == trim($term)) {
                    continue;
                }
                $termMatches[] = [
                    'multi_match' => [
                        'query' => $term,
                        'fields' => $fields,
                        // 'most_fields' combines the scores of all fields that
                        // contain the search term: If the term appears in title,
                        // description, and edition, the score of all of these
                        // occurrences is combined. This is better than using
                        // the default 'best_fields', which simply takes field
                        // with the highest score, discarding all lower scores.
                        'type' => 'most_fields',
                        // Fuzziness is helpful for typos and finding plural
                        // versions of the same word. We do not currently stem
                        // the description and title, which is why using some
                        // fuzziness is essential.
                        // Setting prefix_length to 2 causes fuzziness to not
                        // change the first 2 characters of search terms. As
                        // an example, take the search for 'ghouls':
                        // 'ghouls' only has an edit distanc of 2 to the term
                        // 'should'. We don't want searches for 'ghouls' to
                        // also match 'should', which is why we restrict the
                        // fuzziness to start after the second character.
                        'fuzziness' => 'AUTO',
                        'prefix_length' => 2,
                    ],
                ];
            }
            if (!empty($termMatches)) {
                $orMatches[] = [
                    'bool' => [
                        'must' => $termMatches,
                    ],
                ];
            }
        }

        if (!empty($orMatches)) {
            // Combine the collected OR conditions.
            // At least one of them must match for an adventure to be returned.
            // The adventure will get a higher score if more than one matches.
            $matches[] = [
                'bool' => [
                    'should' => $orMatches,
                    'minimum_should_match' => 1,
                ],
            ];
        }

        return $matches;
    }

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

            $values = isset($filter['v']) ? (array) $filter['v'] : [];
            if (0 == count($values)) {
                // Apparently no filter value provided
                continue;
            }

            $filterMatches = [];
            foreach ($values as $key => $value) {
                if ('' === $value) {
                    continue;
                }

                if ('integer' === $field->getType() && in_array($key, ['min', 'max'], true) && is_numeric($value)) {
                    $filterMatches[] = [
                        'range' => [
                            $field->getName() => [
                                'min' == $key ? 'gte' : 'lte' => $value,
                            ],
                        ],
                    ];
                } elseif ('string' === $field->getType()) {
                    $filterMatches[] = ['term' => [$fieldName.'.keyword' => $value]];
                } elseif ('boolean' === $field->getType()) {
                    $filterMatches[] = ['term' => [$fieldName => '1' === $value]];
                }
            }

            if (count($filterMatches) > 0) {
                if ('integer' === $field->getType()) {
                    // Integer fields must use AND, because you want e.g. the page count to be between min AND max.
                    $matches[] = [
                        'bool' => [
                            'must' => $filterMatches,
                        ],
                    ];
                } else {
                    $matches[] = [
                        'bool' => [
                            'should' => $filterMatches,
                            'minimum_should_match' => 1,
                        ],
                    ];
                }
            }
        }

        return $matches;
    }
}
