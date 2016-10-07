<?php


namespace MinhD\SolrClient\Commands;

use MinhD\SolrClient\SolrClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SolrRunCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('solr:run')
            ->setDescription('Run A Command on SOLR')
            ->setHelp('This command allows you to run a custom command on SOLR')
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'solr',
                        's',
                        InputOption::VALUE_REQUIRED,
                        'SOLR instance',
                        'http://localhost'
                    ),
                    new InputOption(
                        'solr-port',
                        'p',
                        InputOption::VALUE_REQUIRED,
                        'SOLR port',
                        '8983'
                    ),
                    new InputOption(
                        'solr-collection',
                        'c',
                        InputOption::VALUE_REQUIRED,
                        'SOLR collection',
                        'gettingstarted'
                    ),
                    new InputOption(
                        'command',
                        null,
                        InputOption::VALUE_REQUIRED,
                        'Command to run',
                        'commit'
                    )
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getOption('solr');
        $port = $input->getOption('solr-port');
        $collection = $input->getOption('solr-collection');

        $solr = new SolrClient($source, $port, $collection);

        $command = $input->getOption('command');

        switch ($command) {
            case 'commit':
                print_r($solr->commit());
                break;
            case 'optimize':
                print_r($solr->optimize());
                break;
            case 'clear':
                print_r($solr->removeByQuery('*:*'));
                print_r($solr->commit());
                break;
            case 'create':
                print_r($solr->collections()->create(
                    $collection,
                    [
                        'numShards' => 1,
                        'collection.configName' => 'gettingstarted'
                    ]
                ));
                break;
            case 'delete':
                print_r($solr->collections()->delete($collection));
                break;
            case 'list':
                print_r($solr->collections()->get());
                break;
            default:
                break;
        }

        $output->writeln('Done');
    }
}
