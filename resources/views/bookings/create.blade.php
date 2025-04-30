@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>✍️ Add New Booking</h2>
            <a href="{{ route('bookings.index') }}" class="btn btn-success">
                🏠 Home
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('bookings.store') }}" method="POST" novalidate>
            @csrf

            {{-- 1) Guest Name --}}
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" value="{{ Auth::user()->name }}" class="form-control text-white"
                    style="background-color: #6c757d;" readonly>
            </div>

            {{-- 2) IC / Passport --}}
            <div class="mb-3">
                <label for="ic_passport" class="form-label">IC / Passport</label>
                <input type="text" id="ic_passport" name="ic_passport" value="{{ Auth::user()->ic_passport }}"
                    class="form-control text-white" style="background-color: #6c757d;" readonly>
            </div>

            {{-- 3) Contact Phone --}}
            <div class="mb-3">
                <label for="contact_number" class="form-label">Contact Phone</label>
                <input type="text" id="contact_number" name="contact_number" value="{{ Auth::user()->phone_number }}"
                    class="form-control text-white" style="background-color: #6c757d;" readonly>
            </div>

            {{-- 4) Select Room --}}
            <div class="mb-3">
                <label class="form-label">🏨 Room Type</label>
                <input type="text" class="form-control text-white" style="background-color: #6c757d;"
                    value="{{ $roomType->name }} " readonly>
                <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
            </div>


            {{-- 5) Number of Guests --}}
            <div class="mb-3">
                <label for="number_guest" class="form-label">👥 Number of Guests</label>
                <input type="number" id="number_guest" name="number_guest" min="1" value="{{ old('number_guest', 1) }}"
                    class="form-control @error('number_guest') is-invalid @enderror">
                @error('number_guest')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 6) Check‑in and Check‑out --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="check_in_date" class="form-label">📅 Check‑In</label>
                    <input type="date" id="check_in_date" name="check_in_date" min="{{ now()->toDateString() }}"
                        value="{{ old('check_in_date') }}"
                        class="form-control @error('check_in_date') is-invalid @enderror">
                    @error('check_in_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="check_out_date" class="form-label">📅 Check‑Out</label>
                    <input type="date" id="check_out_date" name="check_out_date"
                        class="form-control @error('check_out_date') is-invalid @enderror"
                        value="{{ old('check_out_date') }}" {{-- will be overridden by JS on check‑in change --}}
                        min="{{ now()->addDay()->toDateString() }}">
                    @error('check_out_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Must be at least one day after check‑in.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Submit Booking</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ci = document.getElementById('check_in_date');
            const co = document.getElementById('check_out_date');

            ci.addEventListener('change', () => {
                // bump checkout.min to checkin +1 day
                let d = new Date(ci.value);
                d.setDate(d.getDate() + 1);
                const minOut = d.toISOString().split('T')[0];
                co.min = minOut;
                if (co.value < minOut) {
                    co.value = minOut;
                }
            });
        });
    </script>
@endsection