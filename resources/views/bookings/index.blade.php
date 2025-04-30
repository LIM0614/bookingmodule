@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- Header with title on the left and “My Bookings” button on the right --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Available Hotel Room Types</h2>
            <a href="{{ route('bookings.my') }}" class="btn btn-outline-primary">
                My Bookings
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Show message if no available room types --}}
        @if($roomTypes->isEmpty())
            <p>No room types available at the moment. Please check back later.</p>
        @else
            {{-- Hotel Room Types Section --}}
            @foreach ($roomTypes as $type)
                <div class="card mb-4 shadow-sm d-flex flex-row overflow-hidden">
                    {{-- Left: Room Image --}}
                    @if ($type->image)
                        <img src="{{ asset('images/rooms/' . $type->image) }}" class="img-fluid w-25 object-fit-cover"
                            style="max-height: 180px;" alt="Room Image">
                    @else
                        <div class="bg-secondary text-white w-25 d-flex align-items-center justify-content-center"
                            style="height: 180px;">
                            No Image
                        </div>
                    @endif

                    {{-- Right: Details --}}
                    <div class="card-body w-75">
                        <h5 class="card-title">{{ $type->name }}</h5>
                        <p class="card-text">{{ $type->description }}</p>
                        <p class="fw-bold">Price: RM {{ number_format($type->price_per_night, 2) }}</p>

                        <form action="{{ route('bookings.create', $type->id) }}" method="GET">
                            <button type="submit" class="btn btn-primary">Book Now</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif

    </div>
@endsection