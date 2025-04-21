@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>✍️ Add New Booking</h2>
            {{-- 🏠 回到首页（我的预订列表） --}}
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
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                    class="form-control @error('name') is-invalid @enderror" readonly>
            </div>

            {{-- 2) IC / Passport --}}
            <div class="mb-3">
                <label for="ic_passport" class="form-label">IC / Passport</label>
                <input type="text" id="ic_passport" name="ic_passport" value="{{ old('ic_passport', $user->ic_passport) }}"
                    class="form-control @error('ic_passport') is-invalid @enderror" readonly>
                @error('ic_passport')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 3) Contact Phone --}}
            <div class="mb-3">
                <label for="contact_number" class="form-label">Contact Phone</label>
                <input type="text" + id="contact_number" name="contact_number"
                    value="{{ old('contact_number', $user->phone_number) }}"
                    class="form-control @error('contact_number') is-invalid @enderror" readonly>
                @error('contact_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- …then your existing room, dates, status fields… --}}
            {{-- 4) Select Room --}}
            <div class="mb-3">
                <label for="room_id" class="form-label">🏨 Select Room</label>
                <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror">
                    <option value="">Choose a room…</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @if($room->capacity === 0) disabled @endif {{ old('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->name }}
                            @if($room->capacity === 0)
                                （Full）
                            @else
                                （Remaining {{ $room->capacity }} room）
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('room_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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