{{-- resources/views/admin/bookings/index.blade.php --}}
@extends('layouts.app')

@php
    use Carbon\Carbon;
    $today = Carbon::today()->toDateString();
@endphp

@section('content')
    <div class="container-fluid py-4">
        <h2 class="mb-4">Booking Management</h2>

        {{-- Filter Form --}}
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="user_name" class="form-label">User Name</label>
                    <input type="text" id="user_name" name="user_name" value="{{ request('user_name') }}"
                        class="form-control" placeholder="Enter name...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="checkin" {{ request('status') == 'checkin' ? 'selected' : '' }}>Checked In</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="room_type_id" class="form-label">Room Type</label>
                    <select id="room_type_id" name="room_type_id" class="form-select">
                        <option value="">All</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}" {{ request('room_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="room_id" class="form-label">Room Number</label>
                    <select id="room_id" name="room_id" class="form-select">
                        <option value="">All</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->room_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="date_from" class="form-label">From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                        class="form-control">
                </div>
                <div class="col-md-1">
                    <label for="date_to" class="form-label">To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        {{-- Booking List --}}
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Room Type</th>
                        <th>Room Number</th>
                        <th>Check-In Date</th>
                        <th>Check-Out Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->user->name }}</td>
                            <td>{{ $booking->roomType->name }}</td>
                            <td>{{ $booking->room->room_number }}</td>
                            <td>{{ $booking->check_in_date }}</td>
                            <td>{{ $booking->check_out_date }}</td>
                            <td class="text-capitalize">{{ $booking->status }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-info">Details</a>

                                @if($booking->status === 'pending' && $booking->check_in_date === Carbon::today()->toDateString())
                                    <form action="{{ route('admin.bookings.checkin', $booking) }}" method="POST"
                                        style="display: inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            Check-In
                                        </button>
                                    </form>
                                @endif

                                @if($booking->status === 'checkin')
                                    <form action="{{ route('admin.bookings.checkout', $booking) }}" method="POST"
                                        style="display: inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            Check-Out
                                        </button>
                                    </form>
                                @endif

                                {{-- Force Cancel --}}
                                @if($booking->status !== 'cancelled' && $booking->check_in_date > $today)
                                    <form action="{{ route('admin.bookings.forceCancel', $booking) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Are you sure to force cancel?');">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" title=" ">
                                            Force Cancel
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $bookings->links() }}
        </div>
    </div>
@endsection