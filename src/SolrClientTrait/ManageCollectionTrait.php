<?php


namespace MinhD\SolrClient\SolrClientTrait;

// https://cwiki.apache.org/confluence/display/solr/Collections+API#CollectionsAPI-api2
use MinhD\SolrClient\Admin\CollectionManager;

trait ManageCollectionTrait
{
    public function collections()
    {
        return new CollectionManager($this);
    }

    /**
     * Helper method to reload a collection
     *
     * @param null $collection
     *
     * @return mixed
     */
    public function reload($collection = null)
    {
        if ($collection === null) {
            $collection = $this->getCore();
        }

        return $this->collections()->reload($collection);
    }
}
