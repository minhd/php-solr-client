<?php

namespace MinhD\SolrClient;

use PHPUnit_Framework_TestCase;

class SolrClientTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_create_new_instance()
    {
        $actual = new SolrClient;
        $this->assertInstanceOf('\MinhD\SolrClient\SolrClient', $actual);
    }

    /** @test * */
    public function it_should_be_able_to_get_base_url()
    {
        // default
        $solr = new SolrClient;
        $this->assertEquals('http://localhost:8983/solr/', $solr->getBaseUrl());

        $solr = new SolrClient('http://development.dev', 8080);
        $this->assertEquals('http://development.dev:8080/solr/', $solr->getBaseUrl());

        $solr = new SolrClient('development.dev/', 8080);
        $this->assertEquals('http://development.dev:8080/solr/', $solr->getBaseUrl());

        $solr = new SolrClient('development.dev/', 8080);
        $solr->setPath('solr2');
        $this->assertEquals('http://development.dev:8080/solr2/', $solr->getBaseUrl());
    }

    /** @test **/
    public function it_should_be_able_to_get_status()
    {
        $solr = new SolrClient('localhost', 8983);
        $status = $solr->status();
        $this->assertEquals(0, $status['responseHeader']['status']);
        $this->assertTrue(array_key_exists('gettingstarted_shard1_replica1', $status['status']));
    }

    /** @test **/
    public function it_should_be_able_to_reload_a_core()
    {
        $solr = new SolrClient('localhost', 8983);
        $result = $solr->reload('gettingstarted_shard1_replica1');
        $this->assertEquals(0, $result['responseHeader']['status']);
    }

    /** @test **/
    public function it_should_commit()
    {
        $solr = new SolrClient('localhost', 8983);
        $solr->setCore('gettingstarted');
        $result = $solr->commit();
        $this->assertEquals(0, $result['responseHeader']['status']);
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_should_optimize()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $result = $solr->optimize();
        $this->assertEquals(0, $result['responseHeader']['status']);
        $this->assertTrue(true);
    }
}
