<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTax extends Model
{
    protected $fillable = [
        'product_id',
        'tax_id',
        'tax_type',
        'tax'
    ];
    protected $hidden = [
        'product_id',
        'tax_id',
        'created_at',
        'updated_at',
    ];
}
