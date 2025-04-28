<?php

namespace App\Services;

use App\Services\BookingServiceInterface;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BookingServiceProxy implements BookingServiceInterface
{
    protected BookingServiceInterface $real;

    public function __construct(BookingServiceInterface $real)
    {
        $this->real = $real;
    }

    public function listUpcoming(int $userId): Collection
    {
        abort_unless(Auth::guard('web')->id() === $userId, 403, 'Only allowed to view your own bookings.');
        return $this->real->listUpcoming(userId: $userId);
    }

    public function create(array $data): Booking
    {
        $data['user_id'] = Auth::guard('web')->id();
        return $this->real->create(data: $data);
    }

    public function show(int $id, int $userId): Booking
    {
        $booking = $this->real->show(id: $id, userId: $userId);
        abort_unless($booking->user_id === Auth::guard('web')->id(), 403, 'No permission to view other users’ bookings.');
        return $booking;
    }

    public function update(int $id, array $data, int $userId): Booking
    {
        $booking = Booking::findOrFail($id);

        abort_unless($booking->user_id === Auth::guard('web')->id(), 403, 'No permission to modify.');

        $this->ensureNotCancelled($booking);
        $this->abortIfCheckInIsToday($booking);

        return $this->real->update(id: $id, data: $data, userId: $userId);
    }

    public function cancel(int $id, int $userId): void
    {
        $booking = Booking::findOrFail($id);

        abort_unless($booking->user_id === Auth::guard('web')->id(), 403, 'No permission to cancel.');

        $this->ensureNotCancelled($booking);
        $this->abortIfCheckInIsToday($booking);

        $this->real->cancel(id: $id, userId: $userId);
    }

    // ========== PRIVATE HELPERS ==========

    private function ensureNotCancelled(Booking $booking): void
    {
        abort_if($booking->status === 'cancelled', 422, 'The booking is already cancelled.');
    }

    private function abortIfCheckInIsToday(Booking $booking): void
    {
        abort_if(Carbon::parse($booking->check_in_date)->isToday(), 422, 'Cannot operate on the check-in date.');
    }
}
