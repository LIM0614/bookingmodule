<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RealBookingService implements BookingServiceInterface
{
    public function listUpcoming(int $userId): Collection
    {
        $today = Carbon::today()->toDateString();
        return Booking::with('room')
            ->where('user_id', $userId)
            ->whereDate('check_in_date', '>=', $today)
            ->orderBy('check_in_date')
            ->get();
    }

    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // 1) Ensure capacity
            $room = Room::findOrFail($data['room_id']);
            if ($room->capacity < 1) {
                abort(422, 'No enough room');
            }

            // 2) Lock & grab one free unit
            $unit = RoomUnit::where('room_id', $room->id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->firstOrFail();

            // 3) Mark it booked
            $unit->update(['status' => 'booked']);

            // 4) Decrement cached capacity (optional)
            $room->decrement('capacity');

            // 5) Create the booking, storing that unit’s number
            return Booking::create($data + [
                'status' => 'pending',
                'room_unit_number' => $unit->unit_number,
            ]);
        });
    }

    public function show(int $id, int $userId): Booking
    {
        $booking = Booking::with('room')->findOrFail($id);
        return $booking;
    }

    public function update(int $id, array $data, int $userId): Booking
    {
        return DB::transaction(function () use ($id, $data) {
            $booking = Booking::findOrFail($id);
            // …（与原来 update() 同逻辑：capacity ++/–）
            return tap($booking, function ($b) use ($data) {
                $b->update($data);
            });
        });
    }

    public function cancel(int $id, int $userId): void
    {
        DB::transaction(function () use ($id, $userId) {
            $booking = Booking::findOrFail($id);
            abort_unless($booking->user_id === $userId, 403);
            // your “one-week” check here…

            // 1) Mark booking cancelled
            $booking->update(['status' => 'cancelled']);

            // 2) Release the unit
            if ($unit = $booking->unit) {
                $unit->update(['status' => 'available']);
            }

            // 3) Restore capacity
            $booking->room->increment('capacity');
        });
    }
}
