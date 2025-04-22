<?php
namespace App\Services;

use App\Models\Booking;
use Illuminate\Http\Request;

interface BookingServiceInterface
{
    public function listUpcoming(int $userId): \Illuminate\Database\Eloquent\Collection;
    public function create(array $data): Booking;
    public function show(int $id, int $userId): Booking;
    public function update(int $id, array $data, int $userId): Booking;
    public function cancel(int $id, int $userId): void;
}
