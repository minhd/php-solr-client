<?php

namespace MinhD\SolrClient;

class SolrSearchResultTest extends \PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_init()
    {
        $payload = [
            'responseHeader' => [
                'status' => 0,
                'QTime' => 0,
                'params' => [
                    'q' => 'test',
                    'start' => 0,
                    'rows' => 10
                ]
            ],
            'response' => [
                'numFound' => 1,
                'start' => 0,
                'docs' => [
                    [
                        'id' => '1',
                        'title' => ['test']
                    ]
                ]
            ],
            'facet_counts' => [
                'facet_fields' => [
                    'title' => [
                        0 => 'test',
                        1 => 1
                    ]
                ]
            ]
        ];

        $result = new SolrSearchResult($payload, new SolrClient);
        $this->assertEquals($result->getParams(), $payload['responseHeader']['params']);
        $this->assertEquals($result->getFacets(), $payload['facet_counts']);
        $this->assertEquals($result->getFacetField('title'), ['test' => 1]);
    }
}
