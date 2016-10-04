<?php


namespace MinhD\SolrClient;


class SolrSearchResult
{
    private $numFound;
    private $docs;
    private $params;
    private $client;

    /**
     * SolrSearchResult constructor.
     * @param mixed $payload
     * @param SolrClient $client
     */
    public function __construct($payload, SolrClient $client)
    {
        $this->init($payload);
        $this->client = $client;
    }

    /**
     * @param mixed  $payload
     */
    public function init($payload)
    {
        $this->params = $payload['responseHeader']['params'];
        $this->numFound = $payload['response']['numFound'];

        $this->docs = [];
        foreach ($payload['response']['docs'] as $doc) {
            $this->docs[] = new SolrDocument($doc);
        }
    }

    public function next($pp = 10)
    {
        $nextPageParams = $this->params;
        $nextPageParams['start'] = $this->params['start'] + $pp;

        return $this->client->search($nextPageParams);
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
     * @return mixed
     */
    public function getParam($name)
    {
        return $this->params[$name];
    }


}