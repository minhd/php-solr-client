<?php


namespace MinhD\SolrClient\Admin;

use MinhD\SolrClient\SolrManager;

class CollectionManager extends SolrManager
{
    public function create($name, $options = [])
    {
        $request = array_merge(
            [
                'name' => $name,
                'action' => 'CREATE'
            ],
            $options
        );

        return $this->solr()->request('GET', 'admin/collections', $request);
    }

    public function delete($name)
    {
        return $this->solr()->request('GET', 'admin/collections', [
            'action' => 'DELETE',
            'name' => $name
        ]);
    }

    public function reload($collection = null)
    {
        if ($collection === null) {
            $collection = $this->solr()->getCore();
        }

        return $this->solr()->request('GET', 'admin/collections', [
            'action' => 'RELOAD',
            'name' => $collection
        ]);
    }

    public function listCollections()
    {
        return $this->solr()->request('GET', 'admin/collections', [
            'action' => 'LIST'
        ]);
    }

    public function get()
    {
        $collections = $this->listCollections();

        return $collections['collections'];
    }
}
