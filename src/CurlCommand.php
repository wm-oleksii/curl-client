<?php

namespace Ok\CurlClient;

use Ok\CurlClient\Exceptions\ClientException;
use Psr\Http\Message\RequestInterface;

class CurlCommand
{
    private RequestInterface $request;
    private string $script_path;

    private array $options;

    public function __construct(string $path)
    {
        $this->script_path = $path;
    }

    /**
     * @throws ClientException
     */
    public function createCommand(RequestInterface $request, array $options): string
    {
        $this->request = $request;
        $this->options = $options;

        return implode(' ', [
            $this->getScriptPath(),
            $this->header(),
            $this->data(),
            $this->additionalData(),
            $this->request->getUri(),
        ]);
    }

    /**
     * @throws ClientException
     */
    private function getScriptPath(): string
    {
        if (! file_exists($this->script_path) && ! is_file($this->script_path)) {
            throw new ClientException("Unable to locate curl script_path {$this->script_path}");
        }

        return $this->script_path;
    }

    private function header(): string
    {
        $headersLine = array_map(function ($headerName) {
            if ($headerName === 'cookies') {
                return null;
            }

            return "-H '{$headerName}: {$this->request->getHeaderLine($headerName)}'";
        }, array_keys($this->request->getHeaders()));

        return implode(' ', array_filter($headersLine));
    }

    private function data(): string
    {
        $data = match ($this->request->getMethod()) {
            'GET' => '-X GET -G ',
            'POST' => '-X POST ',
            'DELETE' => '-X DELETE ',
            'PUT' => '-X PUT ',
            'HEAD' => '-I ',
        };

        if (key_exists('json', $this->options) && is_array($this->options['json'])) {
            $data .= "-d '" . json_encode($this->options['json']) . "' ";
        }

        if (key_exists('data', $this->options) && is_array($this->options['data'])) {
            $data .= "-d '" . http_build_query($this->options['data']) . "' ";
        }

        if ($this->request->getUri()->getQuery()) {
            foreach (explode('&', $this->request->getUri()->getQuery()) as $queryData) {
                $data .= "-d '" . urldecode($queryData) . "' ";
            }
        }

        return $data;
    }

    private function additionalData(): string
    {
        $additional = ['--silent -D - -o -'];

        if (key_exists('proxy', $this->options)) {
            $additional[] = "--proxy '{$this->options['proxy']}'";
        }

        if (key_exists('connect_timeout', $this->options)) {
            $additional[] = "--connect-timeout '{$this->options['connect_timeout']}'";
        }

        if (key_exists('allow_redirects', $this->options) && $this->options['allow_redirects']) {
            $additional[] = '-L --max-redirs 5';
        }

        if (key_exists('verify', $this->options) && $this->options['verify'] === false) {
            $additional[] = '--insecure';
        }

        return implode(' ', $additional);
    }
}
