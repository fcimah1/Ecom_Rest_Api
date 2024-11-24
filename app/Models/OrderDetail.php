<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
      
    // $order_detail->order_id = $order->id;
    // $order_detail->seller_id = $products->user_id;
    // $order_detail->product_id = $products->id;
    // $order_detail->variation = $cartItem['variation'];
    // $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
    // $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
    // $order_detail->shipping_type = $cartItem['shipping_type'];
    // $order_detail->product_referral_code = $cartItem['product_referral_code'];
    // $order_detail->shipping_cost = $cartItem['shipping_cost'];
    // $order_detail->payment_status = $order['payment_status'];
    // $order_detail->shipping_type = $order['payment_type'];
    // $order_detail->delivery_status = $order['delivery_status'];
    // $order_detail->quantity = $cartItem['quantity'];
     
    protected $fillable = [
        'order_id',
        'seller_id',
        'product_id',
        'quantity',
        'price',
        'delivery_status',
        'variation',
        'shipping_type',
        'product_referral_code',
        'shipping_cost',
        'tax',
        'payment_status',
        'shipping_type'
    ];

        protected $table = 'order_details';
    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function refund_request()
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }
}
