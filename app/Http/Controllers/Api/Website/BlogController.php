<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    //get all blogs
    public function index(Request $request)
    {
        try {
            $numberOfBlogssPerPage = (!$request->numberOfBlogssPerPage)? 6 : $request->numberOfBlogssPerPage;
            $blogs = Blog::with('category')->orderBy('id', 'desc')
                            ->paginate($numberOfBlogssPerPage);
            if (!$blogs) {
                return response()->json([
                    'status' => false,
                    'message' => 'No blogs found'
                ], 404);
            }
            else {
                $updatedBlogs = $this->transformBlogs($blogs);
                $pagination = [
                    'first_page' => $blogs->url(1),
                    'current_page' => $blogs->currentPage(),
                    'next_page' => $blogs->nextPageUrl(),
                    'prev_page' => $blogs->previousPageUrl(),
                    'links' => $blogs->getUrlRange(1, $blogs->lastPage()),
                    'last_page' => $blogs->url($blogs->lastPage()),
                    'per_page' => $blogs->perPage(),
                    'total_items' => $blogs->total(),
                ];
                return response()->json([
                    'status' => true,
                    'message' => 'Blogs found',
                    'blogs' => $updatedBlogs,
                    'pagination' => $pagination
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    //get blog by slug
    public function getBlog($slug)
    {
        try {
            $blog = Blog::where('slug', 'like', '%'.$slug.'%')->orderByDesc('created_at')->get();
            if (!$blog)
                return response()->json([
                    'status' => false,
                    'message' => 'No blog found'
                ], 404);
                else {
                    $updatedBlogs = $this->transformBlogs($blog);
                    return response()->json([
                        'status' => true,
                        'message' => 'Blogs found',
                        'blogs' => $updatedBlogs
                    ], 200);
                }
            } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    

    
    //get blogs by category
    public function getBlogsByCategory($category_id, Request $request)
    {
        try {
            $numberOfBlogssPerPage = (!$request->numberOfBlogssPerPage)? 6 : $request->numberOfBlogssPerPage;
            $blogs = Blog::where('category_id', $category_id)->orderBy('id', 'desc')
                            ->paginate($numberOfBlogssPerPage);
            if (!$blogs)
                return response()->json([
                    'status' => false,
                    'message' => 'No blogs found'
                ], 404);
                else {
                    $updatedBlogs = $this->transformBlogs($blogs);
                    $pagination = [
                        'first_page' => $blogs->url(1),
                        'current_page' => $blogs->currentPage(),
                        'next_page' => $blogs->nextPageUrl(),
                        'prev_page' => $blogs->previousPageUrl(),
                        'links' => $blogs->getUrlRange(1, $blogs->lastPage()),
                        'last_page' => $blogs->url($blogs->lastPage()),
                        'per_page' => $blogs->perPage(),
                        'total_items' => $blogs->total(),
                    ];
                    return response()->json([
                        'status' => true,
                        'message' => 'Blogs found',
                        'blogs' => $updatedBlogs,
                        'pagination' => $pagination
                    ], 200);
                }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    } 
        

    //get blogs by tag
    public function getBlogsByTag($id, Request $request)
    {
        try {
            $numberOfBlogssPerPage = (!$request->numberOfBlogssPerPage)? 6 : $request->numberOfBlogssPerPage;
            $blogs = Blog::where('tag_id', $id)->orderBy('id', 'desc')
            ->paginate($numberOfBlogssPerPage);
            if (!$blogs)
                return response()->json([
                    'status' => false,
                    'message' => 'No blogs found'
                ], 404);
                else {
                    $updatedBlogs = $this->transformBlogs($blogs);
                    $pagination = [
                        'first_page' => $blogs->url(1),
                        'current_page' => $blogs->currentPage(),
                        'next_page' => $blogs->nextPageUrl(),
                        'prev_page' => $blogs->previousPageUrl(),
                        'links' => $blogs->getUrlRange(1, $blogs->lastPage()),
                        'last_page' => $blogs->url($blogs->lastPage()),
                        'per_page' => $blogs->perPage(),
                        'total_items' => $blogs->total(),
                    ];
                    return response()->json([
                        'status' => true,
                        'message' => 'Blogs found',
                        'blogs' => $updatedBlogs,
                        'pagination' => $pagination
                    ], 200);
                }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    //get blogs by search
    public function getBlogsBySearch(Request $request)
    {
        try {
            $search = $request->s;
            $numberOfBlogssPerPage = (!$request->numberOfBlogssPerPage)? 6 : $request->numberOfBlogssPerPage;
            $blogs = Blog::where('slug', 'like', '%' . $search . '%')
                        ->orWhere('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orderBy('id', 'desc')->paginate($numberOfBlogssPerPage);
                        ;
            if (!$blogs)
                return response()->json([
                    'status' => false,
                    'message' => 'No blog found'
                ], 404);
                else {
                    $updatedBlogs = $this->transformBlogs($blogs);
                    $pagination = [
                        'first_page' => $blogs->url(1),
                        'current_page' => $blogs->currentPage(),
                        'next_page' => $blogs->nextPageUrl(),
                        'prev_page' => $blogs->previousPageUrl(),
                        'links' => $blogs->getUrlRange(1, $blogs->lastPage()),
                        'last_page' => $blogs->url($blogs->lastPage()),
                        'per_page' => $blogs->perPage(),
                        'total_items' => $blogs->total(),
                    ];
                    return response()->json([
                        'status' => true,
                        'message' => 'Blogs found',
                        'blogs' => $updatedBlogs,
                        'pagination' => $pagination
                    ], 200);
                }
        } catch (\Exception $e){
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    //get blogs by date
    public function getBlogsByDate($date, Request $request)
    {
        try {
            $numberOfBlogssPerPage = (!$request->numberOfBlogssPerPage)? 6 : $request->numberOfBlogssPerPage;
            $blogs = Blog::where('created_at', 'like', '%' . $date . '%')
                                ->orderBy('id', 'desc')
                                ->paginate($numberOfBlogssPerPage);
                                if (!$blogs)
                return response()->json([
                    'status' => false,
                    'message' => 'No blogs found'
                ], 404);
                else {
                    $updatedBlogs = $this->transformBlogs($blogs);
                    $pagination = [
                        'first_page' => $blogs->url(1),
                        'current_page' => $blogs->currentPage(),
                        'next_page' => $blogs->nextPageUrl(),
                        'prev_page' => $blogs->previousPageUrl(),
                        'links' => $blogs->getUrlRange(1, $blogs->lastPage()),
                        'last_page' => $blogs->url($blogs->lastPage()),
                        'per_page' => $blogs->perPage(),
                        'total_items' => $blogs->total(),
                    ];
                    return response()->json([
                        'status' => true,
                        'message' => 'Blogs found',
                        'blogs' => $updatedBlogs,
                        'pagination' => $pagination
                    ], 200);
                }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    //get blogs by author
    public function getBlogsByAuthor($id, Request $request)
    {
        try {
            $numberOfBlogssPerPage = (!$request->numberOfBlogssPerPage)? 6 : $request->numberOfBlogssPerPage;
            $blogs = Blog::where('author_id', $id)->orderBy('id', 'desc')
            ->paginate($numberOfBlogssPerPage);
            if (!$blogs)
                return response()->json([
                    'status' => false,
                    'message' => 'No blogs found'
                ], 404);
                else {
                    $updatedBlogs = $this->transformBlogs($blogs);
                    $pagination = [
                        'first_page' => $blogs->url(1),
                        'current_page' => $blogs->currentPage(),
                        'next_page' => $blogs->nextPageUrl(),
                        'prev_page' => $blogs->previousPageUrl(),
                        'links' => $blogs->getUrlRange(1, $blogs->lastPage()),
                        'last_page' => $blogs->url($blogs->lastPage()),
                        'per_page' => $blogs->perPage(),
                        'total_items' => $blogs->total(),
                    ];
                    return response()->json([
                        'status' => true,
                        'message' => 'Blogs found',
                        'blogs' => $updatedBlogs,
                        'pagination' => $pagination
                    ], 200);
                }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // get blogs categories
    public function getAllBlogsCategories()
    {
        try {
            $categories = BlogCategory::withCount('blogs')->all();
            if ($categories->isEmpty())
                return response()->json([
                    'status' => false,
                    'message' => 'No categories found'
                ], 404);
            else
                return response()->json([
                    'status' => true,
                    'message' => 'Categories found',
                    'categories' => $categories
                ], 200);
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    //get category and count of blogs that blongs to it
    public function countBlogsOfCategory()
    {
        try {
            $query = "select  `blog_categories`.id, `blog_categories`.category_name, (select count(*) from `blogs`
                where `blog_categories`.`id` = `blogs`.`category_id` and
                `blogs`.`deleted_at` is null) as `posts_count` from `blog_categories` 
                where `blog_categories`.`deleted_at` is null";

            $result = DB::select($query); // Execute the raw SQL query using DB facade

            if (!$result)
                return response()->json([
                    'status' => false,
                    'message' => 'No categories found'
                ], 404);
            else
                return response()->json([
                    'status' => true,
                    'message' => 'Categories found',
                    'categories' => $result
                ], 200);
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // get blog details by name or id
    public function getBlogDetails($value)
    {
        try {
            $blog = Blog::where('id', $value)->orWhere('title', 'like', "%$value%")->get();
            if ($blog->isEmpty())
                return response()->json([
                    'status' => false,
                    'message' => 'No blog found'
                ], 404);
            else {
                $updatedBlog = $this->transformBlogs($blog);
                return response()->json([
                    'status' => true,
                    'message' => 'Blog found',
                    'blog' => $updatedBlog
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // get 3 newest blogs
    public function getNewestBlogs()
    {
        try {
            $blogs = Blog::orderBy('id', 'desc')->limit(5)->get();
            if (!$blogs)
                return response()->json([
                    'status' => false,
                    'message' => 'No blogs found'
                ], 404);
            else {
                $updatedBlogs = $this->transformBlogs($blogs);
                return response()->json([
                    'status' => true,
                    'message' => 'Blogs found',
                    'blogs' => $updatedBlogs
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    private function transformBlogs($blogs): mixed
    {
        return $blogs->map(function ($blog) {
            // Get blog banner
            if (!$blog['banner'])
                $blog['banner'] = null;
            else{
                $bannerImage = Upload::find($blog['banner']);
                $blog['banner'] = asset("/$bannerImage->file_name");
            }
    
            // Get meta img
            if (!$blog['meta_img'])
                $blog['meta_img'] = null;
            else {
                $metaImage = Upload::find($blog['meta_img']);
                $blog['meta_img'] = asset("/$metaImage->file_name");
            }

            return $blog;
        })->toArray();
    }


}
