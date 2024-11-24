<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Brand extends Model
{

  protected $with = ['brand_translations'];

  protected $fillable = [
        'name',
        'logo',
        'top',
        'meta_title',
        'meta_description',
        'slug'
    ];

  protected $hidden = [
        'created_at',
        'updated_at',  
        'top',
        'meta_title',
        'meta_description',
    ];

  public function getTranslation($field = '', $lang = false){
      $lang = $lang == false ? App::getLocale() : $lang;
      $brand_translation = $this->brand_translations->where('lang', $lang)->first();
      return $brand_translation != null ? $brand_translation->$field : $this->$field;
  }

  public function brand_translations(){
    return $this->hasMany(BrandTranslation::class);
  }

  // return count of products of each brand

  public function products(){
    return $this->hasMany(Product::class);
  }

}
