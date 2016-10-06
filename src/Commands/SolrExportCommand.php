<?php

namespace MinhD\SolrClient\Commands;

use MinhD\SolrClient\SolrClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Stopwatch\Stopwatch;

class SolrExportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('solr:export')
            ->setDescription('Export a SOLR collection')
            ->setHelp('This command allows you to export a SOLR collection...')
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
                        'chunk-size',
                        null,
                        InputOption::VALUE_REQUIRED,
                        'Chunk Size',
                        100
                    ),
                    new InputOption(
                        'target-dir',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Target directory to export to',
                        '/tmp/'
                    )
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getOption('solr');

        // TODO: do something nifty about source containing the port

        $port = $input->getOption('solr-port');
        $collection = $input->getOption('solr-collection');
        $targetDir = $input->getOption('target-dir');
        $rows = $input->getOption('chunk-size');

        $solr = new SolrClient($source, $port, $collection);

        // find out how big this is
        $payload = $solr->search([
            'q' => '*',
            'rows' => '0'
        ]);
        $numFound = $payload->getNumFound();

        $output->writeln('There are ' . $numFound . ' records to export.');

        ini_set('memory_limit', '256M');
        $fs = new Filesystem();
        $stopwatch = new Stopwatch();

        $continue = true;
        $start = '*';
        $i = 1;

        $progressBar = new ProgressBar($output, $numFound);
        $stopwatch->start('download');
        while ($continue) {
            $payload = $solr->cursor($start, $rows);
            $documents = $payload->getDocs('json');
            $fs->dumpFile($targetDir . '/' . $i . '.json', $documents);

            if (sizeof($payload->getDocs()) == 0) {
                $continue = false;
            }

            $i++;
            $start = $payload->getNextCursorMark();
            $progressBar->advance(sizeof($payload->getDocs()));
        }
        $progressBar->finish();
        $event = $stopwatch->stop('download');

        $output->writeln('');
        $output->writeln(
            'Finished. Took (' . round($event->getDuration() / 1000, 2) . ')s'
        );
        $output->writeln(
            'Max Memory Usage: ' . round($event->getMemory() / 1000, 2) . ' KB'
        );
    }
}
