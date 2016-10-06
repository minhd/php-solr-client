<?php

namespace MinhD\SolrClient;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Symfony\Component\Config\Definition\Exception\Exception;

class SolrClient
{
    private $host;
    private $port;
    private $core;
    private $client;

    private $path = 'solr';
    private $autoCommit = false;

    private $errorMessages = [];

    use SolrClientCRUDTrait;
    use SolrClientSearchTrait;

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
    private function request($method, $path, $query, $body = [])
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
