<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CombinedOrder extends Model
{
    protected $fillable = [
        'shipping_address',
        'grand_total',
        'user_id',
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $with = ['address'];

    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function orders(){
    	return $this->hasMany(Order::class);
    }
}
