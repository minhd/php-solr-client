<?php


namespace MinhD\SolrClient;

class SolrManager
{
    private $solr = null;

    /**
     * SolrManager constructor.
     *
     * @param null $solr
     */
    public function __construct($solr)
    {
        $this->solr = $solr;
    }

    public function solr()
    {
        return $this->solr;
    }
}
