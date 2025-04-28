<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingServiceInterface;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected BookingServiceInterface $bookingService;

    public function __construct(BookingServiceInterface $bookingService)
    {
        $this->middleware('auth');
        $this->bookingService = $bookingService;
    }

    public function index()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: Sat, 01 Jan 1990 00:00:00 GMT");
        $bookings = $this->bookingService->listUpcoming(userId: Auth::guard('web')->id());
        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $rooms = Room::where('capacity', '>', 0)->get();
        $user = Auth::guard('web')->user();
        return view('bookings.create', compact('rooms', 'user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'ic_passport' => 'required|string|max:30',
            'contact_number' => 'required|string|max:20',
            'number_guest' => 'required|integer|min:1',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $booking = $this->bookingService->create(data: $data);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking successfully created.');
    }

    public function show($id)
    {
        $booking = $this->bookingService->show(id: $id, userId: Auth::guard('web')->id());
        return view('bookings.show', compact('booking'));
    }

    public function edit($id)
    {
        $booking = $this->bookingService->show(id: $id, userId: Auth::guard('web')->id());
        $rooms = Room::where('capacity', '>', 0)->get();
        return view('bookings.edit', compact('booking', 'rooms'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $booking = $this->bookingService->update(id: $id, data: $data, userId: Auth::guard('web')->id());

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking successfully updated.');
    }

    public function showCancel($id)
    {
        $booking = $this->bookingService->show(id: $id, userId: Auth::guard('web')->id());
        return view('bookings.cancel', compact('booking'));
    }

    public function cancel($id)
    {
        try {
            $this->bookingService->cancel(id: $id, userId: Auth::guard('web')->id());
            return redirect()->route('bookings.index')->with('success', 'Booking successfully cancelled.');
        } catch (\Exception $e) {
            return redirect()->route('bookings.cancel.confirm', $id)->with('error', $e->getMessage());
        }
    }
}
