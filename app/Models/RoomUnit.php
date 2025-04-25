<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomUnit extends Model
{
    // primary key is unit_number (string), not id
    protected $primaryKey = 'unit_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'room_id',
        'unit_number',
        'status',
    ];

    public function type()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function booking()
    {
        return $this->hasOne(Booking::class, 'room_unit_number', 'unit_number');
    }
}
