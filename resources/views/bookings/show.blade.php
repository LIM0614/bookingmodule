@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Back Button --}}
        @unless(session('success'))
            <a href="{{ route('bookings.index') }}" class="btn btn-secondary mb-3">
                ← Back to My Bookings
            </a>
        @endunless

        {{-- Flash Success --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <a href="{{ route('bookings.index') }}" class="btn-close" aria-label="Close"></a>
            </div>
        @endif

        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">📄 Booking Details</h4>
            </div>
            <div class="card-body">
                <p><strong>🆔 Booking ID:</strong> {{ $booking->id }}</p>
                <p><strong>👤 Guest:</strong> {{ $booking->user->name }}</p>

                <hr>

                <p><strong>🏨 Room Type:</strong> {{ $booking->roomType->name }}</p>
                <p><strong>🚪 Assigned Room Number:</strong>
                    <span class="badge bg-info text-dark">{{ $booking->room->room_number }}</span>
                </p>

                <hr>

                <p><strong>📅 Check-In:</strong> {{ $booking->check_in_date }}</p>
                <p><strong>📅 Check-Out:</strong> {{ $booking->check_out_date }}</p>
                <p><strong>📊 Status:</strong>
                    <span
                        class="badge bg-{{ $booking->status === 'pending' ? 'warning' : ($booking->status === 'cancelled' ? 'danger' : 'success') }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </p>
            </div>
        </div>

    </div>
@endsection