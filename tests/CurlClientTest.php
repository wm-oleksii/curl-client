<?php

namespace Ok\CurlClient;

use PHPUnit\Framework\TestCase;

class CurlClientTest extends TestCase
{

    public function testGet()
    {
        $scriptPath = dirname(__DIR__, 1) . '/bin/curl_default';

        $client = new CurlClient($scriptPath);

        $response = $client->get('http://ip-api.com/json/');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
