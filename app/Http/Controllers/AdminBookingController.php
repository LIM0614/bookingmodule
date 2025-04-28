<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function __construct()
    {
        // 必须通过 Admin Guard 登录
        $this->middleware('auth:admin');

        // 只允许 ID = 1 的 Super Admin 访问
        $this->middleware(function ($request, $next) {
            if (auth('admin')->id() != 1) {
                abort(403, 'Only Super Admin can access.');
            }
            return $next($request);
        });
    }

    /**
     * 查看所有预订（带过滤）
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: Sat, 01 Jan 1990 00:00:00 GMT");

        $query = Booking::with(['user', 'room'])
            ->orderBy('check_in_date', 'asc');

        // 过滤：用户名字
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%');
            });
        }

        // 过滤：状态
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 过滤：房间
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // 过滤：入住时间段
        if ($request->filled('date_from')) {
            $query->whereDate('check_in_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('check_in_date', '<=', $request->date_to);
        }

        $bookings = $query->paginate(20)->withQueryString();

        // 如果是 AJAX 请求
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.bookings.partials.list', compact('bookings'))->render(),
                'next_page_url' => $bookings->nextPageUrl(),
            ]);
        }

        // 正常页面请求
        $rooms = Room::all();
        return view('admin.bookings.index', compact('bookings', 'rooms'));
    }

    /**
     * 查看单一预订详情
     */
    public function show(Booking $booking): \Illuminate\View\View
    {
        $booking->load(['user', 'room']);
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * 强制取消预订（管理员权限）
     */
    public function forceCancel(Booking $booking): \Illuminate\Http\RedirectResponse
    {
        if ($booking->status !== 'cancelled') {
            $booking->update([
                'status' => 'cancelled',
            ]);

            $booking->room->increment('capacity', $booking->number_guest);
        }

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Booking forcefully cancelled.');
    }
}
