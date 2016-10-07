<?php


namespace MinhD\SolrClient\SolrClientTrait;

trait CursorMarkTrait
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
        $options = []
    ) {
        $defaultOptions = [
            'sort' => 'id desc',
            'fl' => '*',
            'q' => '*',
            'rows' => 100
        ];

        $params = array_merge(
            $defaultOptions,
            $options,
            [
                'cursorMark' => $start,
                'rows' => $rows
            ]
        );

        $result = $this->search($params);

        return $result;
    }
}
