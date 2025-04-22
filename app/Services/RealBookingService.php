<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
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
            $room = Room::findOrFail($data['room_id']);
            if ($room->capacity < 1) {
                abort(422, '房型剩余不足');
            }
            $room->decrement('capacity', 1);
            return Booking::create($data + [
                'status' => 'pending',
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
        DB::transaction(function () use ($id) {
            $booking = Booking::findOrFail($id);
            // …（原 cancel() 里逻辑）
            $booking->update(['status' => 'cancelled']);
            $booking->room->increment('capacity', 1);
        });
    }
}
