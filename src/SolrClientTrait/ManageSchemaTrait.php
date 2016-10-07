<?php


namespace MinhD\SolrClient\SolrClientTrait;

use MinhD\SolrClient\Admin\SchemaManager;

trait ManageSchemaTrait
{
    public function schema()
    {
        return new SchemaManager($this);
    }
}
