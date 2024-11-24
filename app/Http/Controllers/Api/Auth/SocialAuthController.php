<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Session;
use Socialite;

class SocialAuthController extends Controller
{
        // login by social media
    public function redirectToProvider($provider)
    {
        dd(vars: $provider);
        if ($provider == 'twitter') {
            return Socialite::driver('twitter')->redirect();
        } else {
            return Socialite::driver($provider)->stateless()->redirect();

        }
    }
    
        public function handleProviderCallback($provider)
        {
            try {
                if ($provider == 'twitter') {
                    $user = Socialite::driver('twitter')->user();
                } else {
                    $user = Socialite::driver($provider)->stateless()->user();
                }
                $user = User::where('provider_id', $user->id)->first();
    
                if ($user){
                    Auth::login($user);

                    return $this->createNewToken(Auth::login($user));
                }else{
                    $newUser = User::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'provider_id' => $user->id,
                    ]);
                    Auth::login($newUser);
                    return $this->createNewToken(Auth::login($user));
                }
                
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong '.$e->getMessage(),
                ], 500);
            }
        }

        public function createNewToken($token)
        {
            return response()->json([
                'status' => true,
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => Auth::user(),
            ]);
        }
    
}
