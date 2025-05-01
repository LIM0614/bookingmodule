<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingServiceInterface;
use App\Models\RoomType;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Events\BookingCreated;

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

        $roomTypes = RoomType::whereHas('rooms', function ($query) {
            $query->where('status', 'available');
        })->get();

        return view('bookings.index', compact('roomTypes'));
    }

    public function create($roomTypeId)
    {
        // Find the room type by ID
        $roomType = RoomType::findOrFail($roomTypeId);

        // Check if there are available rooms for this room type
        $availableRooms = \App\Models\Room::where('room_type_id', $roomType->id)
            ->where('status', 'available')
            ->count();

        // If no available rooms, redirect back to the index or show an error
        if ($availableRooms == 0) {
            return redirect()->route('bookings.index')->with('error', 'No available rooms for this room type.');
        }

        // Proceed to the create page if there are available rooms
        return view('bookings.create', compact('roomType'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Validate incoming request
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

        // Call the create method from the service
        $booking = $this->bookingService->create($data);

        // Dispatch the booking created event
        BookingCreated::dispatch();

        // Redirect to the booking details page with a success message
        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking successfully created.');
    }

    public function show($id): View
    {
        // Fetch booking details
        $booking = $this->bookingService->show($id, Auth::guard('web')->id());
        return view('bookings.show', compact('booking'));
    }

    public function edit($id): View
    {
        $booking = $this->bookingService->show($id, Auth::guard('web')->id());

        $roomTypes = RoomType::all();

        $roomTypes = RoomType::whereHas('rooms', function ($query) {
            $query->where('status', 'available');
        })->get();

        return view('bookings.edit', compact('booking', 'roomTypes'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
        ]);

        $data['user_id'] = Auth::guard('web')->id();

        // Call the update method from the service
        $booking = $this->bookingService->update($id, $data, $data['user_id']);

        BookingCreated::dispatch();

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking successfully updated.');
    }

    // Cancel the booking
    public function showCancel($id): View
    {
        $booking = $this->bookingService->show($id, Auth::guard('web')->id());
        return view('bookings.cancel', compact('booking'));
    }

    public function cancel($id): RedirectResponse
    {
        try {
            // Call the cancel method from the service
            $this->bookingService->cancel($id, Auth::guard('web')->id());
            BookingCreated::dispatch();

            return redirect()
                ->route('bookings.cancel.confirm', $id)
                ->with('success', 'Booking has been successfully cancelled. You will be redirected in 5 seconds.');

        } catch (\Exception $e) {
            return redirect()
                ->route('bookings.cancel.confirm', $id)
                ->with('error', $e->getMessage());
        }
    }

    // Handle XML transformation for bookings
    public function transformXmlToXhtml(Request $request)
    {
        // Load the XML file (make sure the XML is in a readable location)
        $xml = new \DOMDocument();
        $xml->load(storage_path('app/public/bookings.xml'));  // Path to your XML file

        // Load the XSLT file from storage
        $xslt = new \DOMDocument();
        $xslt->load(storage_path('app/public/bookings_stylesheet.xsl'));  // Path to your XSL file

        // Initialize the XSLTProcessor
        $processor = new \XSLTProcessor();
        $processor->importStylesheet($xslt);

        // Get the target date from the request, default to '2025-05-01' if not provided
        $targetDate = $request->input('date', '2025-05-01');

        // Set the parameter for the XSLT transformation (dynamic date)
        $processor->setParameter('', 'target_date', $targetDate);

        // Perform the transformation
        $xhtml = $processor->transformToXML($xml);

        // Return the transformed HTML to the browser
        return response($xhtml, 200)->header('Content-Type', 'text/html');
    }
}
