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
    private $options = [];

    protected function configure()
    {
        $this
            ->setName('solr:import')
            ->setDescription('Import a directory to SOLR')
            ->setHelp('This command allows you to import a directory of JSON to SOLR')
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
                        'source-dir',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Source directory to import from',
                        'export/'
                    ),
                    new InputOption(
                        'schema-only',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Import Only Schema',
                        false
                    ),
                    new InputOption(
                        'schema-location',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Schema location for --schema-only=true',
                        'export/schema/schema.json'
                    )
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->options = $input->getOptions();

        if ($this->options['schema-only'] !== false) {
            $this->importSchema($output);

            return true;
        }

        $this->importRecords($output);
    }

    private function importSchema($output)
    {
        $solr = new SolrClient($this->options['solr'], $this->options['solr-port'], $this->options['solr-collection']);
        $fileLocation = $this->options['schema-location'];

        if (!file_exists($fileLocation)) {
            $output->writeln('No file found at '.$fileLocation);

            return false;
        }

        $content = json_decode(file_get_contents($fileLocation), true);

        // add field type
        $result = $solr->schema()->setFieldTypes($content['schema']['fieldTypes']);
        if ($result['responseHeader']['status'] == 0) {
            $output->writeln('FieldTypes added');
        }

        // add fields
        $result = $solr->schema()->setFields($content['schema']['fields']);
        if ($result['responseHeader']['status'] == 0) {
            $output->writeln('Fields added');
        }

        // add copyFields
        $result = $solr->schema()->setCopyFields($content['schema']['copyFields']);
        if ($result['responseHeader']['status'] == 0) {
            $output->writeln('CopyFields added');
        }

        // add dynamicFields
        $result = $solr->schema()->setDynamicFields($content['schema']['dynamicFields']);
        if ($result['responseHeader']['status'] == 0) {
            $output->writeln('Dynamic Fields added');
        }

        $result = $solr->collections()->reload($this->options['solr-collection']);
        if ($result['responseHeader']['status'] == 0) {
            $output->writeln('Collection '.$this->options['solr-collection'].' reloaded');
        }

        $output->writeln('Imported schema '.$fileLocation.' to '.$solr->getBaseUrl().$solr->getCore());
    }

    private function importRecords(OutputInterface $output)
    {
        $solr = new SolrClient($this->options['solr'], $this->options['solr-port'], $this->options['solr-collection']);

        ini_set('memory_limit', '256M');

        // find out how big this is
        $finder = new Finder();
        $finder->files()->in($this->options['source-dir'])->name('*.json');

        $output->writeln('There are ' . count($finder) . ' files to import.');
        $progressBar = new ProgressBar($output, count($finder));
        $stopwatch = new Stopwatch();
        $stopwatch->start('import');

        foreach ($finder as $file) {
            // $output->writeln("Processing ".$file->getRealPath());
            $contents = json_decode($file->getContents(), true);

            if (!$contents) {
                $output->writeln('Fail to read content of '.$file->getRealPath());
                continue;
            }

            foreach ($contents as $doc) {
                $document = new SolrDocument($doc);
                $solr->add($document);
            }
            $progressBar->advance(1);
        }
        $solr->commit();
        $solr->optimize();
        $progressBar->finish();
        $event = $stopwatch->stop('import');
        $output->writeln('');
        $output->writeln(
            'Finished. Took (' . round($event->getDuration() / 1000, 2) . ')s'
        );
        $output->writeln(
            'Max Memory Usage: ' . round($event->getMemory() / 1000, 2) . ' KB'
        );
    }
}
