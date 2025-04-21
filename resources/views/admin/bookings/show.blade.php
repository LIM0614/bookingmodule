@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2>Admin: Booking #{{ $booking->id }}</h2>

        <ul class="list-group mb-4">
            <li class="list-group-item"><strong>User：</strong> {{ $booking->user->name }} ({{ $booking->user->email }})</li>
            <li class="list-group-item"><strong>Room：</strong> {{ $booking->room->name }}</li>
            <li class="list-group-item"><strong>Guests：</strong> {{ $booking->number_guest }}</li>
            <li class="list-group-item"><strong>Check‑In：</strong> {{ $booking->check_in_date }}</li>
            <li class="list-group-item"><strong>Check‑Out：</strong> {{ $booking->check_out_date }}</li>
            <li class="list-group-item"><strong>Status：</strong> {{ ucfirst($booking->status) }}</li>
        </ul>

        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Back to List</a>

        @if($booking->status !== 'cancelled')
            <form action="{{ route('admin.bookings.forceCancel', $booking->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Force cancel this booking?');">
                    Force Cancel
                </button>
            </form>
        @endif
    </div>
@endsection