<?php

namespace DanGreaves\ParseHub;

use GuzzleHttp\Client;
use Illuminate\Support\Fluent;
use Illuminate\Support\Collection;

/**
 * Unofficial PHP SDK for ParseHub.
 */
class ParseHub
{
    /**
     * API token as provided by ParseHub.
     *
     * @var string
     */
    protected $token;

    /**
     * Guzzle client for performing HTTP requests.
     *
     * @var Client
     */
    protected $client;

    /**
     * Construct a new instance of the SDK.
     *
     * @param string $token API token as provided by ParseHub.
     */
    public function __construct($token)
    {
        $this->token  = $token;
        $this->client = $this->generateClient();
    }

    /**
     * Fetch project with the provided token.
     *
     * @param  string $token
     * @return Fluent
     */
    public function getProject($token)
    {
        return $this->get('projects/'.$token);
    }

    /**
     * Make a GET API request to the provided resource.
     *
     * @param  string $uri
     * @return Collection|Fluent
     */
    protected function get($uri)
    {
        $response = $this->client->get($uri);
        $payload  = json_decode((string) $response->getBody());

        if (is_array($payload)) {
            return (new Collection($payload))->map(function ($item) {
                return new Fluent($item);
            });
        } else {
            return new Fluent($payload);
        }
    }

    /**
     * Generate a Guzzle client instance.
     *
     * @return Client
     */
    protected function generateClient()
    {
        return new Client([
            'base_uri' => 'https://www.parsehub.com/api/v2/',
            'query'    => ['api_key' => $this->token]
        ]);
    }
}
