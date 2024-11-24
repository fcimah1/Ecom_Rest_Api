<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Upload;
use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allBrands()
    {
        try {
            $brands = Brand::withCount('products')->orderByDesc('created_at')->limit(9)->get();
            if ($brands) {
                $updatedbrands = $this->transformBrand($brands);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Brands found',
                    'brands' => $updatedbrands
                ]);
            } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Brands not found',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => 'false',
                'message' => $e->getMessage(),
            ]);
        }
    }


        // get featured Brands
    public function getFeaturedBrands()
    {
        try {
            // Fetch featured Brands with image, product count, name, and URL filter
            $brands = Brand::withCount('products')
                ->where('top', true) // Assuming you have a field 'is_featured' to identify featured Brands
                ->limit(9)->get();
            if (!$brands) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Featured Brands Found'
                ], 404);
            }
            $updatedBrands = $this->transformBrand($brands);
            $result = [];
            foreach ($updatedBrands as $brand) {
                $result[] = [
                    'name' => $brand['name'],
                    'logo_link' => $brand['logo'],
                    'products_count' => $brand['products_count'],
                    'filter_url' => route('products', ['brand_id' => $brand['id']]), // Assuming you have a 'slug' field
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Featured Brands Found',
                'Brands' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error("error message " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }

    }
    

    public function limitBrand_3()
    {
        try {
            $limitedbrands = Brand::orderByDesc('created_at')->limit(3)->get();

            if ($limitedbrands) {
                $updatedbrands = $this->transformBrand($limitedbrands);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Brands found',
                    'brands' => $updatedbrands
                ], 200);
            } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Brands not found',
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => 'false',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function limitBrand_4()
    {
        try {
            $limitedbrands = Brand::orderByDesc('created_at')->limit(4)->get();

            if ($limitedbrands) {
                $updatedbrands = $this->transformBrand($limitedbrands);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Brands found',
                    'brands' => $updatedbrands
                ], 200);
            } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Brands not found',
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => 'false',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function limitBrand()
    {
        try {
            $limitedbrands = Brand::orderByDesc('created_at')->limit(4)->get();
            if ($limitedbrands) {
                $updatedbrands = $this->transformBrand($limitedbrands);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Brands found',
                    'brands' => $updatedbrands
                ], 200);
            } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Brands not found',
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => 'false',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function transformBrand($brands): mixed
    {
        return $brands->map(function ($brand) {
            // Get brand logo image
            if (!$brand['logo'])
                $brand['logo'] = null;
            else{
                $logoImage = Upload::find($brand['logo']);
                $brand['logo'] = asset("/$logoImage->file_name");
            }
            return $brand;
        })->toArray();
    }

}
