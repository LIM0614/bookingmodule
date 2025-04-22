@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <a href="{{ route('bookings.index') }}" class="btn btn-secondary mb-3">
            ← Back to My Bookings
        </a>

        <h2 class="mb-4">✏️ Edit Booking #{{ $booking->id }}</h2>

        {{-- 如果有成功消息，也可在编辑页显示（可选） --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('bookings.update', $booking->id) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            {{-- 1) 房型 --}}
            <div class="mb-3">
                <label for="room_id" class="form-label">🏨 Select Room</label>
                <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror">
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

            {{-- 2) 日期 --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="check_in_date" class="form-label">📅 Check‑In</label>
                    <input type="date" id="check_in_date" name="check_in_date"
                        class="form-control @error('check_in_date') is-invalid @enderror" min="{{ now()->toDateString() }}"
                        value="{{ old('check_in_date', $booking->check_in_date) }}">
                    @error('check_in_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="check_out_date" class="form-label">📅 Check‑Out</label>
                    <input type="date" id="check_out_date" name="check_out_date"
                        class="form-control @error('check_out_date') is-invalid @enderror"
                        min="{{ now()->addDay()->toDateString() }}"
                        value="{{ old('check_out_date', $booking->check_out_date) }}">
                    @error('check_out_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Must be at least one day after check‑in.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Booking</button>
        </form>
    </div>
@endsection