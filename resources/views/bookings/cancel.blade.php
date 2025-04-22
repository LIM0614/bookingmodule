{{-- resources/views/bookings/cancel.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- 1) Already cancelled? --}}
        @if($booking->status === 'cancelled')
            <div class="alert alert-secondary">
                This booking has already been cancelled.
            </div>

        @else

            {{-- 2) Error flash (too late to cancel) --}}
            @if(session('error'))
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <div class="flex-grow-1">
                        {{ session('error') }}
                    </div>
                    {{-- A real link styled as a close button --}}
                    <a href="{{ route('bookings.index') }}" class="btn-close" aria-label="Close">
                    </a>
                </div>
            @endif

            {{-- 3) Confirmation heading & details --}}
            <h2 class="mb-4 text-danger">⚠️ Cancel Booking</h2>
            <p>Are you sure you want to cancel this booking? This action cannot be undone.</p>

            <ul class="list-group mb-4">
                <li class="list-group-item"><strong>ID:</strong> {{ $booking->id }}</li>
                <li class="list-group-item"><strong>Guest:</strong> {{ $booking->user->name }}</li>
                <li class="list-group-item"><strong>Room:</strong> {{ $booking->room->name }}</li>
                <li class="list-group-item"><strong>Check‑In:</strong> {{ $booking->check_in_date }}</li>
                <li class="list-group-item"><strong>Check‑Out:</strong> {{ $booking->check_out_date }}</li>
                <li class="list-group-item"><strong>Status:</strong> {{ ucfirst($booking->status) }}</li>
            </ul>

            {{-- 4) Only show buttons if no error flash --}}
            @unless(session('error'))
                <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        Yes, Cancel Booking
                    </button>
                </form>
                <a href="{{ route('bookings.index') }}" class="btn btn-secondary">
                    No, Go Back
                </a>
            @endunless

        @endif

    </div>
@endsection