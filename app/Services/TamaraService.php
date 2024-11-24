<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;

class TamaraService
{
    protected $baseUrl;
    protected $secretKey;
    protected $publicKey;
    protected $countryCode;
    protected $currency;
    protected $client;
    protected $tamaraMode;

    public function __construct()
    {
        $this->tamaraMode = config(key: 'service.tamara.mode');
        $this->baseUrl      = ($this->tamaraMode == 'live')? config('service.tamara.live_api_url') : config('service.tamara.test_api_url');
        $this->secretKey    = config('service.tamara.secret_key');
        $this->publicKey    = config('service.tamara.public_key');
        // $this->currency     = config('tamara.currency');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function createCheckoutSession(array $orderData)
    {
        try{
        $response = $this->client->post("/checkout", ['json' => $orderData]);
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


// public function checkPaymentOptionsAvailability($order)
//     {
//         try {
//             $response = $this->client->post("/checkout/payment-options-pre-check", [
//                 'json' => [
//                     'country' => $this->countryCode,
//                     'phone_number' => $order['phone'],
//                     'order_value' => [
//                         'amount' => $order['total'],
//                         'currency' => $this->currency
//                     ]
//                 ]
//             ]);
//             return json_decode($response->getBody()->getContents(), true);
//         } catch (RequestException $e) {
//             if ($e->hasResponse()) {
//                 return [
//                     'error' => true,
//                     'message' => $e->getResponse()->getBody()->getContents()
//                 ];
//             }
//             return [
//                 'error' => true,
//                 'message' => 'An error occurred while checking payment options availability.'
//             ];
//         }
//     }

//     public function getOrderDetails($orderId)
//     {
//         try{
//             $response = $this->client->get("/orders/$orderId");
//             return json_decode($response->getBody()->getContents(), true);
//         } catch (RequestException $e) {
//             if ($e->hasResponse()) {
//                 return [
//                     'error' => true,
//                     'message' => $e->getResponse()->getBody()->getContents()
//                 ];
//             }
//             return [
//                 'error' => true,
//                 'message' => 'An error occurred while getting order details.'
//             ];
//         }
//     }

//     public function cancelOrder($order)
//     {   
//         $orderId = $order['id'];
//         $data      = [
//             'orderId'      => $order['id'],
//             'total_amount' => [
//                 'amount'   => $order['amount'],
//                 'currency' => $this->currency,
//             ]
//         ];
//         $response  = Http::withHeaders([
//             'Accept'        => 'application/json',
//             'Content-Type'  => 'application/json',
//             'Authorization' => 'Bearer ' . $this->token
//         ])->post($this->baseUrl, $data);

//         $responseResult = json_decode($response->getBody()->getContents(), true);
//         return $responseResult;
//     }

//     public function getPaymentTypes()
//     {
//         $this->baseUrl .= "/checkout/payment-types?country=SA&phone=966506459343&currency=SAR&order_value=1";

//         $response = Http::withHeaders([
//             'Accept'        => 'application/json',
//             'Content-Type'  => 'application/json',
//             'Authorization' => 'Bearer ' . $this->token
//         ])->get( $this->baseUrl);
//         $responseResult = json_decode($response->getBody()->getContents(), true);
//         return $responseResult;
//     }
}