<?php

namespace AppBundle\Command;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\TagName;
use AppBundle\Listener\SearchIndexUpdater;
use AppBundle\Service\FieldUtils;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AppElasticsearchReindexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:elasticsearch:reindex')
            ->setDescription('Reindex the whole elasticsearch index')
        ;
    }

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
            'title' => [
                'type' => 'text',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword',
                        'ignore_above' => 256,
                    ]
                ]
            ],
            'slug' => [
                'enabled' => false
            ]
        ];

        $fieldUtils = new FieldUtils();

        /** @var TagName[] $tagNames */
        $tagNames = $em->getRepository(TagName::class)->findAll();
        foreach ($tagNames as $tagName) {
            $fieldName = 'info_' . $tagName->getId();
            $mappings[$fieldName] = $fieldUtils->generateMappingFor($tagName->getType());
        }

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
            $searchIndexUpdater->update($adventure);
            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
        $output->writeln('Reindexed documents.');
    }
}
