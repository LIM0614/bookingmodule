<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\RoomType;
use App\Models\Room;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminBookingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth('admin')->check()) {
                return redirect()->route('admin.login');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: Sat, 01 Jan 1990 00:00:00 GMT");

        // 预加载 user、roomType（房型）、room（房间）
        $query = Booking::with(['user', 'roomType', 'room'])
            ->orderBy('check_in_date', 'asc');

        // —— 过滤条件 —— //

        // 用户姓名
        if ($request->filled('user_name')) {
            $query->whereHas(
                'user',
                fn($q) =>
                $q->where('name', 'like', '%' . $request->user_name . '%')
            );
        }

        // 预订状态
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 房型过滤
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        // 房间（rooms 表）过滤
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // 入住日期区间
        if ($request->filled('date_from')) {
            $query->whereDate('check_in_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('check_in_date', '<=', $request->date_to);
        }

        // 分页
        $bookings = $query->paginate(20)->withQueryString();

        // AJAX 分页返回
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.bookings.partials.list', compact('bookings'))->render(),
                'next_page_url' => $bookings->nextPageUrl(),
            ]);
        }

        // 下拉菜单数据
        $roomTypes = RoomType::all();
        $rooms = Room::all();

        return view('admin.bookings.index', compact('bookings', 'roomTypes', 'rooms'));
    }

    /**
     * 查看单一预订详情
     */
    public function show(Booking $booking)
    {
        $booking->load(['user', 'roomType', 'room']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function checkIn(Booking $booking)
    {
        $today = Carbon::today()->toDateString();

        // 仅对状态为 pending 且入住日期为今天的预订生效
        if ($booking->status !== 'pending') {
            return redirect()
                ->route('admin.bookings.index')
                ->with('error', 'Only booking status is pending can check in.');
        }

        if ($booking->check_in_date !== $today) {
            return redirect()
                ->route('admin.bookings.index')
                ->with('error', 'Only accept check in in check in date.');
        }

        // 更新状态
        $booking->update(['status' => 'checkin']);

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Successful check in.');
    }

    public function checkOut(Booking $booking)
    {
        // 仅对状态为 pending 且入住日期为今天的预订生效
        if ($booking->status !== 'checkin') {
            return redirect()
                ->route('admin.bookings.index')
                ->with('error', 'Only check in user can check out');
        }

        // 更新状态
        $booking->update(attributes: ['status' => 'checkout']);
        $booking->room->roomType->increment('capacity');
        $booking->room->update(attributes: ['status' => 'available']);

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Successful check out.');
    }

    /**
     * 管理员强制取消预订
     */
    public function forceCancel(Booking $booking)
    {
        $today = Carbon::today()->toDateString();

        // 如果已经取消
        if ($booking->status === 'cancelled') {
            return redirect()
                ->route('admin.bookings.index')
                ->with('error', 'This booking has already been cancelled.');
        }

        // 如果入住日是今天或更早，则禁止强制取消
        if ($booking->check_in_date <= $today) {
            abort(403, 'Force-cancel is only allowed before the check-in date.');
        }

        // 走到这里，说明可以取消
        $booking->update(['status' => 'cancelled']);

        // 归还剩余容量
        $booking->room->roomType->increment('capacity');
        $booking->room->update(['status' => 'available']);

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Booking forcefully cancelled.');
    }
}
