<?php

namespace MinhD\SolrClient;

/**
 * Class SolrClientCRUDTrait
 * create = add
 * read = get
 * update = update
 * delete = remove
 */
trait SolrClientCRUDTrait
{
    /**
     * @param SolrDocument $document
     *
     * @return mixed
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
     * @param int   $id
     * @param array $content
     *
     * @return mixed
     */
    public function update($id, $content)
    {
        $content['id'] = $id;

        $result =  $this->request(
            'POST',
            $this->getCore(). '/update/json',
            [],
            [
                'add' => [
                    'doc' => $content
                ]
            ]
        );

        if ($this->isAutoCommit()) {
            $this->commit();
        }

        return $result;
    }

    /**
     * @param array $ids
     *
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
}
