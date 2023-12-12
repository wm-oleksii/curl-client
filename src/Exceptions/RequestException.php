<?php

namespace  Ok\CurlClient\Exceptions;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;

class RequestException extends \Exception implements RequestExceptionInterface
{
    private RequestInterface $request;

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }
}