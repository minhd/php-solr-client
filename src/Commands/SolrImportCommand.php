<?php


namespace MinhD\SolrClient\Commands;


use MinhD\SolrClient\SolrClient;
use MinhD\SolrClient\SolrDocument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Stopwatch\Stopwatch;

class SolrImportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('solr:import')
            ->setDescription('Import a directory to SOLR')
            ->setHelp('This command allows you to import a directory of JSON to SOLR')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption(
                        'solr', 's',
                        InputOption::VALUE_REQUIRED,
                        'SOLR instance',
                        'http://localhost'
                    ),
                    new InputOption(
                        'solr-port', 'p',
                        InputOption::VALUE_REQUIRED,
                        'SOLR port',
                        '8983'
                    ),
                    new InputOption(
                        'solr-collection', 'c',
                        InputOption::VALUE_REQUIRED,
                        'SOLR collection',
                        'gettingstarted'
                    ),
                    new InputOption(
                        'chunk-size', null,
                        InputOption::VALUE_REQUIRED,
                        'Chunk Size',
                        100
                    ),
                    new InputOption(
                        'source-dir', 't',
                        InputOption::VALUE_REQUIRED,
                        'Source directory to import from',
                        '/tmp/'
                    )
                ))
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getOption('solr');

        // TODO: do something nifty about source containing the port

        $port = $input->getOption('solr-port');
        $collection = $input->getOption('solr-collection');
        $sourceDir = $input->getOption('source-dir');
        $rows = $input->getOption('chunk-size');

        $solr = new SolrClient($source, $port, $collection);

        ini_set('memory_limit', '256M');

        // find out how big this is
        $finder = new Finder();
        $finder->files()->in($sourceDir);
        $output->writeln("There are " . count($finder) . " files to export.");
        $progressBar = new ProgressBar($output, count($finder));
        $stopwatch = new Stopwatch();
        $stopwatch->start('import');
        foreach ($finder as $file) {
            // $output->writeln("Processing ".$file->getRealPath());
            $contents = json_decode($file->getContents(), true);
            foreach ($contents as $doc) {
                $document = new SolrDocument($doc);
                $solr->add($document);
            }
            $progressBar->advance(1);
            $solr->commit();
        }
        $solr->optimize();
        $progressBar->finish();
        $event = $stopwatch->end('import');
        $output->writeln('');
        $output->writeln(
            'Finished. Took (' . round($event->getDuration() / 1000, 2) . ')s'
        );
        $output->writeln(
            "Max Memory Usage: " . round($event->getMemory() / 1000, 2) . ' KB'
        );

    }
}