<?php


namespace MinhD\SolrClient;

class SchemaManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_get_schema_correctly()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $schema = $solr->schema()->get();

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

    /** @test **/
    public function it_should_add_and_delete_fieldType()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');

        // add it
        $result = $solr->schema()->setFieldTypes([
            ['name' => 'alphaOnlySort', 'class' => 'solr.TextField']
        ]);

        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertTrue($solr->schema()->hasFieldType('alphaOnlySort'));

        // remove it
        $result = $solr->schema()->deleteFieldTypes(['alphaOnlySort']);
        $this->assertArrayNotHasKey('errors', $result);
        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertFalse($solr->schema()->hasFieldType('alphaOnlySort'));

        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_add_and_delete_dynamicField()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');

        // add it
        $result = $solr->schema()->setDynamicFields([
            ['name' => '*_search', 'type' => 'text_en_splitting']
        ]);

        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertTrue($solr->schema()->hasDynamicField('*_search'));

        // remove it
        $result = $solr->schema()->deleteDynamicFields(['*_search']);
        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertFalse($solr->schema()->hasDynamicField('*_search'));
    }

    /** @test **/
    public function it_should_add_and_delete_copy_field()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');

        // add the fields
        $result = $solr->schema()->setFields([
            ['name' => 'field1', 'type' => 'strings'],
            ['name' => 'field2', 'type' => 'text_en_splitting']
        ]);

        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertTrue($solr->schema()->hasField('field1'));
        $this->assertTrue($solr->schema()->hasField('field2'));

        // add the copyField
        $result = $solr->schema()->setCopyFields([
            ['source' => 'field1', 'dest' => 'field2']
        ]);
        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertTrue($solr->schema()->hasCopyField('field1', 'field2'));

        // remove the copyField
        $result = $solr->schema()->deleteCopyField('field1', 'field2');
        $solr->collections()->reload('gettingstarted');
        $this->assertEquals($result['responseHeader']['status'], 0);
        $this->assertFalse($solr->schema()->hasCopyField('field1', 'dest2'));

        // remove the fields
        $solr->schema()->deleteFields(['field1', 'field2']);
        $solr->collections()->reload('gettingstarted');
    }
}
