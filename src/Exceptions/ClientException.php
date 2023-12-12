<?php

namespace Ok\CurlClient\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;
use Exception;

class ClientException extends Exception implements ClientExceptionInterface
{
}
