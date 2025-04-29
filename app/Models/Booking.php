<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'room_type_id',
        'room_id',
        'name',
        'ic_passport',
        'contact_number',
        'number_guest',
        'check_in_date',
        'check_out_date',
        'status',
    ];


    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

