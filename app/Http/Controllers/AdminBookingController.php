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
        // 为测试自动登录 ID=1
        Auth::loginUsingId(1);

        // 必须登录
        $this->middleware('auth');

        // 仅允许 ID=1 访问
        $this->middleware(function ($req, $next) {
            if (Auth::id() !== 1) {
                abort(403, 'Only Super Admin can access.');
            }
            return $next($req);
        });
    }

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'room'])
            ->orderBy('check_in_date', 'asc');

        // 筛选：用户名（模糊）
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%');
            });
        }
        // 筛选：状态
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // 筛选：房型
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        // 筛选：入住日期区间
        if ($request->filled('date_from')) {
            $query->whereDate('check_in_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('check_in_date', '<=', $request->date_to);
        }

        // 每页 20 条
        $bookings = $query->paginate(20)->withQueryString();

        // 如果是 AJAX 请求，就返回局部渲染的 HTML 和下一页 URL
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.bookings.partials.list', compact('bookings'))->render(),
                'next_page_url' => $bookings->nextPageUrl(),
            ]);
        }

        // 首次加载，返回完整视图
        $rooms = Room::all();
        return view('admin.bookings.index', compact('bookings', 'rooms'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'room']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function forceCancel(Booking $booking)
    {
        if ($booking->status !== 'cancelled') {
            $booking->update(['status' => 'cancelled']);
            $booking->room()->increment('capacity', $booking->number_guest);
        }

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Booking force‑cancelled.');
    }
}