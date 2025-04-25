<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public function units()
    {
        return $this->hasMany(RoomUnit::class, 'room_id');
    }

}
