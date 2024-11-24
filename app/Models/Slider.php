<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
  protected $fillable = ['photo', 'published', 'link'];
  protected $hidden = ['created_at', 'updated_at','link'];
}
