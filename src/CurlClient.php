<?php

namespace Ok\CurlClient;

use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\UriResolver;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Process\Factory;
use Illuminate\Process\PendingProcess;
use Illuminate\Process\Pool;
use Ok\CurlClient\Exceptions\ClientException;
use Ok\CurlClient\Exceptions\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;


class CurlClient
{
    private array $options = [];
    private string $script_path;

    public function __construct(string $script_path)
    {
        $this->configureDefaultOptions();
        $this->script_path = $script_path;
    }

    /**
     * @throws RequestException
     * @throws ClientException
     */
    public function post(string $uri, array $options = []): ResponseInterface
    {
        return $this->sendRequest('post', $uri, $options);
    }

    /**
     * @throws RequestException
     * @throws ClientException
     */
    public function get(string $uri, array $options = []): ResponseInterface
    {
        return $this->sendRequest('get', $uri, $options);
    }

    /**
     * @throws ClientException
     */
    public function makeCommand(RequestInterface $request, array $options = []): string
    {
        return (new CurlCommand($this->script_path))->createCommand($request, $options);
    }

    /**
     * @throws ClientException
     * @throws RequestException
     */
    public function sendRequest(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options = $this->prepareDefaults($options);

        $request = $this->makeRequest($method, $uri, $options);

        $result = $this->makeProcess(
            $this->makeCommand($request, $options)
        )->run();

        return $this->makeResponse($result->output(), $request);
    }

    /**
     * @throws RequestException
     */
    public function sendAsync(array $process): array
    {
        $results = (new Factory())->concurrently(function (Pool $pool) use ($process) {
            foreach ($process as $key => $item) {
                $pool->as($key)->command($item->command);
            }
        });

        $response = [];
        foreach (array_keys($process) as $key) {
            if (! $results[$key]->output()) {
                continue;
            }
            $response[$key] = $this->makeResponse($results[$key]->output());
        }

        return $response;
    }

    /**
     * @throws ClientException
     */
    public function makeAsyncProcess(string $method, string $uri, array $options = []): PendingProcess
    {
        $options = $this->prepareDefaults($options);
        $request = $this->makeRequest($method, $uri, $options);

        return $this->makeProcess(
            $this->makeCommand($request, $options)
        );
    }

    public function makeProcess(string $command): PendingProcess
    {
        return (new Factory())->newPendingProcess()->command($command);
    }

    public function makeResponse(string $output, ?RequestInterface $request = null): ResponseInterface
    {
        if (mb_stripos($output, 'HTTP/') > 1) {
            $output = mb_strcut($output, mb_stripos($output, 'HTTP/'));
        }
        if (empty($output)) {
            $exception = new RequestException('Empty output');
            if ($request) {
                $exception->setRequest($request);
            }

            throw $exception;
        }

        return Message::parseResponse($output);
    }

    public function makeRequest(string $method, string $uri, array $options): RequestInterface
    {
        $headers = $options['headers'] ?? [];
        $body = $options['body'] ?? null;
        $version = $options['version'] ?? '1.1';

        $uri = $this->buildUri(Utils::uriFor($uri), $options);

        return new Request($method, $uri, $headers, $body, $version);
    }

    protected function buildUri(UriInterface $uri, array $config): UriInterface
    {
        if (isset($config['base_uri'])) {
            $uri = UriResolver::resolve(Utils::uriFor($config['base_uri']), $uri);
        }

        return $uri->getScheme() === '' && $uri->getHost() !== '' ? $uri->withScheme('http') : $uri;
    }

    private function configureDefaultOptions(): void
    {
        $this->options = [
            'allow_redirects' => true,
            'verify' => false,
            'connect_timeout' => 30
        ];
    }

    private function prepareDefaults(array $options): array
    {
        unset($options['headers']['User-Agent']);
        $defaults = $options + $this->options;

        foreach ($defaults as $k => $v) {
            if ($v === null) {
                unset($defaults[$k]);
            }
        }

        return $defaults;
    }
}