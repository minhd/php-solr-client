<?php


namespace MinhD\SolrClient;

class CollectionManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @test * */
    public function it_should_create_and_delete_collection()
    {
        $solr = new SolrClient('localhost', 8983);

        $result = $solr->collections()->create('newcol', [
            'collection.configName' => 'gettingstarted',
            'numShards' => 1
        ]);
        $this->assertEquals(0, $result['responseHeader']['status']);

        $result = $solr->collections()->delete('newcol');
        $this->assertEquals(0, $result['responseHeader']['status']);
    }
}
