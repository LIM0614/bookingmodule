@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- 1) Only show “Back” when there is NO success flash --}}
        @unless(session('success'))
            <a href="{{ route('bookings.index') }}" class="btn btn-secondary mb-3">
                ← Back to My Bookings
            </a>
        @endunless

        {{-- 2) If we just created/updated, show success and have its “×” go home --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <a href="{{ route('bookings.index') }}" class="btn-close" aria-label="Close"></a>
            </div>
        @endif

        <h2>Booking Details</h2>
        <p><strong>Booking ID:</strong> {{ $booking->id }}</p>
        <p><strong>Guest:</strong> {{ $booking->user->name }}</p>
        <p><strong>Room:</strong> {{ $booking->room->name }}</p>
        <p><strong>Check‑In:</strong> {{ $booking->check_in_date }}</p>
        <p><strong>Check‑Out:</strong> {{ $booking->check_out_date }}</p>
        <p><strong>Status:</strong> {{ ucfirst($booking->status) }}</p>

    </div>
@endsection