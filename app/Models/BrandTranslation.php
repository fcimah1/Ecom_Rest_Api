<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandTranslation extends Model
{
  protected $fillable = ['name', 'lang', 'brand_id'];
protected $hidden = ['id', 'brand_id', 'created_at', 'updated_at'];


  public function brand(){
    return $this->belongsTo(Brand::class);
  }
}
