<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    //
    protected $fillable = [
        'name',
        'capacity',
        'price_per_night',
        'description',
        'image',
        'amenities',
    ];
}
