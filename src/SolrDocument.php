<?php

namespace MinhD\SolrClient;

class SolrDocument
{
    private $props = [];

    /**
     * SolrDocument constructor.
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        $this->props = $props;
    }

    public function __get($name)
    {
        return $this->props[$name];
    }

    public function __set($name, $value)
    {
        $this->props[$name] = $value;
    }

    public function toArray()
    {
        return $this->props;
    }

    public function toJSON()
    {
        return json_encode($this->toArray(), true);
    }
}
