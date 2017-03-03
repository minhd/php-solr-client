<?php

namespace MinhD\SolrClient;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use MinhD\SolrClient\SolrClientTrait\CRUDTrait;
use MinhD\SolrClient\SolrClientTrait\CursorMarkTrait;
use MinhD\SolrClient\SolrClientTrait\ManageCollectionTrait;
use MinhD\SolrClient\SolrClientTrait\ManageSchemaTrait;
use MinhD\SolrClient\SolrClientTrait\SearchTrait;

class SolrClient
{
    private $host;
    private $port;
    private $core;
    private $client;

    private $path = 'solr';
    private $autoCommit = false;

    private $errorMessages = [];

    use CRUDTrait;
    use SearchTrait;
    use CursorMarkTrait;

    use ManageSchemaTrait;
    use ManageCollectionTrait;

    /**
     * SolrClient constructor.
     *
     * @param string $host
     * @param int    $port
     * @param string $core
     */
    public function __construct(
        $host = 'http://localhost',
        $port = 8983,
        $core = 'gettingstarted'
    ) {

        $host = $this->cleanHost($host);
        $parts = parse_url($host);
        $this->host = $parts['scheme'] . "://" . $parts['host'];

        $this->port = $port;
        if (array_key_exists('port', $parts)) {
            $this->port = $parts['port'];
        }

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
        try {
            return $this->request('GET', 'admin/cores', [
                'action' => 'status'
            ]);
        } catch (RequestException $e) {
            $this->logError('Failed to connect with request: '.Psr7\str($e->getRequest()));

            return false;
        }
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
     * @param bool|mixed $core
     *
     * @return mixed
     */
    public function optimize($core = null)
    {
        if ($core === null) {
            $core = $this->getCore();
        }

        return $this->request('GET', $core.'/update', ['optimize' => 'true']);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $query
     * @param array  $body
     *
     * @return mixed
     */
    public function request($method, $path, $query, $body = [])
    {
        // reset errors
        $this->errorMessages = [];

        $query['wt'] = 'json';
        $request = [
            'Accept' => 'application/json',
            'query' => $query
        ];

        if ($method != 'GET') {
            $request['json'] = $body;
        }

        try {
            $res = $this->client->request($method, $path, $request);

            return json_decode($res->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($e->getResponse() === null) {
                $this->logError($e->getMessage());

                return false;
            }

            // 4xx
            if ($e->getResponse()->getStatusCode() == 400) {
                $content = $e->getResponse()->getBody()->getContents();
                $this->logError($content);

                return json_decode($content, true);
            }

            if ($e->getResponse()->getStatusCode() === 404) {
                $this->logError('404. Path: '.$e->getRequest()->getUri()->getPath(). " doesn't exist");

                return false;
            }

            $this->logError($e->getMessage());

            return false;
        }
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
     *
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

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errorMessages;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return count($this->getErrors()) > 0 ? true : false;
    }

    /**
     * @param string $errorMessages
     *
     * @return $this
     */
    public function logError($errorMessages)
    {
        $this->errorMessages[] = $errorMessages;

        return $this;
    }
}
