<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'ic_passport',
        'contact_number',
        'number_guest',
        'room_id',
        'room_unit_number',   // ← add this!
        'check_in_date',
        'check_out_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 3) The room that was booked
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function unit()
    {
        return $this->belongsTo(
            RoomUnit::class,
            'room_unit_number',  // FK on bookings
            'unit_number'        // PK on room_units
        );
    }

}
