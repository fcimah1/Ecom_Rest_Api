<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    // function to get the google map latutude and longitude
    public function googleMap()
    {
        try {
            $latutude = '30.0131';
            $longitude = '31.2357';
            return response()->json([
                'status' => true,
                'latutude' => $latutude,
                'longitude' => $longitude,
            ], 200);
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // function to get the contact information
    public function contactInfo()
    {
        try {
            $contact = [
                'Egypt' => [
                    'email' => 'info@myelviraeg.com',
                    'phone' => '0100 500 9829',
                    'address' => 'Nakhil Complex Building (213) Cairo Egypt',
                ],
                'United Arab Emirates' => [
                    'email' => 'info@myelviraeg.com',
                    'phone' => '',
                    'address' => 'Compass Building,Al Shohada Road AL Hamra Industrial Zone-FZ, RAKEZ Ras Al Khaimah',
                ]
            ];
            $social = [
                'facebook' => 'https://www.facebook.com/mywebsite',
                'twitter' => 'https://www.twitter.com/mywebsite',
                'instagram' => 'https://www.instagram.com/mywebsite',
                'linkedin' => 'https://www.linkedin.com/mywebsite',
            ];

            return response()->json([
                'status' => true,
                'contact' => $contact,
                'social' => $social,
            ], 200);
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // function to get the contact form data
    public function contactForm(ContactRequest $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'message' => 'required|string|max:255',
            ]);
            // send email to the admin
            $to = 'eng.mohamedahmed2741991@gmail.com';
            $subject = 'Contact Form';
            $body = 'Name: ' . $data['name'] . '<br> Email: ' . $data['email'] . '<br> Message: ' . $data['message'];
            $checkMail = mail($to, $subject, $body);
            if (!$checkMail) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong, please try again later.'
                ], 500);
            }
            // send contacts in database
            Contact::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'message' => $data['message'],
            ]);

            // create email to the user
            $user_email = $request->email;
            $user_subject = 'Thank you for contacting us';
            $user_body = 'Thank you for contacting us, we will get back to you soon.';
            mail($user_email, $user_subject, $user_body);

            return response()->json([
                'status' => true,
                'message' => 'Thank you for contacting us, we will get back to you soon.'
            ], 200);
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}