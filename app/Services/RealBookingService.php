<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Exception;

class RealBookingService implements BookingServiceInterface
{
    /**
     * List all upcoming bookings for a user.
     */
    public function listUpcoming(int $userId): Collection
    {
        $today = Carbon::today()->toDateString();

        return Booking::with('roomType')
            ->where('user_id', $userId)
            ->where('check_in_date', '>=', $today)
            ->orderBy('check_in_date')
            ->get();
    }

    /**
     * Check if there are enough available rooms for the requested booking.
     */
    private function checkRoomAvailability(int $roomTypeId, int $requiredRooms): bool
    {
        // Count how many available rooms exist for this room type
        $availableRooms = Room::where('room_type_id', $roomTypeId)
            ->where('status', 'available') // Room must be available
            ->count();

        // If the number of available rooms is less than the required rooms, return false
        return $availableRooms >= $requiredRooms;
    }

    /**
     * Create a new booking.
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $roomType = RoomType::findOrFail($data['room_type_id']);

            // Ensure there are enough available rooms for the booking
            $requiredRooms = $data['required_rooms'] ?? 1; // Default to 1 room if not provided

            if (!$this->checkRoomAvailability($data['room_type_id'], $requiredRooms)) {
                throw new Exception('No available rooms for this room type.');
            }

            // Retrieve the required number of available rooms
            $availableRooms = Room::where('room_type_id', $roomType->id)
                ->where('status', 'available')
                ->limit($requiredRooms) // Limit to the number of required rooms
                ->get();

            // Mark each room as occupied
            foreach ($availableRooms as $room) {
                $room->update(['status' => 'occupied']);
            }

            // Add the room ID (from the first room) to the booking data
            $data['room_id'] = $availableRooms->first()->id;

            // Create the booking and return the newly created booking
            return Booking::create($data + [
                'status' => 'pending', // Set the booking status as pending
            ]);
        });
    }

    /**
     * Show details for a specific booking.
     */
    public function show(int $id, int $userId): Booking
    {
        return Booking::with(['roomType', 'room'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    /**
     * Update a booking (change room type or room assignment).
     */
    public function update(int $id, array $data, int $userId): Booking
    {
        return DB::transaction(function () use ($id, $data, $userId): Booking {
            $booking = Booking::with('room', 'roomType')->where('id', $id)
                ->where('user_id', $userId)->firstOrFail();

            // If the room type is changed, handle room status updates
            if ($data['room_type_id'] != $booking->room_type_id) {
                // Make the old room available again
                $booking->room->update(['status' => 'available']);

                // Get the new room type
                $newRoomType = RoomType::findOrFail($data['room_type_id']);

                // Ensure there are available rooms for the new room type
                if (!$this->checkRoomAvailability($newRoomType->id, 1)) {
                    throw new Exception('No available rooms for the selected room type.');
                }

                // Get the first available room of the new type
                $newRoom = Room::where('room_type_id', $newRoomType->id)
                    ->where('status', 'available')
                    ->firstOrFail();

                // Mark the new room as occupied
                $newRoom->update(['status' => 'occupied']);

                // Update the booking with the new room and room type
                $booking->update([
                    'room_type_id' => $data['room_type_id'],
                    'room_id' => $newRoom->id,
                ]);
            }

            return $booking;
        });
    }

    /**
     * Cancel a booking.
     */
    public function cancel(int $id, int $userId): void
    {
        DB::transaction(function () use ($id, $userId): void {
            $booking = Booking::with(['room.roomType'])
                ->where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            // Ensure that cancellation is not less than 7 days before check-in
            $sevenDaysBefore = Carbon::now()->addDays(7);

            if ($booking->check_in_date < $sevenDaysBefore->toDateString()) {
                throw new Exception('Booking cannot be cancelled less than 7 days before check-in.');
            }

            // Mark as cancelled
            $booking->update(['status' => 'cancelled']);

            // Mark the room as available again
            $booking->room->update(['status' => 'available']);
        });
    }
}
