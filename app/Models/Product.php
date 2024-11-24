<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'added_by',
        'user_id',
        'category_id',
        'brand_id',
        'video_provider',
        'video_link',
        'unit_price',
        'purchase_price',
        'unit',
        'slug',
        'colors',
        'choice_options',
        'variations',
        'thumbnail_img',
        'meta_title',
        'meta_description'
    ];

    protected $hidden = [
        'added_by',
        'user_id',
        'category_id',
        'brand_id',
        'created_at',
        'updated_at',
        'purchase_price',
        'unit',
        'choice_options',
        'variations',
        'meta_description',
        'variant_product',
        'attributes',
        'todays_deal',
        'published',
        'approved',
        'stock_visibility_state',
        'cash_on_delivery',
        'featured',
        "seller_featured",
        "current_stock",
        "min_qty",
        "low_stock_quantity",
        "discount",
        "discount_type",
        "discount_start_date",
        "discount_end_date",
        "tax",
        "tax_type",
        "shipping_type",
        "shipping_cost",
        "is_quantity_multiplied",
        "est_shipping_days",
        "num_of_sale",
        "earn_point",
        "refundable",
        "rating",
        "barcode",
        "digital",
        "auction_product",
        "file_name",
        "file_path",
        "external_link",
        "wholesale_product",
        "external_link_btn",
        "pdf",
        'photos'
    ]; 


    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? App::getLocale() : $lang;
        $product_translations = $this->product_translations->where('lang', $lang)->first();
        return $product_translations != null ? $product_translations->$field : $this->$field;
    }

    public function product_translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class)
                    ->select(['id', 'name', 'slug']);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class)
                    ->select(['id', 'name', 'slug', 'logo']);
    }

    public function user()
    {
        return $this->belongsTo(User::class)
                    ->select(['id', 'name', 'email', 'user_type']);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 1);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function taxes()
    {
        return $this->hasMany(ProductTax::class);
    }

    public function flash_deal_product()
    {
        return $this->hasOne(FlashDealProduct::class);
    }

    public function bids()
    {
        return $this->hasMany(AuctionProductBid::class);
    }

    public function scopeFilter(Builder $builder, $filter)
    {
        $options = array_merge([
            'search' => '',
            'category_id' => '',
            'tag' => '',
            'brand_id' => '',
            'sort_by' => 'latest',
            'flash_deal' => '',
            'featured' => '',
            'color' => '',
            'size' => [],
            'available' => '',
            'min_discount' => '',
            'max_discount' => ''
        ], $filter);


        // $builder->when($options['search'], function ($builder, $value) {
        //     $builder->where('slug', 'like', '%' . $value . '%')
        //             ->orWhere('name', 'like', '%' . $value . '%') 
        //             ->orWhereHas('product_translations', function ($query) use ($value) {
        //                 $query->where('name', 'like', '%' . $value . '%');
        //             });
        // });

        $builder->when($options['category_id'], function ($builder, $value) {
            $builder->where('category_id', $value);
        });

        $builder->when($options['brand_id'], function ($builder, $value) {
            $builder->where('brand_id', $value);
        });

        $builder->when($options['sort_by'], function ($builder, $value) use ($options) {
            switch ($value) {
                case 'latest':
                    $builder->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $builder->orderBy('created_at', 'asc');
                    break;
                case 'price_low_high':
                    $builder->orderBy('unit_price', 'asc');
                    break;
                case 'price_high_low':
                    $builder->orderBy('unit_price', 'desc');
                    break;
                case 'top_rating':
                    $builder->orderBy('rating', 'desc');
                    break;
                case 'top_selling':
                    $builder->orderBy('num_of_sale', 'desc');
                    break;
                case 'trending':
                    $builder->orderBy('num_of_sale', 'desc');
                    break;
            }
        });


        $builder->when($options['featured'], function ($builder, $value) {
            $builder->where('featured', $value);
        });

        $builder->when($options['tag'], function ($builder, string $value) {
            $builder->where('tags', 'like', '%' . $value . '%');

            // $builder->whereRaw('JSON_CONTAINS(tags, \'["' . implode('","', $value) . '"]\')');
            // $builder->whereRaw('id IN (SELECT product_id FROM product_tag WHERE tag_id = ?)', $value);
            // $builder->whereExists(function ($query) use ($value) {
            //     $query->select(1)
            //         ->from('product_tag')
            //         ->whereRaw('product_tag.product_id', 'products.id')
            //         ->where('product_tag.tag_id', $value);
            // });
        });

        // $builder->when($options['flash_deal'], function ($builder, $value) {
        //     $builder->where('flash_deal', $value);
        // });

        // $builder->when($options['min_discount'], function ($builder, $value) {
        //     $builder->where('discount', '>=', $value);
        // });

        // $builder->when($options['max_discount'], function ($builder, $value) {
        //     $builder->where('discount', '<=', $value);
        // });

        $builder->when($options['available'], function ($builder, $value) {
            $builder->where('current_stock', '>=', $value);
        });

        $builder->when($options['color'], function ($builder, string $value) {
            $hexCode = Color::getHex($value);
            $builder->where('colors', 'like', '%' . $hexCode . '%');
        });

        // $builder->when($options['size'], function ($builder, $value) {
        //     $builder->whereRaw('JSON_CONTAINS(sizes, \'["' . implode('","', $value) . '"]\')');
        // });

    }


}
