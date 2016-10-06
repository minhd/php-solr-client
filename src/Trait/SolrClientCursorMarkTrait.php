<?php


namespace MinhD\SolrClient;

trait SolrClientCursorMarkTrait
{
    /**
     * @param string $start
     * @param int    $rows
     * @param array  $options
     *
     * @return SolrSearchResult
     */
    public function cursor(
        $start = '*',
        $rows = 100,
        $options = ['sort' => 'id desc', 'fl' => '*', 'q' => '*']
    ) {
        $params = array_merge(
            [
                'cursorMark' => $start,
                'rows' => $rows
            ],
            $options
        );
        $result = $this->search($params);

        return $result;
    }
}
