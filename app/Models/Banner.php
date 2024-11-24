<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';
    protected $fillable = [
        'photo',
        'url',
        'published',
        'position',
    ];
    protected $appends = ['photo_url'];

    protected $hidden = [
        'photo',
        'url',
        'published',
        'position',
        'created_at',
        'updated_at',
    ];

    public function getphotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('public/' . $this->photo);
        }
        return asset('photos/no-photo.png');
    }
}
