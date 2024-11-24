<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdController extends Controller
{
    public function index()
    {
        try {
            $ads = Ad::where('status', 1)->orderByDesc('id')
                        ->where('end_date', '>', strtotime(now()))
                        ->get();
            if ($ads->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Ads Found',
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Ads found',
                'data' => $ads
            ]);
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get ad by type
    public function getAdByType(string $type)
    {
        try {
            $ads = Ad::where('status', 1)->where('type', $type)
                        ->where('end_date', '>=', strtotime(now()))
                        ->orderByDesc('id')->get();
            if ($ads->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Ads Found',
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Ads found',
                'data' => $ads
            ]);
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
