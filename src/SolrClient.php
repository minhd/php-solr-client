<?php

namespace MinhD\SolrClient;

use GuzzleHttp\Client as HttpClient;

class SolrClient
{
    private $host;
    private $port;
    private $core;
    private $client;

    private $path = 'solr';
    private $autoCommit = false;

    /**
     * SolrClient constructor.
     *
     * @param string $host
     * @param int $port
     * @param string $core
     */
    public function __construct(
        $host = 'http://localhost',
        $port = 8983,
        $core = 'collection1'
    ) {

        $this->host = $this->cleanHost($host);
        $this->port = $port;

        $this->client = new HttpClient([
            'base_uri' => $this->getBaseUrl()
        ]);

        $this->setCore($core);
    }

    /**
     * @return mixed
     */
    public function status()
    {
        return $this->request('GET', 'admin/cores', [
            'action' => 'status'
        ]);
    }

    /**
     * @param string $core
     *
     * @return mixed
     */
    public function reload($core)
    {
        return $this->request('GET', 'admin/cores', [
            'action' => 'RELOAD',
            'core' => $core
        ]);
    }

    /**
     * @param SolrDocument $document
     * @return mixed
     *
     */
    public function add(SolrDocument $document)
    {
        $result = $this->request(
            'POST',
            $this->getCore() . '/update/json',
            [],
            [
                'add' => [
                    'doc' => $document->toArray()
                ]
            ]
        );

        if ($this->isAutoCommit()) {
            $this->commit();
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function commit()
    {
        return $this->request(
            'GET',
            $this->getCore() . '/update',
            ['commit' => 'true']
        );
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function remove($ids = [])
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $result = $this->request(
            'POST',
            $this->getCore() . '/update/json',
            [],
            [
                'delete' => $ids
            ]
        );

        if ($this->isAutoCommit()) {
            $this->commit();
        }

        return $result;
    }

    /**
     * @param int $id
     *
     * @return SolrDocument|null
     */
    public function get($id)
    {
        $result = $this->request('GET', $this->getCore() . '/select', [
            'q' => 'id:' . $id
        ]);

        if (array_key_exists(0, $result['response']['docs'])) {
            $doc = $result['response']['docs'][0];

            return new SolrDocument($doc);
        }

        return null;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $query
     * @param array $body
     *
     * @return mixed
     */
    private function request($method, $path, $query, $body = [])
    {
        $query['wt'] = 'json';
        $request = [
            'Accept' => 'application/json',
            'query' => $query
        ];

        if ($method != 'GET') {
            $request['json'] = $body;
        }

        $res = $this->client->request($method, $path, $request);
        $result = json_decode($res->getBody()->getContents(), true);

        return $result;
    }

    /**
     * Return the correct base url of the port
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->host . ':' . $this->port . '/' . $this->path . '/';
    }

    /**
     * Helper method to clean the host url
     *
     * @param string $host
     *
     * @return string $host
     */
    private function cleanHost($host)
    {
        // communicate via http by default if none is assigned
        if (strpos($host, 'http://') === false || strpos($host, 'https://')) {
            $host = 'http://' . $host;
        }

        // remove / at the end
        $host = rtrim($host, '/');

        return $host;
    }

    /**
     * @param string $path
     *
     * @return SolrClient
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param mixed $core
     *
     * @return SolrClient
     */
    public function setCore($core)
    {
        $this->core = $core;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @param boolean $autoCommit
     * @return SolrClient
     */
    public function setAutoCommit($autoCommit)
    {
        $this->autoCommit = $autoCommit;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAutoCommit()
    {
        return $this->autoCommit;
    }
}
