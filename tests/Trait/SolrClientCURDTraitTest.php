<?php


namespace MinhD\SolrClient;

class SolrClientCURDTraitTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function round_trip()
    {
        $solr = new SolrClient('localhost', 8983);
        $solr->setCore('gettingstarted');
        $document = new SolrDocument(['id' => 12345, 'title' => 'fish']);
        $result = $solr->add($document);
        $this->assertEquals(0, $result['responseHeader']['status']);
        $solr->commit();

        $doc = $solr->get(12345);
        $this->assertEquals($doc->id, 12345);

        $solr->remove(12345);
        $solr->commit();

        $doc = $solr->get(12345);
        $this->assertNull($doc);
    }

    /** @test **/
    public function it_should_add_and_commit()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $solr->setAutoCommit(true);

        // add a document, auto commit
        $solr->add(new SolrDocument(['id' => 1, 'title' => 'test']));

        // get the document right away, make sure it's there
        $doc = $solr->get(1);
        $this->assertNotNull($doc);

        // remove the document, auto commit
        $solr->remove(1);

        // make sure it's gone
        $doc = $solr->get(1);
        $this->assertNull($doc);
    }

    /** @test **/
    public function it_should_update()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $solr->setAutoCommit(true);

        // add a document, auto commit
        $solr->add(new SolrDocument(['id' => 1, 'title' => 'test']));

        // get the document right away, make sure it's there
        $doc = $solr->get(1);
        $this->assertNotNull($doc);
        $this->assertEquals(['test'], $doc->title);

        // update
        $solr->update(1, ['title' => 'changed']);

        $doc = $solr->get(1);
        $this->assertNotNull($doc);
        $this->assertEquals(['changed'], $doc->title);

        // cleanup
        $solr->remove(1);

        // make sure it's gone
        $doc = $solr->get(1);
        $this->assertNull($doc);

        $this->assertTrue(true);
    }
}
