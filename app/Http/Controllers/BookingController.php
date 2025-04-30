<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingServiceInterface;
use App\Models\RoomType;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BookingController extends Controller
{
    protected BookingServiceInterface $bookingService;

    public function __construct(BookingServiceInterface $bookingService)
    {
        $this->middleware('auth');
        $this->bookingService = $bookingService;
    }

    public function myBookings(): View
    {
        $bookings = $this->bookingService->listUpcoming(Auth::guard('web')->id());

        return view('bookings.myBookings', compact('bookings'));
    }


    public function index()
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Sat, 01 Jan 1990 00:00:00 GMT');

        // 只取剩余 capacity > 0 的房型
        $roomTypes = RoomType::where('capacity', '>', 0)
            ->orderBy('name')
            ->get();

        return view('bookings.index', compact('roomTypes'));
    }


    public function create($roomTypeId): View
    {
        $roomType = RoomType::findOrFail($roomTypeId);

        return view('bookings.create', compact('roomType'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'ic_passport' => 'required|string|max:30',
            'contact_number' => 'required|string|max:20',
            'number_guest' => 'required|integer|min:1',
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $data['user_id'] = Auth::guard('web')->id();

        $booking = $this->bookingService->create($data);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking successfully created.');
    }


    public function show($id): View
    {
        $booking = $this->bookingService->show($id, Auth::guard('web')->id());
        return view('bookings.show', compact('booking'));
    }

    public function edit($id): View
    {
        $booking = $this->bookingService->show($id, Auth::guard('web')->id());

        $roomTypes = RoomType::where('capacity', '>', 0)->get();

        return view('bookings.edit', compact('booking', 'roomTypes'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
        ]);

        $data['user_id'] = Auth::guard('web')->id();

        $booking = $this->bookingService->update($id, $data, $data['user_id']);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking successfully updated.');
    }

    //改下面的
    public function showCancel($id): View
    {
        $booking = $this->bookingService->show($id, Auth::guard('web')->id());
        return view('bookings.cancel', compact('booking'));
    }

    public function cancel($id): RedirectResponse
    {
        try {
            $this->bookingService->cancel($id, Auth::guard('web')->id());

            return redirect()
                ->route('bookings.cancel.confirm', $id)
                ->with('success', 'Booking has been successfully cancelled. You will be redirected in 5 seconds.');
        } catch (\Exception $e) {
            return redirect()
                ->route('bookings.cancel.confirm', $id)
                ->with('error', $e->getMessage());
        }
    }
}
