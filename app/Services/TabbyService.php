<?php

namespace App\Services;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TabbyService
{
    protected $client;
    protected $baseUrl;
    protected $publicKey;
    protected $secretKey;
    protected $merchantId;

    public function __construct()
    {
        $this->merchantId = env('MARCHENT_CODE');   
        $this->baseUrl = config('services.tabby.api_url');
        $this->publicKey = config('services.tabby.public_key');
        $this->secretKey = config('services.tabby.secret_key');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Create a checkout session with Tabby
     *
     * @param array $data
     * @return array
     */
    public function createCheckoutSession(array $data)
    {
        try {
            $response = $this->client->post('v2/checkout', [
                'json' => $data,
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            // Handle errors
            if ($e->hasResponse()) {
                return [
                    'error' => true,
                    'message' => $e->getResponse()->getBody()->getContents()
                ];
            }
            return [
                'error' => true,
                'message' => 'An error occurred while creating the checkout session.'
            ];
        }
    }
}
