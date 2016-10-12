<?php


namespace MinhD\SolrClient;

use MinhD\SolrClient\Commands\SolrImportCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SolrImportCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_import_from_file_correctly()
    {
        // set up application
        $application = new Application();
        $application->add(new SolrImportCommand());
        $command = $application->find('solr:import');
        $commandTester = new CommandTester($command);

        $path = getenv('TEST_PATH').'/assets/import/data';

        $commandTester->execute([
            '-t' => $path
        ]);

        $this->assertRegExp('/There are 1 files to import/', $commandTester->getDisplay());
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());

        // test if the file exists
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $this->assertInstanceOf(SolrDocument::class, $solr->get(1));
        $this->assertInstanceOf(SolrDocument::class, $solr->get(2));
    }

    /** @test **/
    public function it_should_import_schema_correctly()
    {
        // TODO: implement
        // have a schema.json file in /assets/import/schema
        // import it
        // make sure the new file is there
        // import the old one back
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_fail_to_read_some_file()
    {
        // TODO: implement
        // have a weird file,
        // expect message
        $this->assertTrue(true);
    }

    public function tearDown()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $solr->removeByQuery('*:*');
        $solr->commit();
    }
}
