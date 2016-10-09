<?php

namespace MinhD\SolrClient;

use MinhD\SolrClient\Commands\SolrRunCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SolrRunCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_commit_optimize_correctly()
    {
        $application = new Application();
        $application->add(new SolrRunCommand());
        $command = $application->find('solr:run');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '-d' => 'commit',
            '-c' => 'gettingstarted'
        ]);
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());

        $commandTester->execute([
            '-d' => 'optimize',
            '-c' => 'gettingstarted'
        ]);
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());

        $commandTester->execute([
            '-d' => 'commit',
            '-c' => 'asdfasdf'
        ]);
        $this->assertRegExp('/404/', $commandTester->getDisplay());
    }

    /** @test **/
    public function it_should_create_and_delete_a_collection()
    {
        $application = new Application();
        $application->add(new SolrRunCommand());
        $command = $application->find('solr:run');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '-d' => 'create',
            '-c' => 'testcol'
        ]);
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());

        $commandTester->execute([
            '-d' => 'delete',
            '-c' => 'testcol'
        ]);
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());

        $commandTester->execute([
            '-d' => 'delete',
            '-c' => 'testcol'
        ]);
        $this->assertRegExp('/Could not find collection/', $commandTester->getDisplay());
    }

    /** @test **/
    public function it_should_print_out_unknown_command()
    {
        $application = new Application();
        $application->add(new SolrRunCommand());
        $command = $application->find('solr:run');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '-d' => 'unknowncommand'
        ]);
        $this->assertRegExp('/Unknown -d flag/', $commandTester->getDisplay());
    }

    /** @test **/
    public function it_should_clear_a_collection()
    {
        $application = new Application();
        $application->add(new SolrRunCommand());
        $command = $application->find('solr:run');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '-d' => 'create',
            '-c' => 'testcol'
        ]);
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());

        $commandTester->execute([
            '-d' => 'list'
        ]);
        $this->assertRegExp('/testcol/', $commandTester->getDisplay());

        $commandTester->execute([
            '-d' => 'reload',
            '-c' => 'testcol'
        ]);
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());

        sleep(2);

        $application = new Application();
        $application->add(new SolrRunCommand());
        $command = $application->find('solr:run');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '-d' => 'clear',
            '-c' => 'testcol'
        ]);
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());

        $commandTester->execute([
            '-d' => 'delete',
            '-c' => 'testcol'
        ]);
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());
    }
}
