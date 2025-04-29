<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RoomType;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class RealBookingService implements BookingServiceInterface
{
    public function listUpcoming(int $userId): Collection
    {
        $today = Carbon::today()->toDateString();

        return Booking::with('roomType')
            ->where('user_id', $userId)
            ->where('check_in_date', '>=', $today)
            ->orderBy('check_in_date')
            ->get();
    }

    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $roomType = RoomType::findOrFail($data['room_type_id']);

            if ($roomType->capacity < 1) {
                abort(422, 'No available room capacity for this room type.');
            }


            $room = \App\Models\Room::where('room_type_id', $roomType->id)
                ->where('status', 'available')
                ->firstOrFail();


            $roomType->decrement('capacity');
            $room->update(['status' => 'occupied']);


            $data['room_id'] = $room->id;

            return Booking::create($data + [
                'status' => 'pending',
            ]);
        });
    }


    public function show(int $id, int $userId): Booking
    {
        return Booking::with(['roomType', 'room'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function update(int $id, array $data, int $userId): Booking
    {
        return DB::transaction(function () use ($id, $data, $userId): Booking {
            $booking = Booking::with('room', 'roomType')->where('id', $id)
                ->where('user_id', $userId)->firstOrFail();

            if ($data['room_type_id'] != $booking->room_type_id) {

                $booking->room->update(['status' => 'available']);
                $booking->roomType->increment('capacity');

                $newRoomType = RoomType::findOrFail($data['room_type_id']);

                if ($newRoomType->capacity < 1) {
                    abort(422, 'No available capacity for the selected room type.');
                }

                $newRoom = \App\Models\Room::where('room_type_id', $newRoomType->id)
                    ->where('status', 'available')
                    ->firstOrFail();

                $newRoomType->decrement('capacity');
                $newRoom->update(['status' => 'occupied']);

                $booking->update([
                    'room_type_id' => $data['room_type_id'],
                    'room_id' => $newRoom->id,
                ]);
            }

            return $booking;
        });
    }

    public function cancel(int $id, int $userId): void
    {
        DB::transaction(function () use ($id, $userId): void {
            $booking = Booking::with(['room.roomType'])
                ->where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $sevenDaysBefore = Carbon::now()->addDays(7);

            if ($booking->check_in_date < $sevenDaysBefore->toDateString()) {
                throw new \Exception('Booking cannot be cancelled less than 7 days before check-in.');
            }

            // Mark as cancelled
            $booking->update(['status' => 'cancelled']);


            // Restore capacity and room
            $booking->room->roomType->increment('capacity');
            $booking->room->update(['status' => 'available']);
        });
    }
}
