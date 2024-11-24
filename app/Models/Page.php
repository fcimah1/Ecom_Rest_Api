<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Page extends Model
{

  protected $fillable = ['title', 'slug', 'content', 'meta_title', 'meta_description', 'keywords', 'created_at', 'updated_at'];
  protected $hidden = [
    'created_at',
    'updated_at',
    'meta_title',
    'meta_description',
    'meta_image',
    'keywords',
    'slug'
  ];

  public function getTranslation($field = '', $lang = false)
  {
    $lang = $lang == false ? App::getLocale() : $lang;
    $page_translation = $this->hasMany(PageTranslation::class)->where('lang', $lang)->first();
    return $page_translation != null ? $page_translation->$field : $this->$field;
  }

  public function page_translations()
  {
    return $this->hasMany(PageTranslation::class);
  }
}