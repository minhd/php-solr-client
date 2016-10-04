<?php


namespace MinhD\SolrClient;


class SolrSearchClientSearchTraitTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_search_correctly()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $solr->setAutoCommit(true);

        // add a document, auto commit
        $solr->add(new SolrDocument(['id' => 1, 'title' => 'test']));
        $solr->add(new SolrDocument(['id' => 2, 'title' => 'another']));

        $result = $solr->query('test');
        $docs = $result->getDocs();
        $this->assertEquals(1, $result->getNumFound());
        $this->assertEquals(1, $docs[0]->id);

        $solr->remove([1,2]);
    }

    /** @test **/
    public function it_should_search_and_go_next_page()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');

        // add 100 document
        for ($i = 0; $i < 100; $i++) {
            $solr->add(new SolrDocument(['id' => $i, 'title' => 'test:'.$i]));
        }
        $solr->commit();

        $result = $solr->query('*:*');

        $this->assertEquals(100, $result->getNumFound());
        $this->assertEquals(10, count($result->getDocs()));

        $result = $result->next(10, $solr);
        $this->assertEquals(100, $result->getNumFound());
        $this->assertEquals(10, $result->getParam('start'));

        $solr->removeByQuery("*:*");
        $solr->commit();

    }
}
