<?php


namespace MinhD\SolrClient;

class SolrSearchResult
{
    private $numFound;
    private $docs;

    private $errorMessage = null;

    private $facets = null;
    private $facetFields = null;

    private $params;
    private $client;

    private $cursorMark = null;
    private $nextCursorMark = null;

    /**
     * SolrSearchResult constructor.
     *
     * @param mixed      $payload
     * @param SolrClient $client
     */
    public function __construct($payload, SolrClient $client)
    {
        if ($payload === false) {
            return null;
        }

        $this->init($payload);
        $this->client = $client;
    }

    /**
     * @param mixed $payload
     * @throws \Exception
     */
    public function init($payload)
    {
        $this->params = $payload['responseHeader']['params'];

        // error
        if (!array_key_exists('response', $payload)) {
            $this->errorMessage = $payload['error']['msg'];
            return;
        }

        // numFound
        $this->numFound = $payload['response']['numFound'];

        // facet_counts
        if (array_key_exists('facet_counts', $payload)) {
            $this->facets = $payload['facet_counts'];
            $this->facetFields = [];
            foreach ($this->facets['facet_fields'] as $name => $facet) {
                for ($i = 0; $i < count($facet) - 1; $i += 2) {
                    $this->facetFields[$name][$facet[$i]] = $facet[$i + 1];
                }
            }
        }

        // docs
        $this->docs = [];
        foreach ($payload['response']['docs'] as $doc) {
            $this->docs[] = new SolrDocument($doc);
        }

        // cursorMark
        if (array_key_exists('nextCursorMark', $payload)) {
            $this->nextCursorMark = $payload['nextCursorMark'];
            $this->cursorMark = $payload['responseHeader']['params']['cursorMark'];
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
     * @param string $format
     *
     * @return mixed
     */
    public function getDocs($format = 'array')
    {
        if ($format == 'json') {
            $filler = [];
            foreach ($this->docs as $doc) {
                $filler[] = $doc->toArray();
            }

            return json_encode($filler, true);
        }

        return $this->docs;
    }

    public function errored()
    {
        return $this->errorMessage != null;
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

    /**
     * @return mixed
     */
    public function getCursorMark()
    {
        return $this->cursorMark;
    }

    /**
     * @return mixed
     */
    public function getNextCursorMark()
    {
        return $this->nextCursorMark;
    }

    /**
     * @return null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
