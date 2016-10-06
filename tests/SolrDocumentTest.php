<?php

namespace MinhD\SolrClient;

use PHPUnit_Framework_TestCase;

class SolrDocumentTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_be_able_to_init()
    {
        $doc = new SolrDocument();
        $doc->id = 12345;
        $this->assertEquals($doc->id, 12345);

        $expected = ['id' => 12345];
        $actual = $doc->toArray();
        $this->assertEquals($expected, $actual);

        $expected = '{"id":12345}';
        $actual = $doc->toJSON();
        $this->assertEquals($expected, $actual);

        $doc->title = 'fish';
        $expected = '{"id":12345,"title":"fish"}';
        $actual = $doc->toJSON();
        $this->assertEquals($expected, $actual);

        $doc = new SolrDocument(['id' => 12345]);
        $this->assertEquals($doc->id, 12345);
    }
}
