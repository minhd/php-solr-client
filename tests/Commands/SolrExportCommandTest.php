<?php


namespace MinhD\SolrClient;

use MinhD\SolrClient\Commands\SolrExportCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SolrExportCommandTest extends \PHPUnit_Framework_TestCase
{
    private $exportPath;

    /** @test **/
    public function it_should_export_data_correctly()
    {
        $application = new Application();
        $application->add(new SolrExportCommand());
        $command = $application->find('solr:export');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '-t' => $this->exportPath.'/data/'
        ]);

        $this->assertRegExp('/There are 2 records to export./', $commandTester->getDisplay());
        $this->assertRegExp('/Finished/', $commandTester->getDisplay());

        // make sure there is a 1.json file generated
        $this->assertTrue(is_file($this->exportPath.'/data/1.json'));
        $content = json_decode(
            file_get_contents($this->exportPath.'/data/1.json'),
            true
        );
        $this->assertContains('test2', $content);
    }

    /** @test **/
    public function it_should_export_schema_correctly()
    {
        $application = new Application();
        $application->add(new SolrExportCommand());
        $command = $application->find('solr:export');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            '-t' => $this->exportPath.'/schema/',
            '--schema-only' => true
        ]);
        $this->assertRegExp('/Schema written to./', $commandTester->getDisplay());
    }

    public function setUp()
    {
        $this->exportPath = getenv('TEST_PATH').'assets/export';

        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $solr->add(new SolrDocument(['id' => 1, 'title_s' => 'test']));
        $solr->add(new SolrDocument(['id' => 2, 'title_s' => 'test2']));
        $solr->commit();
    }

    public function tearDown()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $solr->removeByQuery('*:*');
        $solr->commit();

        // delete all exported files
        array_map('unlink', glob($this->exportPath.'/data/*'));
        array_map('unlink', glob($this->exportPath.'/schema/*'));
    }
}
