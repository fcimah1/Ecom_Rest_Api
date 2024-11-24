<?php

namespace App\Utility;

use App\Models\OtpConfiguration;
use App\Utility\MimoUtility;
// use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
class SendSMSUtility
{
    public static function sendSMS($to, $from, $text, $template_id)
    {
            //  $phone = preg_replace('/^\+?2/', '002', $to);

// if (preg_match('/^002\d{11}$/', $to)) {
         $url = 'https://live.my-compound.com/api/test-phone-sms-for-companies';

        $data = [
            "data" => [
                "account_id" => "12345",
                "account_password" => "123456789",
                "sender_name" => "MyCompound",
                "security_hash" => "61a50058cc2613928145a22b65207a20fb4ffe4972bb107cf60d5d1025574162",
                "receiver_msisdn" => "$to",
                "sms_text" => "$text"
            ]
        ];

        $client = new Client();

        $response = $client->post($url, [
            'json' => $data,
        ]);

// You can then handle the response as needed
        $responseBody = $response->getBody()->getContents();
        $responseStatus = $response->getStatusCode();
//        return $response->getBody();

   $jsonResponse = json_decode($response->getBody(), true);
        $status = $jsonResponse['status'];

      
        Log::error('send'.$responseStatus);
        Log::error($data);
        Log::error($jsonResponse);
         
            //   return  $jsonResponse;
        
// }
        
        
        
        
        
        
        
        // if (OtpConfiguration::where('type', 'nexmo')->first()->value == 1) {
        //     $api_key = env("NEXMO_KEY"); //put ssl provided api_token here
        //     $api_secret = env("NEXMO_SECRET"); // put ssl provided sid here

        //     $params = [
        //         "api_key" => $api_key,
        //         "api_secret" => $api_secret,
        //         "from" => $from,
        //         "text" => $text,
        //         "to" => $to
        //     ];

        //     $url = "https://rest.nexmo.com/sms/json";
        //     $params = json_encode($params);

        //     $ch = curl_init(); // Initialize cURL
        //     curl_setopt($ch, CURLOPT_URL, $url);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        //     curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        //     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //         'Content-Type: application/json',
        //         'Content-Length: ' . strlen($params),
        //         'accept:application/json'
        //     ));
        //     $response = curl_exec($ch);
        //     curl_close($ch);

        //     return $response;
        // } elseif (OtpConfiguration::where('type', 'twillo')->first()->value == 1) {
        //     $sid = env("TWILIO_SID"); // Your Account SID from www.twilio.com/console
        //     $token = env("TWILIO_AUTH_TOKEN"); // Your Auth Token from www.twilio.com/console

        //     $client = new Client($sid, $token);
        //     try {
        //         $client->messages->create(
        //             $to, // Text this number
        //             array(
        //                 'from' => env('VALID_TWILLO_NUMBER'), // From a valid Twilio number
        //                 'body' => $text
        //             )
        //         );
        //     } catch (\Exception $e) {

        //     }

        // } elseif (OtpConfiguration::where('type', 'ssl_wireless')->first()->value == 1) {
        //     $token = env("SSL_SMS_API_TOKEN"); //put ssl provided api_token here
        //     $sid = env("SSL_SMS_SID"); // put ssl provided sid here

        //     $params = [
        //         "api_token" => $token,
        //         "sid" => $sid,
        //         "msisdn" => $to,
        //         "sms" => $text,
        //         "csms_id" => date('dmYhhmi') . rand(10000, 99999)
        //     ];

        //     $url = env("SSL_SMS_URL");
        //     $params = json_encode($params);

        //     $ch = curl_init(); // Initialize cURL
        //     curl_setopt($ch, CURLOPT_URL, $url);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        //     curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        //     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //         'Content-Type: application/json',
        //         'Content-Length: ' . strlen($params),
        //         'accept:application/json'
        //     ));

        //     $response = curl_exec($ch);

        //     curl_close($ch);

        //     return $response;
        // } elseif (OtpConfiguration::where('type', 'fast2sms')->first()->value == 1) {

        //     if (strpos($to, '+91') !== false) {
        //         $to = substr($to, 3);
        //     }

        //     if (env("ROUTE") == 'dlt_manual') {
        //         $fields = array(
        //             "sender_id" => env("SENDER_ID"),
        //             "message" => $text,
        //             "template_id" => $template_id,
        //             "entity_id" => env("ENTITY_ID"),
        //             "language" => env("LANGUAGE"),
        //             "route" => env("ROUTE"),
        //             "numbers" => $to,
        //         );
        //     } else {
        //         $fields = array(
        //             "sender_id" => env("SENDER_ID"),
        //             "message" => $text,
        //             "language" => env("LANGUAGE"),
        //             "route" => env("ROUTE"),
        //             "numbers" => $to,
        //         );
        //     }


        //     $auth_key = env('AUTH_KEY');

        //     $curl = curl_init();

        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_SSL_VERIFYHOST => 0,
        //         CURLOPT_SSL_VERIFYPEER => 0,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "POST",
        //         CURLOPT_POSTFIELDS => json_encode($fields),
        //         CURLOPT_HTTPHEADER => array(
        //             "authorization: $auth_key",
        //             "accept: */*",
        //             "cache-control: no-cache",
        //             "content-type: application/json"
        //         ),
        //     ));

        //     $response = curl_exec($curl);
        //     $err = curl_error($curl);

        //     curl_close($curl);

        //     return $response;
        // } elseif (OtpConfiguration::where('type', 'mimo')->first()->value == 1) {
        //     $token = MimoUtility::getToken();

        //     MimoUtility::sendMessage($text, $to, $token);
        //     MimoUtility::logout($token);
        // }
        // elseif (OtpConfiguration::where('type', 'mimsms')->first()->value == 1) {
        //     $url = "https://esms.mimsms.com/smsapi";
        //       $data = [
        //         "api_key" => env('MIM_API_KEY'),
        //         "type" => "text",
        //         "contacts" => $to,
        //         "senderid" => env('MIM_SENDER_ID'),
        //         "msg" => $text,
        //       ];
        //       $ch = curl_init();
        //       curl_setopt($ch, CURLOPT_URL, $url);
        //       curl_setopt($ch, CURLOPT_POST, 1);
        //       curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //       $response = curl_exec($ch);
        //       curl_close($ch);
        //       return $response;
        // }
        // elseif (OtpConfiguration::where('type', 'msegat')->first()->value == 1) {
        //     $url = "https://www.msegat.com/gw/sendsms.php";
        //     $data = [
        //         "apiKey" => env('MSEGAT_API_KEY'),
        //         "numbers" => $to,
        //         "userName" => env('MSEGAT_USERNAME'),
        //         "userSender" => env('MSEGAT_USER_SENDER'),
        //         "msg" => $text
        //     ];
        //     $ch = curl_init();
        //     curl_setopt($ch, CURLOPT_URL, $url);
        //     curl_setopt($ch, CURLOPT_POST, 1);
        //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //     $response = curl_exec($ch);
        //     curl_close($ch);
        //     return $response;
        // }
        return true;
    }
}
