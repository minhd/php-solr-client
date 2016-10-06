<?php

namespace MinhD\SolrClient\Commands;

use MinhD\SolrClient\SolrClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SolrExportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('solr:export')
            ->setDescription('Export a SOLR collection')
            ->setHelp('This command allows you to export a SOLR collection...')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption(
                        'source-solr', 's',
                        InputOption::VALUE_REQUIRED,
                        'SOLR instance',
                        'http://localhost'
                    ),
                    new InputOption(
                        'source-solr-port', 'p',
                        InputOption::VALUE_REQUIRED,
                        'SOLR port',
                        '8983'
                    ),
                    new InputOption(
                        'source-solr-collection', 'c',
                        InputOption::VALUE_REQUIRED,
                        'SOLR collection',
                        'gettingstarted'
                    ),
                    new InputOption(
                        'target-dir', 't',
                        InputOption::VALUE_REQUIRED,
                        'Target directory to export to',
                        '/tmp/'
                    )
                ))
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getOption('source-solr');
        $port = $input->getOption('source-solr-port');
        $collection = $input->getOption('source-solr-collection');
        $targetDir = $input->getOption('target-dir');

        $solr = new SolrClient($source, $port, $collection);



    }
}