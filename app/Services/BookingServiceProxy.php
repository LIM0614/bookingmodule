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
        // 仅本人可查看自己的列表
        abort_unless(Auth::id() === $userId, 403, '无权查看他人预订');
        return $this->real->listUpcoming($userId);
    }

    public function create(array $data): Booking
    {
        // 自动注入当前用户
        $data['user_id'] = Auth::id();
        return $this->real->create($data);
    }

    public function show(int $id, int $userId): Booking
    {
        $booking = $this->real->show($id, $userId);
        abort_unless($booking->user_id === Auth::id(), 403, '无权查看此预订');
        return $booking;
    }

    public function update(int $id, array $data, int $userId): Booking
    {
        $booking = Booking::findOrFail($id);
        abort_unless($booking->user_id === Auth::id(), 403, '无权修改此预订');
        // 不能修改已取消或当天入住
        abort_if($booking->status === 'cancelled', 422, '已取消不可修改');
        abort_if(Carbon::parse($booking->check_in_date)->isToday(), 422, '当天不可修改');
        return $this->real->update($id, $data, $userId);
    }

    public function cancel(int $id, int $userId): void
    {
        $booking = Booking::findOrFail($id);
        abort_unless($booking->user_id === Auth::id(), 403, '无权取消此预订');
        // 一周前才能取消
        $cutoff = Carbon::parse($booking->check_in_date)->subWeek();
        abort_if(Carbon::now()->gt($cutoff), 422, '只能在入住前一周取消');
        $this->real->cancel($id, $userId);
    }
}
