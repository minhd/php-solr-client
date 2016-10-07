<?php


namespace MinhD\SolrClient;

class CursorMarkTraitTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_get_next_cursor()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');

        for ($i = 0; $i < 250; $i++) {
            $solr->add(new SolrDocument(['id' => $i, 'title' => 'test']));
        }
        $solr->commit();

        $payload = $solr->cursor();
        $this->assertNotNull($payload->getCursorMark());
        $this->assertNotNull($payload->getNextCursorMark());

        $next = $payload->getNextCursorMark();

        $nextPayload = $solr->cursor($next);
        $this->assertNotNull($nextPayload->getCursorMark());
        $this->assertNotNull($nextPayload->getNextCursorMark());
    }

    /** @test **/
    public function it_should_not_get_next_cursor()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $payload = $solr->cursor();
        $this->assertNotNull($payload->getCursorMark());
        $this->assertNotNull($payload->getNextCursorMark());
    }

    public function tearDown()
    {
        $solr = new SolrClient('localhost', 8983, 'gettingstarted');
        $solr->removeByQuery('*:*');
        $solr->commit();
    }
}
