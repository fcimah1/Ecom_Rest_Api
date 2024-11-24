<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FooterController extends Controller
{
    public function policyLinks()
    {
        try {
            $pages = Page::where('slug', 'like', '%policy%')->get();
            if ($pages->isEmpty()) {
            } else {
                $result = $pages->map(function ($page) {
                    return [
                        'title' => $page->title,
                        'slug' => $page->slug,
                        'url' => $page->url,
                    ];
                });

                return response()->json([
                    'status' => true,
                    'data' => $result
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function profileLinks()
    {
        try {
            $pages = Page::where('slug', 'like', '%profile%')
                ->where('slug', '=', '%account%')
                ->where('slug', '=', '%account%')
                ->where('slug', '=', '%account%')->get();
            if ($pages->isEmpty()) {
            } else {
                $result = $pages->map(function ($page) {
                    return [
                        'title' => $page->title,
                        'slug' => $page->slug,
                        'url' => $page->url,
                    ];
                });

                return response()->json([
                    'status' => true,
                    'data' => $result
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function socialLinks()
    {
        try {
            $socials = Page::where('slug', 'like', '%social%')->get();
            if ($socials->isEmpty()) {
            } else {
                $result = $socials->map(function ($social) {
                    return [
                        'title' => $social->title,
                        'url' => $social->url,
                        'icon' => $social->icon,
                    ];
                });

                return response()->json([
                    'status' => true,
                    'data' => $result
                ], 200);
            }

        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function subscribe(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email'
            ]);
            $subscribe = Subscriber::where('email', $data['email'])->first();
            if ($subscribe) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'You are already subscribed to our newsletter.'
                    ],200);
            } else {
                Subscriber::create($data);
                return response()->json([
                    'status' => true,
                    'message' => 'Thank you for subscribing to our newsletter.'
                ], 200);
            }
        }catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


}