<?php


namespace MinhD\SolrClient\Commands;

use MinhD\SolrClient\SolrClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
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
                        'do',
                        'd',
                        InputOption::VALUE_REQUIRED,
                        'Do something',
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

        $command = $input->getOption('do');

        switch ($command) {
            case 'commit':
                $solr->commit();
                $this->handleResponse($solr, $output);
                break;
            case 'optimize':
                $solr->optimize();
                $this->handleResponse($solr, $output);
                break;
            case 'reload':
                $solr->collections()->reload();
                $this->handleResponse($solr, $output);
                break;
            case 'clear':
                $solr->removeByQuery('*:*');
                $solr->commit();
                $this->handleResponse($solr, $output);
                break;
            case 'create':
                $solr->collections()->create(
                    $collection,
                    [
                        'numShards' => 1,
                        'collection.configName' => 'gettingstarted'
                    ]
                );
                $this->handleResponse($solr, $output);
                break;
            case 'delete':
                $solr->collections()->delete($collection);
                $this->handleResponse($solr, $output);
                break;
            case 'list':
                $result = $solr->collections()->get();
                foreach ($result as $r) {
                    $output->writeln($r);
                }
                break;
            default:
                $output->writeln('Unknown -d flag: '. $command);
                break;
        }
    }

    private function handleResponse($solr, OutputInterface $output)
    {
        if ($solr->hasError()) {
            foreach ($solr->getErrors() as $error) {
                $output->writeln("<error>$error</error>");
            }
        }
        $output->writeln($solr->hasError() ? 'Finish With Error' : 'Finished!');
    }
}
