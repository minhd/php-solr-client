<?php


namespace MinhD\SolrClient\SolrClientTrait;

use MinhD\SolrClient\SolrSearchResult;

trait SearchTrait
{
    private $searchParams = [
        'start' => 0,
        'rows' => 10
    ];

    private $result = null;

    /**
     * @param string $query
     *
     * @return SolrSearchResult
     */
    public function query($query)
    {
        return $this->search(
            array_merge($this->getSearchParams(), ['q' => $query])
        );
    }

    /**
     * @param mixed $parameters
     *
     * @return SolrSearchResult
     */
    public function search($parameters)
    {
        $this->result = null;
        $result = $this->request('GET', $this->getCore().'/select', $parameters);
        $this->result = new SolrSearchResult($result, $this);

        return $this->result;
    }

    /**
     * @return array
     */
    public function getSearchParams()
    {
        return $this->searchParams;
    }

    /**
     * @param string $name
     * @return null
     */
    public function getSearchParam($name)
    {
        return array_key_exists($name, $this->searchParams) ?
            $this->searchParams[$name] : null;
    }

    /**
     * Set the facet to search on
     *
     * @param string $name
     *
     * @return $this
     */
    public function setFacet($name)
    {
        if ($this->getSearchParam('facet') === null) {
            $this->setSearchParams('facet', 'true');
        }

        $this->setSearchParams('facet.field', $name);

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setSearchParams($name, $value)
    {
        $this->searchParams[$name] = $value;

        return $this;
    }
}
