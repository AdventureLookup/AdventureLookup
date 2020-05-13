<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adventure;
use AppBundle\Listener\SearchIndexUpdater;
use AppBundle\Service\ElasticSearch;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppElasticsearchReindexCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ElasticSearch
     */
    private $elasticSearch;

    /**
     * @var SearchIndexUpdater
     */
    private $searchIndexUpdater;

    public function __construct(EntityManagerInterface $em, ElasticSearch $elasticSearch, SearchIndexUpdater $searchIndexUpdater)
    {
        $this->em = $em;
        $this->elasticSearch = $elasticSearch;
        $this->searchIndexUpdater = $searchIndexUpdater;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:elasticsearch:reindex')
            ->setDescription('Reindex the whole Elasticsearch index')
        ;
    }

    // TODO: Add when using PHP 7.1
    /* private */ const FIELD_NON_SEARCHABLE = [
        'enabled' => false
    ];
    const FIELD_STRING = [
        'type' => 'text',
        'fields' => [
            'keyword' => [
                'type' => 'keyword',
                'ignore_above' => 256,
            ]
        ]
    ];
    const FIELD_TEXT = [
        'type' => 'text',
    ];
    const FIELD_INTEGER = [
        'type' => 'integer',
    ];
    const FIELD_BOOLEAN = [
        'type' => 'boolean',
    ];
    const FIELD_DATE = [
        'type' => 'date'
    ];

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->elasticSearch->getClient();
        $indexName = $this->elasticSearch->getIndexName();
        $typeName = $this->elasticSearch->getTypeName();

        try {
            $client->indices()->delete([
                'index' => $indexName,
            ]);
            $output->writeln('Deleted index.');
        } catch (Missing404Exception $e) {

        }

        $client->indices()->create([
            'index' => $indexName,
        ]);
        $output->writeln('Recreated index.');

        $mappings = [
            'authors' => self::FIELD_STRING,
            'edition' => self::FIELD_STRING,
            'environments' => self::FIELD_STRING,
            'items' => self::FIELD_STRING,
            'publisher' => self::FIELD_STRING,
            'setting' => self::FIELD_STRING,
            'commonMonsters' => self::FIELD_STRING,
            'bossMonsters' => self::FIELD_STRING,

            'title' => self::FIELD_STRING,
            'description' => self::FIELD_TEXT,
            'slug' => self::FIELD_NON_SEARCHABLE,
            'minStartingLevel' => self::FIELD_INTEGER,
            'maxStartingLevel' => self::FIELD_INTEGER,
            'startingLevelRange' => self::FIELD_STRING,
            'numPages' => self::FIELD_INTEGER,
            'foundIn' => self::FIELD_STRING,
            'partOf' => self::FIELD_STRING,
            'link' => self::FIELD_NON_SEARCHABLE,
            'thumbnailUrl' => self::FIELD_NON_SEARCHABLE,
            'soloable' => self::FIELD_BOOLEAN,
            'pregeneratedCharacters' => self::FIELD_BOOLEAN,
            'tacticalMaps' => self::FIELD_BOOLEAN,
            'handouts' => self::FIELD_BOOLEAN,

            'createdAt' => self::FIELD_DATE,
            'numPositiveReviews' => self::FIELD_INTEGER,
            'numNegativeReviews' => self::FIELD_INTEGER,
        ];

        $client->indices()->putMapping([
            'index' => $indexName,
            'type' => $typeName,
            'body' => [
                SearchIndexUpdater::TYPE => [
                    'properties' => $mappings
                ]
            ]
        ]);
        $output->writeln('Created mappings');
        $output->writeln('Reindexing documents');

        $adventures = $this->em->getRepository(Adventure::class)->findAll();
        $this->searchIndexUpdater->updateSearchIndexForAdventures($adventures);

        $output->writeln('Reindexed documents.');
    }
}
