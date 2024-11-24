<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
class Blog extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $hidden = [
        "meta_title",
        "category_id",
        "meta_description",
        "status",
        "created_at",
        "updated_at",
        "deleted_at"
    ];
    protected $with = [
        'category'
    ];
    public function category() {
        return $this->belongsTo(BlogCategory::class, 'category_id')->select(columns: ['id', 'category_name']);
    }

}
