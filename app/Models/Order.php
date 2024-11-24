<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        "user_id",
        "combined_order_id",
        "seller_id",
        "guest_id",
        "payment_method",
        "payment_status",
    ];
    protected $hidden = [
        "combined_order_id",
        "user_id",
        "guest_id",
        "seller_id",
        "payment_details",
        "coupon_discount",
        "date",
        "viewed",
        "delivery_viewed",
        "payment_status_viewed",
        "commission_calculated",
        "created_at",
        "updated_at"
    ];
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->select(['name', 'email']);
    }

    public function seller()
    {
        return $this->hasOne(Shop::class, 'user_id', 'seller_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function club_point()
    {
        return $this->hasMany(ClubPoint::class);
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'assign_delivery_boy', 'id');
    }

    public function proxy_cart_reference_id()
    {
        return $this->hasMany(ProxyPayment::class)->select('reference_id');
    }
}
