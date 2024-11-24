<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function userProfile()
    {
        try {
            $user = User::findOrFail(Auth::id());
            if ($user) {
                return response()->json([
                    'status' => true,
                    'message' => 'User details',
                    'data' => [
                        'email' => $user->email,
                        'name' => $user->name,
                        'id' => $user->id
                    ],
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = User::findOrFail(Auth::id());
            if ($user) {
                $data = $request->validate([
                    'name' => 'required|string',
                    'phone' => 'required',
                    'password' => 'required|password',
                    'confirmed_password' => 'required|same:password',
                ]);
                $updatedUser = $user->update([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => bcrypt($data['password']),
                ]);
                if ($updatedUser) {
                    return response()->json([
                        'status' => true,
                        'message' => 'User details updated',
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'User details not updated',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyMail(Request $request)
    {
        // $user = User::findOrFail(Auth::id());
        // if ($user) {
        //     $user->sendEmailVerificationNotification();
        //     return response()->json([
        //         'status' => true,
        //         'message' => 'Verification mail sent',
        //     ]);
        // }
    }

    public function updateEmail(Request $request)
    {
        try {
            $user = User::findOrFail(Auth::id());
            if ($user) {
                $data = $request->validate([
                    'email' => 'required|email|unique:users,email,' . $user->id,
                ]);
                $updatedUser = $user->update([
                    'email' => $data['email'],
                ]);
                if ($updatedUser) {
                    return response()->json([
                        'status' => true,
                        'message' => 'User details updated',
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'User details not updated',
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
