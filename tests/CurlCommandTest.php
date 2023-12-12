<?php

namespace Ok\CurlClient;

use PHPUnit\Framework\TestCase;

class CurlCommandTest extends TestCase
{

    public function testCreateCommand()
    {
        $scriptPath = dirname(__DIR__, 1) . '/bin/curl_default';

        $client = new CurlClient($scriptPath);

        $command = $client->makeCommand(
            $client->makeRequest('get', 'http://127.0.0.1/', []), [
                'allow_redirects' => true,
                'verify' => false,
                'connect_timeout' => 30
        ]);

        $this->assertEquals(
            $scriptPath . " -H 'Host: 127.0.0.1' -X GET -G  --silent -D - -o - --connect-timeout '30' -L --max-redirs 5 --insecure http://127.0.0.1/",
            $command
        );
    }
}
