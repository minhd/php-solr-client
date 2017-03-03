<?php


namespace MinhD\SolrClient;

class SearchTraitTest extends \PHPUnit_Framework_TestCase
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

        $this->assertEquals(1, count($docs));

        $this->assertEquals(1, $result->getNumFound());
        $this->assertEquals(1, $docs[0]->id);

        $solr->remove([1, 2]);
    }

    /** @test **/
    public function it_should_handle_error_correctly()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $solr->setAutoCommit(true);

        $result = $solr->search([
            'q' => 'Something:bad!- )'
        ]);

        $this->assertTrue($result->errored());
        $this->assertNotEmpty($result->getErrorMessage());
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

        $solr->removeByQuery('*:*');
        $solr->commit();
    }

    /** @test **/
    public function it_should_search_and_facet_correctly()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');

        // add 100 document with even and odd
        for ($i = 1; $i < 16; $i++) {
            $solr->add(new SolrDocument([
                'id' => $i,
                'title' => 'test:'.$i,
                'subject' =>  ($i % 2 == 0) ? 'even' : 'odd'
            ]));
        }
        $solr->commit();

        $result = $solr->setFacet('subject')->query('*:*');
        $subjectFacetFields = $result->getFacetField('subject');
        $this->assertEquals(7, $subjectFacetFields['even']);
        $this->assertEquals(8, $subjectFacetFields['odd']);

        $solr->removeByQuery('*:*');
        $solr->commit();
    }
}
