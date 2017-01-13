<?php

namespace DanGreaves\ParseHub;

use GuzzleHttp\Client;
use Illuminate\Support\Fluent;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

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
     * Run project with the provided token and options.
     *
     * @param  string $token
     * @param  array  $options Optional options.
     * @return Fluent
     */
    public function runProject($token, $options = [])
    {
        return $this->post('projects/'.$token.'/run', array_filter($options));
    }

    /**
     * Fetch run with the provided token.
     *
     * @param  string $token
     * @return Fluent
     */
    public function getRun($token)
    {
        return $this->get('runs/'.$token);
    }

    /**
     * Fetch and store extracted CSV data at the provided path.
     *
     * @param  string $token
     * @param  string $path
     * @param  string $format Defaults to 'csv' but can also use 'json'
     * @return ResponseInterface
     */
    public function storeRunData($token, $path, $format = 'csv')
    {
        return $this->client->get('runs/'.$token.'/data', [
            'sink' => $path,
            'query' => [
                'api_key' => $this->token,
                'format'  => $format
            ]
        ]);
    }

    /**
     * Make a GET API request to the provided resource.
     *
     * @param  string $uri
     * @param  bool   $processResponse
     * @return Collection|Fluent|ResponseInterface
     */
    protected function get($uri, $processResponse = true)
    {
        $response = $this->client->get($uri);

        if ($processResponse) {
            return $this->generateResponsePayload($response);
        }

        return $response;
    }

    /**
     * Make a POST API request to the provided resource.
     *
     * @param  string $uri
     * @param  array  $payload
     * @param  bool   $processResponse
     * @return Collection|Fluent|ResponseInterface
     */
    protected function post($uri, $payload = [], $processResponse = true)
    {
        $response = $this->client->post($uri, [
            'form_params' => array_merge(
                $payload,
                ['api_key' => $this->token]
            )
        ]);

        if ($processResponse) {
            return $this->generateResponsePayload($response);
        }

        return $response;
    }

    /**
     * Convert a response payload to a collection or fluent instance.
     *
     * @param  ResponseInterface $response
     * @return Collection|Fluent
     */
    protected function generateResponsePayload(ResponseInterface $response)
    {
        $payload = json_decode((string) $response->getBody());

        if (is_array($payload)) {
            return (new Collection($payload))->map(function ($item) {
                return new Fluent($item);
            });
        }

        return new Fluent($payload);
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
