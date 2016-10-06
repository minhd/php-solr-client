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
        if (array_key_exists('_version_', $props)) {
            unset($props['_version_']);
        }

        array_walk_recursive($props, function (&$item) {
            $item = strval($item);
        });

        $this->props = $props;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->props[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->props[$name] = $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->props;
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray(), true);
    }
}
