<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Log;

class CustomPageController extends Controller
{
    public function getCustomPage($slug)
    {
        try {
            $pageContent = Page::where('slug', 'like' , '%'.$slug.'%')->first();
            if ($pageContent) {
                return response()->json([
                    'status' => true,
                    'message' => 'Page found',
                    'data' => $pageContent
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Page not found'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
