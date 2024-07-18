<?php

namespace _JchOptimizeVendor\Psr\Http\Client;

use _JchOptimizeVendor\Psr\Http\Message\RequestInterface;
use _JchOptimizeVendor\Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface if an error happens while processing the request
     */
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
