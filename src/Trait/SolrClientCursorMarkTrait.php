<?php


namespace MinhD\SolrClient;


trait SolrClientCursorMarkTrait
{

    /**
     * @param string $start
     * @param array $options
     * @return SolrSearchResult
     */
    public function cursor(
        $start = "*",
        $options = ['sort' => 'id desc', 'fl' => '*', 'rows' => 100, 'q' => '*']
    ) {
        $params = array_merge(['cursorMark' => $start], $options);
        $result = $this->search($params);
        return $result;
    }
}