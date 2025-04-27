<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingServiceInterface;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $bookings = $this->bookingService->listUpcoming(Auth::id());
        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $rooms = Room::where('capacity', '>', 0)->get();
        $user = Auth::user();
        return view('bookings.create', compact('rooms', 'user'));
    }

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

        $booking = $this->bookingService->create($data);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking successfully created');
    }

    public function show($id)
    {
        $booking = $this->bookingService->show($id, Auth::id());
        return view('bookings.show', compact('booking'));
    }

    public function edit($id)
    {
        $booking = $this->bookingService->show($id, Auth::id());

        // 以下两个方法可保留在 Controller 中，或也可移到 Proxy
        $this->ensureNotCancelled($booking);
        $this->abortIfCheckInIsToday($booking);

        $rooms = Room::where('capacity', '>', 0)
            ->get();


        return view('bookings.edit', compact('booking', 'rooms'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $booking = $this->bookingService->update($id, $data, Auth::id());

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking successfully updated');
    }

    public function showCancel($id)
    {
        // 调用 proxy 或 service 拿到 booking
        $booking = $this->bookingService->show($id, Auth::id());

        // 你原来可能还有这两步检查
        $this->ensureNotCancelled($booking);
        $this->abortIfCheckInIsToday($booking);

        return view('bookings.cancel', compact('booking'));
    }

    public function cancel($id)
    {
        try {
            $this->bookingService->cancel($id, Auth::id());

            // on success, go back to list with success flash
            return redirect()
                ->route('bookings.index')
                ->with('success', 'Booking successfully cancelled');
        } catch (HttpException $e) {
            // on failure, redirect back to the confirmation page with the error message
            return redirect()
                ->route('bookings.cancel.confirm', $id)
                ->with('error', $e->getMessage());
        }
    }

    // 下面两个辅助方法可不变，或移至 Proxy
    protected function ensureNotCancelled($booking)
    {
        abort_if($booking->status === 'cancelled', 422, '已取消的预订不可操作');
    }

    protected function abortIfCheckInIsToday($booking)
    {
        abort_if(
            \Carbon\Carbon::parse($booking->check_in_date)->isToday(),
            422,
            '当天不可操作'
        );
    }
}
