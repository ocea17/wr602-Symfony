<?php

namespace App\Service;

use Stripe\HttpClient\ClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymfonyStripeHttpClient implements ClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    public function request($method, $absUrl, $headers, $params, $hasFile)
    {
        $options = ['headers' => $headers];

        if ($method === 'post') {
            $options['body'] = $params;
        }

        $response = $this->httpClient->request(strtoupper($method), $absUrl, $options);

        return [
            $response->getContent(false),
            $response->getStatusCode(),
            $response->getHeaders(false)
        ];
    }
}