<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $table = 'taxes';
    protected $fillable = ['tax_id', 'tax', 'tax_type', 'product_id'];

    // Relationship
    public function product_taxes() {
        return $this->hasMany(ProductTax::class);
    }
    public function products() {
        return $this->belongsToMany(Product::class, 'product_taxes');
    }
    
}
