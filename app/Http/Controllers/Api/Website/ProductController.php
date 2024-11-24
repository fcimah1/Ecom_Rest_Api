<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    // get all products 

    public function index(Request $request)
    {
        try {
            $numberOfProductsPerPage = (!$request->numberOfProductsPerPage)? 15 : $request->numberOfProductsPerPage;
            $products = Product::filter($request->query())
                ->with("category", "brand", "product_translations", "user", "stocks", "taxes")
                ->orderBy('id', direction: 'desc')
                ->paginate($numberOfProductsPerPage);
        
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }
    
            $updateProduct = $this->transformProducts($products);
    
               // Prepare pagination data
            $pagination = [
                'first_page' => $products->url(1),
                'current_page' => $products->currentPage(),
                'next_page' => $products->nextPageUrl(),
                'prev_page' => $products->previousPageUrl(),
                'links' => $products->getUrlRange(1,$products->lastPage()),
                'last_page' => $products->url($products->lastPage()),
                'per_page' => $products->perPage(),
                'total_items' => $products->total(),
            ];

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
                'pagination' => $pagination, // Include pagination information
            ], 200);
    
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);
    
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get featured products
    public function getFeaturedProducts()
    {
        try {
            $products = Product::with("category", "brand", "product_translations", "user", "stocks", "taxes")
                ->where('featured', true)
                ->orderBy('id', 'desc')->limit(9)->get();
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }

            $updateProduct = $this->transformProducts($products);

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
            ], 200);

        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get 3 newest products
    public function getLatestProducts()
    {
        try {
            $products = Product::with("category", "brand", "product_translations", "user", "stocks", "taxes")
                ->orderBy('id', 'desc')->limit(3)->get();
            if (!$products) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }

            $updateProduct = $this->transformProducts($products);

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
            ], 200);

        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get hot offer products
    public function getHotOfferProducts(){
        try {
            $products = Product::with("category", "brand", "product_translations", "user", "stocks", "taxes")
                ->orderBy('discount', 'desc')
                ->where('discount', '>', 0)
                ->where('discount_end_date', '>', strtotime(Carbon::now()))
                ->limit(3)->get();
            if ($products->isEmpty())
            {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }

            $updateProduct = $this->transformProducts($products);

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
            ], 200);

        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get best selling products
    public function getBestSellingProducts(){
        try {
            $products = Product::with("category", "brand", "product_translations", "user", "stocks", "taxes")
                ->orderBy('num_of_sale', 'desc')->limit(3)->get();
            if (!$products)
            {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }

            $updateProduct = $this->transformProducts($products);

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
            ], 200);

        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    // search products
    public function searchProducts(Request $request)
    {
        try {
            $searchTerm = $request->s;
            $numberOfProductsPerPage = (!$request->numberOfProductsPerPage)? 15 : $request->numberOfProductsPerPage;
            $products = Product::where('name', 'like', "%$searchTerm%")
                ->with("category", "brand", "product_translations", "user", "stocks", "taxes")
                ->orderBy('id', 'desc')->paginate($numberOfProductsPerPage);
            if (!$products) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }

            $updateProduct = $this->transformProducts($products);

            // Prepare pagination data
            $pagination = [
                'first_page' => $products->url(1),
                'current_page' => $products->currentPage(),
                'next_page' => $products->nextPageUrl(),
                'prev_page' => $products->previousPageUrl(),
                'links' => $products->getUrlRange($products->firstItem(), $products->lastPage()),
                'last_page' => $products->url($products->lastPage()),
                'per_page' => $products->perPage(),
                'total_items' => $products->total(),
            ];

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
                'pagination' => $pagination, // Include pagination information
            ], 200);

        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get products by category
    public function getProductsByCategory($categ, Request $request)
    {
        try {
            $category = Category::where('name', $categ)
                                ->orWhere('id', $categ)->first();

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found.',
                ], 404);
            }
            $numberOfProductsPerPage = (!$request->numberOfProductsPerPage)? 15 : $request->numberOfProductsPerPage;
            $products = Product::where('category_id', $category->id)
                    ->with("category", "brand", "product_translations", "user", "stocks", "taxes")
                    ->orderByDesc('id')->paginate($numberOfProductsPerPage);
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }
    
            $updateProduct = $this->transformProducts($products);
            // Prepare pagination data
            $pagination = [
                    'first_page' => $products->url(1),
                    'current_page' => $products->currentPage(),
                    'next_page' => $products->nextPageUrl(),
                    'prev_page' => $products->previousPageUrl(),
                    'links' => $products->getUrlRange($products->firstItem(), $products->lastPage()),
                    'last_page' => $products->url($products->lastPage()),
                    'per_page' => $products->perPage(),
                    'total_items' => $products->total(),
                ];
            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
                'pagination' => $pagination, // Include pagination information
            ], 200);
            } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    // get products by brand
    public function getProductsByBrand($brand, Request $request)
    {
        try {
            $brand = Brand::where('name', $brand)->orWhere('id', $brand)->first();
            if (!$brand) {
                return response()->json([
                    'status' => false,
                    'message' => 'Brand not found.',
                ], 404);
            }
            
            $numberOfProductsPerPage = (!$request->numberOfProductsPerPage)? 15 : $request->numberOfProductsPerPage;
            $products = Product::where('brand_id', $brand->id)
                    ->with("category", "brand", "product_translations", "user", "stocks", "taxes")
                    ->orderByDesc('id')->paginate($numberOfProductsPerPage);
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }
    
            $updateProduct = $this->transformProducts($products);
            // Prepare pagination data
            $pagination = [
                    'first_page' => $products->url(1),
                    'current_page' => $products->currentPage(),
                    'next_page' => $products->nextPageUrl(),
                    'prev_page' => $products->previousPageUrl(),
                    'links' => $products->getUrlRange($products->firstItem(), $products->lastPage()),
                    'last_page' => $products->url($products->lastPage()),
                    'per_page' => $products->perPage(),
                    'total_items' => $products->total(),
            ];
            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
                'pagination' => $pagination, // Include pagination information
            ], 200);
            } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
  
  // sort products by price, newest, oldest
    public function sortBy(Request $request)
    {
        try {
            $products = Product::query();
            $case = '';
            switch ($request->s) {
                case 'price-asc':
                    $products->orderBy('unit_price');
                    $case = 'price-asc';
                    break;
                case 'price-desc':
                    $products->orderBy('unit_price', 'desc');
                    $case = 'price-desc';
                    break;
                case 'newest':
                    $products->orderBy('created_at', 'desc');
                    $case = 'newest';
                    break;
                case 'oldest':
                    $products->orderBy('created_at');
                    $case = 'oldest';
                    break;
                default:
                    return response()->json([
                        'status' => false,
                        'error' => 'Invalid sort option.'
                    ], 400);
            }
            $numberOfProductsPerPage = (!$request->numberOfProductsPerPage)? 15 : $request->numberOfProductsPerPage;
            $products = $products->with("category", "brand", "product_translations", "user", "stocks", "taxes")
                    ->orderByDesc('id')->paginate($numberOfProductsPerPage);

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }
            $updateProduct = $this->transformProducts($products);
    
            // Prepare pagination data
            $pagination = [
                'first_page' => $products->url(1),
                'current_page' => $products->currentPage(),
                'next_page' => $products->nextPageUrl(),
                'prev_page' => $products->previousPageUrl(),
                'links' => $products->getUrlRange($products->firstItem(), $products->lastPage()),
                'last_page' => $products->url($products->lastPage()),
                'per_page' => $products->perPage(),
                'total_items' => $products->total(),
            ];

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
                'pagination' => $pagination, // Include pagination information
            ], 200);
    
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // count products by category
    public function countProductsByCategory()
    {
        try {
            $categories = Category::all();

            $productsCount = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'category' => $category->name,
                    'count of products' => $category->products->count(),
                ];
            });
            return response()->json([
                'status' => true,
                'message' => 'Products counted successfully.',
                'count result' => $productsCount,
            ]);
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get count of products by brand
    public function countProductsByBrand()
    {
       try {
            $brands = Brand::all();

            $productsCount = $brands->map(function ($brand) {
                return [
                    'brand' => $brand->name,
                    'count of products' =>Product::where('brand_id',$brand->id)->count(),
                ];
            });
            return response()->json([
                'status' => true,
                'message' => 'Products counted successfully.',
                'count result' => $productsCount,
            ]);
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        } 
    }

    //get popular products with selected product in product details page
    public function productPopular($category_id)
    {
        try {
            $products = Product::where('category_id', $category_id)
                    ->where('featured', true)
                    ->with("category", "brand", "product_translations", "user", "stocks", "taxes")
                    ->limit(9)
                    ->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }
    
            $updateProduct = $this->transformProducts($products);

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
            ], 200);
    
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get product by its name or id
    public function getProductByName($value)
    {
        try {
            $product = Product::where('name', $value)
                    ->orwhere('id', $value)
                    ->with("category", "brand", "product_translations", "user", "stocks", "taxes")
                    ->get();
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'No product found.',
                ], 404);
            }
    
            $updateProduct = $this->transformProducts($product);

            return response()->json([
                'status' => true,
                'message' => 'Product retrieved successfully.',
                'product' => $updateProduct,
            ], 200);
    
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get product by id

    public function getProductById(string $id)
    {
        try {
            $product = Product::where('id', $id)
                    ->with("category", "brand", "product_translations", "user", "stocks", "taxes")
                    ->get();
            if ($product->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No product found.',
                ], 404);
            }

            $updateProduct = $this->transformProducts($product);

            return response()->json([
                'status' => true,
                'message' => 'Product retrieved successfully.',
                'product' => $updateProduct,
            ], 200);

        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function newestProduct()
    {
        try {
            $products = Product::orderBy('id', 'desc')
                ->with("category", "brand", "product_translations", "user", "stocks", "taxes")
                ->limit(9)
                ->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found.',
                ], 404);
            }
        
            $updateProduct = $this->transformProducts($products);
    
            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'products' => $updateProduct,
            ], 200);
        } catch (\Exception $e) {
            Log::error("error message: " . $e->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function announcement()
    {
        try {
            $announcements = Product::where('discount', '>', 0)->where('discount_end_date', '>', strtotime(now()))->get();
            if ($announcements->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No announcements found.',
                ], 404);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Announcements retrieved successfully.',
                    'announcements' => $announcements,
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


    private function transformProducts($products): mixed
    {
        return $products->map(function ($product) {
            // Get product colors
            $colors = json_decode($product['colors']);
            $colorsNames = Color::whereIn('code', $colors)->pluck('name')->toArray();
            $product['colors'] = $colorsNames;
    
            // Get product thumbnail image
            if (!$product['thumbnail_img'])
                $product['thumbnail_img'] = null;
            else{
                $thumbnailImage = Upload::find($product['thumbnail_img']);
                $product['thumbnail_img'] = asset("/$thumbnailImage->file_name");
            }
    
            // Get meta img
            if (!$product['meta_img'])
                $product['meta_img'] = null;
            else {
                $metaImage = Upload::find($product['meta_img']);
                $product['meta_img'] = asset("/$metaImage->file_name");
            }
            
            // Get product images
            $photoIds = explode(',', $product['photos']);
            $urls = Upload::whereIn('id', $photoIds)->pluck('file_name')->toArray();
            $product['images'] = array_map(fn($fileName) => asset("/$fileName"), $urls);
            
            return $product;
        })->toArray();
    }
}
