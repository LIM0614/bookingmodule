@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Back to list --}}
        <a href="{{ route('bookings.my') }}" class="btn btn-secondary mb-3">
            ← Back to My Bookings
        </a>

        {{-- Success alert --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">✏️ Edit Booking #{{ $booking->id }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('bookings.update', $booking->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Room Type --}}
                    <div class="mb-4">
                        <label for="room_type_id" class="form-label">🏨 Select Room Type</label>
                        <select name="room_type_id" id="room_type_id"
                            class="form-select @error('room_type_id') is-invalid @enderror">
                            <option value="">Choose a room type...</option>
                            @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}" {{ old('room_type_id', $booking->room_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} (Remaining {{ $type->capacity }} rooms)
                                </option>
                            @endforeach
                        </select>
                        @error('room_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted">The system will automatically assign a room number.</div>
                    </div>

                    {{-- Dates --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">📅 Check-In Date</label>
                            <input type="text" class="form-control" value="{{ $booking->check_in_date }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">📅 Check-Out Date</label>
                            <input type="text" class="form-control" value="{{ $booking->check_out_date }}" readonly>
                        </div>
                        <div class="form-text text-muted">Only allow to change the room type</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            💾 Update Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection