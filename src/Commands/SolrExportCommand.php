<?php

namespace MinhD\SolrClient\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SolrExportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('solr:export')
            ->setDescription('Export a SOLR collection')
            ->setHelp("This command allows you to export a SOLR collection...");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Executed");
    }
}