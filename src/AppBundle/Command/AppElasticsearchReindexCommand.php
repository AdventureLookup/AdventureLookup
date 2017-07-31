<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adventure;
use AppBundle\Listener\SearchIndexUpdater;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppElasticsearchReindexCommand extends ContainerAwareCommand
{
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = ClientBuilder::create()->build();

        try {
            $client->indices()->delete([
                'index' => SearchIndexUpdater::INDEX,
            ]);
            $output->writeln('Deleted index.');
        } catch (Missing404Exception $e) {

        }

        $client->indices()->create([
            'index' => SearchIndexUpdater::INDEX,
        ]);
        $output->writeln('Recreated index.');

        $mappings = [
            'authors' => self::FIELD_STRING,
            'edition' => self::FIELD_STRING,
            'environments' => self::FIELD_STRING,
            'items' => self::FIELD_STRING,
            'npcs' => self::FIELD_STRING,
            'publisher' => self::FIELD_STRING,
            'setting' => self::FIELD_STRING,
            'monsters' => self::FIELD_STRING,

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
        ];

        $client->indices()->putMapping([
            'index' => SearchIndexUpdater::INDEX,
            'type' => SearchIndexUpdater::TYPE,
            'body' => [
                SearchIndexUpdater::TYPE => [
                    'properties' => $mappings
                ]
            ]
        ]);
        $output->writeln('Created mappings');
        $output->writeln('Reindexing documents');

        $searchIndexUpdater = $this->getContainer()->get('search_index_updater');

        $adventures = $em->getRepository(Adventure::class)->findAll();
        $progress = new ProgressBar($output, count($adventures));
        $progress->start();

        foreach($adventures as $adventure) {
            $searchIndexUpdater->updateSearchIndexForAdventure($adventure);
            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
        $output->writeln('Reindexed documents.');
    }
}
