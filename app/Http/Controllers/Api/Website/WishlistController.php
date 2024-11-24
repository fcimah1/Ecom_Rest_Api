<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\TempGuest;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class WishlistController extends Controller
{
    protected $tempGuest;

    public function __construct(TempGuest $tempGuest)
    {
        $this->tempGuest = $tempGuest;
    }
    public function getWishlist(Request $request)
    {
        try {
            if (Auth::check()) {
                $user_id = Auth::id();

                $temp_user_id = $request->temp_user_id ?? $request->temp_user_id;
                if ($temp_user_id != null) {
                    Wishlist::where('temp_user_id', $temp_user_id)
                        ->update([
                            'user_id' => $user_id,
                            'temp_user_id' => null
                        ]);
                    // session()->forget('temp_user_id');
                }

                $wishlists = Wishlist::where('user_id', $user_id)->get();
            } else {
                $wishlists = $request->temp_user_id != null ? Wishlist::where('temp_user_id', $request->temp_user_id)->get() : [];
            }

            if ($wishlists) { // More readable than count() > 0
                return response()->json([
                    'status' => true,
                    'message' => 'Wishlist fetched successfully',
                    'data' => $wishlists,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No wishlist found'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("Error message: {$e->getMessage()}, In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'An error occurred while fetching the wishlist.' . $e->getMessage(),
            ], 500);
        }
    }


public function addWishlist(Request $request)
{
    try {
        // Initialize data array
        $data = [];

        // Determine user authentication and set user_id/temp_user_id
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $data['temp_user_id'] = null;
            $wishlists = Wishlist::where('user_id', $data['user_id'])->get();
        } else {
            $temp_user_id = $this->getOrCreateTempUserId($request->temp_user_id);
            $data['user_id'] = null;
            $data['temp_user_id'] = $temp_user_id;
            $wishlists = Wishlist::where('temp_user_id', $temp_user_id)->get();
        }

        // Check if product already exists in the wishlist
        if ($this->productExistsInWishlist($wishlists, $request->product_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Product already exists in wishlist.'
            ]);
        } 
        
        // Add product to wishlist
        $data['product_id'] = $request->product_id;
        Wishlist::create([
            'user_id' => $data['user_id'],
            'temp_user_id' => $data['temp_user_id'],
            'product_id' => $data['product_id'],
        ]);
        
        // Fetch the updated wishlist count
        $wishlistsCount = $this->getWishlistCount($data);

        return response()->json([
            'status' => true,
            'message' => 'Product added to wishlist successfully.',
            'wishlist_count' => $wishlistsCount,
            ''
        ], 200);
        
    } catch (\Exception $e) {
        Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

        return response()->json([
            'status' => false,
            'message' => 'Error in adding to wishlist: ' . $e->getMessage(),
        ], 500);
    }
}

private function getOrCreateTempUserId($temp_user_id)
{
    if ($temp_user_id) {
        $temp_user = TempGuest::where('temp_user_id', $temp_user_id)->first();
        return $temp_user ? $temp_user->temp_user_id : $this->createNewTempUserId();
    }

    return $this->createNewTempUserId();
}

private function createNewTempUserId()
{
    $temp_user_id = bin2hex(random_bytes(10));
    $expires_at = now()->addDay();
    TempGuest::create([
        'temp_user_id' => $temp_user_id,
        'expires_at' => now()->addDay()->toDateTimeString(),
    ]);
    return $temp_user_id;
}

private function productExistsInWishlist($wishlists, $product_id)
{
    return $wishlists->contains('product_id', $product_id);
}

private function getWishlistCount($data)
{
    return Wishlist::where('user_id', $data['user_id'])
                   ->orWhere('temp_user_id', $data['temp_user_id'])
                   ->count();
}

public function removeWishlist($id, Request $request)
    {
        try {
            Wishlist::destroy($id);
            if (Auth::user() != null) {
                $user_id = Auth::user()->id;
                $wishlists = Wishlist::where('user_id', $user_id)->get();
            } else {
                $request->temp_user_id ? $wishlists = Wishlist::where('temp_user_id', $request->temp_user_id)->get() : null;
            }
            return response()->json([
                'status' => true,
                'message' => 'Product removed from wishlists successfully',
                'wishlist_count' => count($wishlists),
            ])->setStatusCode(200);

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
            $wishlists = null;
            if (Auth::check()) {
                $user_id = Auth::id();
                $wishlists = Wishlist::where('user_id', $user_id)->get();
            } else {
                ($request->temp_user_id != null) ? $wishlists = Wishlist::where('temp_user_id', $request->temp_user_id)->get() : null;
            }
            if ($wishlists && count($wishlists) > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Wishlist count',
                    'wishlist_count' => count($wishlists),
                ], 200);
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'No products in wishlist',
                ], 401);
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
