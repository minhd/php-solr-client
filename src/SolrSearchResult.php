<?php


namespace MinhD\SolrClient;

class SolrSearchResult
{
    private $numFound;
    private $docs;

    private $facets = null;
    private $facetFields = null;

    private $params;
    private $client;

    /**
     * SolrSearchResult constructor.
     *
     * @param mixed      $payload
     * @param SolrClient $client
     */
    public function __construct($payload, SolrClient $client)
    {
        $this->init($payload);
        $this->client = $client;
    }

    /**
     * @param mixed $payload
     */
    public function init($payload)
    {
        $this->params = $payload['responseHeader']['params'];
        $this->numFound = $payload['response']['numFound'];

        if (array_key_exists('facet_counts', $payload)) {
            $this->facets = $payload['facet_counts'];
            $this->facetFields = [];
            foreach ($this->facets['facet_fields'] as $name => $facet) {
                for ($i = 0; $i < count($facet) - 1; $i += 2) {
                    $this->facetFields[$name][$facet[$i]] = $facet[$i + 1];
                }
            }
        }

        $this->docs = [];
        foreach ($payload['response']['docs'] as $doc) {
            $this->docs[] = new SolrDocument($doc);
        }
    }

    /**
     * Return the next page SolrSearchResult
     *
     * @param int $pp
     *
     * @return SolrSearchResult
     */
    public function next($pp = 10)
    {
        $nextPageParams = $this->params;
        $nextPageParams['start'] = $this->params['start'] + $pp;

        return $this->client->search($nextPageParams);
    }

    /**
     * Get facet by field
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getFacetField($field)
    {
        return $this->facetFields[$field];
    }

    /**
     * @return mixed
     */
    public function getNumFound()
    {
        return $this->numFound;
    }

    /**
     * @return mixed
     */
    public function getDocs()
    {
        return $this->docs;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getParam($name)
    {
        return $this->params[$name];
    }

    /**
     * @return mixed
     */
    public function getFacets()
    {
        return $this->facets;
    }
}
