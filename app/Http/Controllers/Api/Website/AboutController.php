<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AboutController extends Controller
{
    public function about()
    {
        try{
            $about = Page::where('slug', 'aboutUs')->first();
            if($about->isEmpty()){
                return response()->json([
                    'status' => false, 
                    'message' => 'About not found'
                ],404);
            }else{
                return response()->json([
                    'status' => true, 
                    'data' => $about
                ], 200);
            }
        }catch(\Exception $e){
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);
            return response()->json([
                'status' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
}
