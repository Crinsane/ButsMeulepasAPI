<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

abstract class Repository
{
    /**
     * Instance of the Guzzle client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * PostRepository constructor.
     *
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Decode the response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \stdClass|array
     */
    protected function decodeResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents());
    }
}