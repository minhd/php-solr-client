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
    private $options = [];

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
                        'export/'
                    ),
                    new InputOption(
                        'schema-only',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Export the Schema Only',
                        false
                    )
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->options = $input->getOptions();

        if ($this->options['schema-only'] !== false) {
            $this->exportSchema($output);

            return true;
        }

        $this->exportRecords($output);
    }

    private function exportSchema(OutputInterface $output)
    {
        $solr = new SolrClient(
            $this->options['solr'],
            $this->options['solr-port'],
            $this->options['solr-collection']
        );
        $fs = new Filesystem();
        $schema = $solr->schema()->get();
        $fileName =  $this->options['target-dir'].'schema.json';
        $fs->dumpFile($fileName, json_encode($schema, true));
        $output->writeln('Schema written to '.$this->options['target-dir'].'schema.json');
    }

    private function exportRecords(OutputInterface $output)
    {
        $solr = new SolrClient(
            $this->options['solr'],
            $this->options['solr-port'],
            $this->options['solr-collection']
        );

        $searchParams = [
            'q' => '*',
            'rows' => $this->options['chunk-size']
        ];

        // find out how big this is
        $payload = $solr->search($searchParams);
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
            $payload = $solr->cursor($start, $this->options['chunk-size'], $searchParams);
            $documents = $payload->getDocs();

            if (count($documents) > 0) {
                $fs->dumpFile($this->options['target-dir'] . '/' . $i . '.json', json_encode($payload->getDocs('json'), true));
            }

            if ($start == $payload->getNextCursorMark()) {
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
