@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- 固定回到列表，不用 previous() --}}
        <a href="{{ route('bookings.index') }}" class="btn btn-secondary mb-3">
            ← Back to My Bookings
        </a>

        {{-- 如果有 update() 带来的 success flash --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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