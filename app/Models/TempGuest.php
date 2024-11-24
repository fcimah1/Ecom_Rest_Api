<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempGuest extends Model
{
    use HasFactory;

    protected $fillable = ['temp_user_id','expires_at'];
    protected $table = 'temp_guest';
    protected $hidden = ['id', 'created_at', 'updated_at','expires_at'];


}
