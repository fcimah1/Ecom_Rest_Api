<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            if (isset(request()->email)) {
                $credentials = request(['email', 'password']);
                $message = 'email and password does not match';
            } else if (isset(request()->phone)) {
                $credentials = request(['phone', 'password']);
                $message = 'phone and password does not match';
            }

            if (!$token = Auth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => $message,
                ], 401);
            }

            return $this->createNewToken($token);

        } catch (Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|between:2,100',
                'email' => 'required|string|email|max:100|unique:users',
                'phone' => 'required|string|between:2,100|unique:users',
                'password' => 'required|string|min:6',
            ]);

            // $user = User::create();
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    $validator->errors()
                ], 400);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
            ]);
            if ($user) {
                return $this->createNewToken(Auth::attempt([
                    'email' => $request->email,
                    'password' => $request->password,
                ]));
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong',
                ], 500);
            }
        } catch (Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([

            ]);
        } catch (Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        try{
        if(!Auth::user()){
            return response()->json([
                'status'=>false,
                'message'=>'Unauthorized'
            ], 401);
        }else{
            return response()->json([
                'status' => true,
                'message' => 'User Profile',
                'user' => Auth::user()
            ], 200);
        }
        } catch (Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try{
        Auth::logout();

        return response()->json([
            'status'=> true,
            'message' => 'Successfully logged out'
        ],200);
        } catch (Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try{
        return response()->json([
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]); 
        } catch (Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => Auth::user()
        ], 200);
    }

}
