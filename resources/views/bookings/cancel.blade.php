@extends('layouts.app')

@section('content')
    <div class="container py-4">

        @php
            use Carbon\Carbon;
            $cancelDeadline = Carbon::now()->addDays(7)->toDateString();
            $canCancel = $booking->check_in_date >= $cancelDeadline;
        @endphp

        <div class="card shadow-sm border-0">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">⚠️ Cancel Booking #{{ $booking->id }}</h4>
            </div>

            <div class="card-body">

                {{-- 1. Already cancelled --}}
                @if($booking->status === 'cancelled')
                    <div class="alert alert-secondary">
                        ❌ This booking has already been cancelled.
                    </div>

                    {{-- 2. Error message from controller --}}
                @elseif(session('error'))
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
                        <div class="flex-grow-1">
                            {{ session('error') }}
                        </div>
                        <a href="{{ route('bookings.my') }}" class="btn-close" aria-label="Close"></a>
                    </div>

                    {{-- 3. Not eligible (less than 7 days) --}}
                @elseif(!$canCancel)
                    <div class="alert alert-warning">
                        ❌ This booking cannot be cancelled less than <strong>7 days</strong>
                    </div>
                    <a href="{{ route('bookings.my') }}" class="btn btn-secondary">← Back</a>

                    {{-- 4. Eligible to cancel --}}
                @else

                    <p class="text-warning fw-bold mb-3">
                        ⚠️ Cancelled bookings are only eligible for partial refund.
                    </p>

                    {{-- Booking details --}}
                    <ul class="list-group mb-4">
                        <li class="list-group-item"><strong>ID:</strong> {{ $booking->id }}</li>
                        <li class="list-group-item"><strong>Guest:</strong> {{ $booking->user->name }}</li>
                        <li class="list-group-item"><strong>Room Number:</strong> {{ $booking->room->room_number }}</li>
                        <li class="list-group-item"><strong>Check-In:</strong> {{ $booking->check_in_date }}</li>
                        <li class="list-group-item"><strong>Check-Out:</strong> {{ $booking->check_out_date }}</li>
                        <li class="list-group-item"><strong>Status:</strong> {{ ucfirst($booking->status) }}</li>
                    </ul>

                    {{-- Cancel form --}}
                    <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-lg">
                            ✅ Yes, Cancel Booking
                        </button>
                    </form>

                    <a href="{{ route('bookings.my') }}" class="btn btn-secondary btn-lg ms-3">
                        ❌ No, Go Back
                    </a>

                @endif

                {{-- 5. Cancel success feedback --}}
                @if(session('success'))
                    <div class="alert alert-success mt-4">
                        {{ session('success') }}<br>
                        You will be redirected to the homepage in <strong>5 seconds</strong>...
                    </div>

                    <script>
                        setTimeout(function () {
                            window.location.href = "{{ route('bookings.my') }}";
                        }, 5000);
                    </script>
                @endif

            </div>
        </div>

    </div>
@endsection