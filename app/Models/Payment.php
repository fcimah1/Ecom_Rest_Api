<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_id',
        'amount',
        'payment_method',
        'payment_details',
        'seller_id',
        'txn_code',
    ];
}
