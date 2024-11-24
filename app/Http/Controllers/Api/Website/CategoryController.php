<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Upload;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allCategories()
    {
        try {
            $categories = Category::withCount(relations: 'products')->orderByDesc('created_at')->get();
            if (!$categories) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Categories Found'
                ], 404);
            }

            $updatedCategories = $this->transformCategory($categories);
            return response()->json([
                'status' => true,
                'message' => 'Categories Found',
                'categories' => $updatedCategories
            ], 200);
        } catch (\Exception $e) {
            Log::error("error message " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // get featured categories
    public function getFeaturedCategories()
    {
        try{
        // Fetch featured categories with image, product count, name, and URL filter
            $categories = Category::withCount('products')
                ->where('featured', true) // Assuming you have a field 'is_featured' to identify featured categories
                ->limit(6)->get();
            $updatedCategories = $this->transformCategory($categories);
            $result = [];
                foreach ($updatedCategories as $category) {
                    // dd($category);
                    $result[] = [
                        'name' => $category['name'],
                        'image_banner_link' => $category['banner'],
                        'image_icon_link' => $category['icon'],
                        'products_count' => $category['products_count'],
                        'filter_url' => route('products', ['category_id' => $category['id']]), // Assuming you have a 'slug' field
                    ];
                }
            if (!$categories) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Featured Categories Found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Featured Categories Found',
                'categories' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error("error message " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }

    }

    public function LimitCategories_3()
    {
        try {
            $categories = Category::orderByDesc('created_at')->limit(3)->get();
            if (!$categories) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Categories Found'
                ], 404);
            }

            $updatedCategories = $this->transformCategory($categories);
            return response()->json([
                'status' => true,
                'message' => 'Categories Found',
                'categories' => $updatedCategories
            ], 200);
        } catch (\Exception $e) {
            Log::error("error message " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function LimitCategories()
    {
        try {
            $categories = Category::limit(4)->get();
            if (!$categories)
                return response()->json([
                    'status' => false,
                    'message' => 'No Categories Found'
                ], 404);

                $updatedCategories = $this->transformCategory($categories);
                return response()->json([
                    'status' => true,
                    'message' => 'Categories Found',
                    'categories' => $updatedCategories
                ], 200);
            
        } catch (\Exception $e) {
            Log::error("error message ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function transformCategory($categories): mixed
    {
        return $categories->map(function ($category) {
            // Get category banner image
            if (!$category['banner'])
                $category['banner'] = null;
            else{
                $bannerImage = Upload::find($category['banner']);
                $category['banner'] = asset("/$bannerImage->file_name");
    
            }
            // Get category icon image
            if (!$category['icon'])
                $category['icon'] = null;
            else{
                $iconImage = Upload::find($category['icon']);
                $category['icon'] = asset("/$iconImage->file_name");
            }

            return $category;
        })->toArray();
    }
}
