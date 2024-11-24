<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Language;
use App\Models\Wishlist;
use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HeaderController extends Controller
{
    public function language()
    {
        try {
            $lang = Language::select('id','name','code')->get();
            if (!$lang)
                return response()->json([
                    'status' => false,
                    'message' => 'No Language Found'
                ], 404);
            else {
                return response()->json([
                    'status' => true,
                    'message' => 'Language Found',
                    'languages' => $lang
                ]);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function cartCount()
    {
        try {
            $cartCount = Cart::where('user_id', Auth::id())->count();
            if (!$cartCount) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Cart Found'
                ], 404);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Cart Found',
                    'cartCount' => $cartCount
                ]);
            }
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function wishlistCount(Request $request)
    {
        try {
            if(Auth::check()){
                $wishlistCount = Wishlist::where('user_id', Auth::id())->count();
            }elseif($request->session()->get('temp_user_id')){
                $wishlistCount = Wishlist::where('temp_user_id', $request->session()->get('temp_user_id'))->count();
            }else{
                $wishlistCount = 0;
            }
            if ($wishlistCount > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Wishlist Found',
                    'wishlistCount' => $wishlistCount
                ], 200);
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'No Wishlist Found'
                    ],
                    404
                );
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
