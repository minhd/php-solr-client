<?php


namespace MinhD\SolrClient;

class SchemaManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_get_schema_correctly()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $schemaResult = $solr->schema()->get();

        $this->assertEquals($schemaResult['responseHeader']['status'], 0);
        $this->assertArrayHasKey('schema', $schemaResult);
        $schema = $schemaResult['schema'];
        $this->assertArrayHasKey('fields', $schema);
        $this->assertArrayHasKey('fieldTypes', $schema);
        $this->assertArrayHasKey('dynamicFields', $schema);
        $this->assertArrayHasKey('copyFields', $schema);
    }

    /** @test **/
    public function it_should_add_and_update_field_correctly()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');

        // set a field and make sure it's there
        $result = $solr->schema()->setFields([
            ['name' => 'title', 'type' => 'string', 'stored' => true]
        ]);
        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertTrue($solr->schema()->hasField('title'));
        $field = $solr->schema()->getField('title');
        $this->assertEquals($field, ['name' => 'title', 'type' => 'string', 'stored' => true]);

        // set it back to strings and make sure it's there and check the type
        $result = $solr->schema()->setFields([
            ['name' => 'title', 'type' => 'strings', 'stored' => true]
        ]);
        $solr->collections()->reload('gettingstarted');

        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertTrue($solr->schema()->hasField('title'));
        $field = $solr->schema()->getField('title');
        $this->assertEquals($field['type'], 'strings');
    }

    /** @test **/
    public function it_should_delete_field_correctly()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');

        // add the field to be deleted
        $result = $solr->schema()->setFields([
            ['name' => 'description', 'type' => 'text_en_splitting', 'stored' => true]
        ]);
        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertTrue($solr->schema()->hasField('description'));

        // remove it
        $result = $solr->schema()->deleteFields(['description']);
        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertFalse($solr->schema()->hasField('description'));
    }
}
