<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    protected $fillable = ['name', 'lang', 'category_id'];
    protected $hidden = ['id', 'category_id','created_at', 'updated_at'];


    // relationships
    public function category(){
    	return $this->belongsTo(Category::class);
    }
}
