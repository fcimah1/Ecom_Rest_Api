<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'colors';
    protected $fillable = ['name', 'code'];
    public static function getHex($colorName){
        $color = self::where('name', $colorName)->first();
        return $color->code;
    }

    public static function getColors(){
        return self::all();
    }

}