<?php

namespace Ok\CurlClient;

use Ok\CurlClient\Exceptions\ClientException;
use Psr\Http\Message\RequestInterface;

class CurlCommand
{
    private RequestInterface $request;
    private string $script_path;

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

        return implode(' ' , [
            $this->getScriptPath(),
            $this->header(),
            $this->data(),
            $this->additionalData($options),
            $this->request->getUri()
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
            if ($headerName == 'cookies') {
                return null;
            }

            return  "-H '$headerName: {$this->request->getHeaderLine($headerName)}'";
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

        if (! empty($this->options['json'])) {
            $data .= "-d '" . json_encode($this->options['json']) . "' ";
        }

        if (! empty($this->options['data'])) {
            $data .= "-d '" . http_build_query($this->options['data']) . "' ";
        }

        if ($this->request->getUri()->getQuery()) {
            foreach (explode('&', $this->request->getUri()->getQuery()) as $queryData) {
                $data .= "-d '" . urldecode($queryData) . "' ";
            }
        }

        return $data;
    }

    private function additionalData(array $options): string
    {
        $additional = ['--silent -D - -o -'];

        if (! empty($options['proxy'])) {
            $additional[] = "--proxy '" . $options['proxy']  . "'";
        }

        if (! empty($options['connect_timeout'])) {
            $additional[] = "--connect-timeout '" . $options['connect_timeout']  . "'";
        }

        if (isset($options['allow_redirects']) &&  $options['allow_redirects']) {
            $additional[] = "-L --max-redirs 5";
        }

        if (isset($options['verify']) &&  $options['verify'] === false) {
            $additional[] = "--insecure";
        }

        return implode(' ', $additional);
    }
}