<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct()
    {
        // FOR DEV / TESTING: auto‑login user #1
        Auth::loginUsingId(1);

        // require auth everywhere in this controller
        $this->middleware('auth');
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $bookings = Booking::with('room')
            ->where('user_id', Auth::id())
            ->whereDate('check_in_date', '>=', $today)
            ->orderBy('check_in_date')
            ->get();

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        //Take only the available room (First check)
        $rooms = Room::where('capacity', '>', 0)->get();
        $user = Auth::user();

        return view('bookings.create', compact('rooms', 'user'));

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'ic_passport' => 'required|string|max:50',
            'contact_number' => 'required|string|max:20',
            'number_guest' => 'required|integer|min:1',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        //double check the capacity of the room at backend
        DB::transaction(function () use ($data, &$booking) {
            $room = Room::findOrFail($data['room_id']);
            if ($room->capacity < 1) {
                abort(422, 'Room Type is not enough');
            }

            $data['user_id'] = Auth::id();
            $data['status'] = 'pending';
            $booking = Booking::create($data);

            //room decrement
            $room->decrement('capacity', 1);
        });



        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking successfully created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this booking.');
        }

        $booking->load(['user', 'room']);
        return view('bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //check the current user(access control)
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this booking.');
        }

        $this->ensureNotCancelled($booking);
        $this->abortIfCheckInIsToday($booking);

        // Pull all rooms with remaining capacity>0, and include the currently selected room type
        $rooms = Room::where('capacity', '>', 0)
            ->orWhere('id', $booking->room_id)
            ->get();

        return view('bookings.edit', compact('booking', 'rooms'));
    }


    /**
     * Validate and update the booking in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this booking.');
        }

        $this->ensureNotCancelled($booking);
        $this->abortIfCheckInIsToday($booking);

        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        DB::transaction(function () use ($booking, $data) {
            // If the room type is changed, the old room capacity will be returned first, and then the new room capacity will be deducted
            if ($data['room_id'] !== $booking->room_id) {
                // Return the old room type
                $booking->room()->increment('capacity', 1);

                // Deduct new room type (and lock to prevent concurrent overselling)
                $newRoom = Room::lockForUpdate()->findOrFail($data['room_id']);
                if ($newRoom->capacity < 1) {
                    abort(422, 'This room type is not available。');
                }
                $newRoom->decrement('capacity', 1);
            }

            // update new record
            $booking->update([
                'room_id' => $data['room_id'],
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
            ]);
        });

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('success', 'Booking successfully updated');
    }


    public function showCancel(Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);
        $this->ensureNotCancelled($booking);
        $this->abortIfCheckInIsToday($booking);
        $booking->load(['user', 'room']);
        return view('bookings.cancel', compact('booking'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }
        $this->ensureNotCancelled($booking);
        $this->abortIfCheckInIsToday($booking);

        // only can cancel before 1 weeks
        $checkIn = Carbon::parse($booking->check_in_date);
        $cutoffTime = $checkIn->copy()->subWeek();  // 入住日前 7 天
        if (Carbon::now()->gt($cutoffTime)) {
            return back()->with('error', 'You only can cancel your bookings before 1 weeks');
        }

        DB::transaction(function () use ($booking) {
            // change the status to cancelled
            $booking->update(['status' => 'cancelled']);

            $booking->room()->increment('capacity', 1);
        });

        return redirect()
            ->route('bookings.index')
            ->with('success', '预订已成功取消。');
    }

    protected function ensureNotCancelled(Booking $booking)
    {
        // 如果已经取消，抛出 403 或者带错误重定向
        if ($booking->status === 'cancelled') {
            abort(403, 'This booking has already been cancelled and cannot be modified.');
        }
    }

    protected function abortIfCheckInIsToday(Booking $booking)
    {
        if (Carbon::parse($booking->check_in_date)->isToday()) {
            abort(403, 'You cannot modify or cancel on the day of check‑in.');
        }
    }
}
