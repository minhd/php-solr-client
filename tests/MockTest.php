<?php

namespace MinhD\SolrClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;

class MockTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_mock_something()
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
//            new Response(200, ['X-Foo' => 'Bar']),
//            new Response(202, ['Content-Length' => 0]),
            new Response(200, [], 'asdfasdf'),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->assertTrue(true);
    }
}
