<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'status',
        'image',
        'type',
        'url',
        'position',
        'start_date',
        'end_date',
    ];

    // protected $appends = ['photo_url'];

    protected $hidden = [
        'position',
        'start_date',
        'created_at',
        'updated_at',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'on');
    }
    public function scopeInactive($query)
    {
        return $query->where('status', 'off');
    }
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // return end date as date not strtotime
    public function getEndDateAttribute($value)
    {
        return date('Y-m-d h:m:s', $value);
    }

}
