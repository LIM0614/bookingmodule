{{-- resources/views/bookings/cancel.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container py-4">
        @if($booking->status === 'cancelled')
            <div class="alert alert-secondary">
                This booking has been cancelled.
            </div>
            {{-- 提示完就不继续渲染后面的编辑/取消表单 --}}
            @return
        @endif

        {{-- 1. 如果有 error，就显示可关闭的警告框 --}}
        @if(session('error'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" aria-label="Close"
                    onclick="window.location='{{ route('bookings.index') }}';"></button>
            </div>
        @endif

        {{-- 2. 标题 --}}
        <h2 class="mb-4 text-danger">⚠️ Cancel Booking</h2>
        <p>Are you sure you want to cancel the following booking? This action cannot be undone.</p>

        {{-- 3. 预订详情 --}}
        <ul class="list-group mb-4">
            <li class="list-group-item">
                <strong>Booking ID：</strong> {{ $booking->id }}
            </li>
            <li class="list-group-item">
                <strong>Guest：</strong> {{ $booking->user->name }}
            </li>
            <li class="list-group-item">
                <strong>Room：</strong> {{ $booking->room->name }}
            </li>
            <li class="list-group-item">
                <strong>Check‑In：</strong> {{ $booking->check_in_date }}
            </li>
            <li class="list-group-item">
                <strong>Check‑Out：</strong> {{ $booking->check_out_date }}
            </li>
            <li class="list-group-item">
                <strong>Status：</strong> {{ ucfirst($booking->status) }}
            </li>
        </ul>

        {{-- 4. 确认取消按钮 --}}
        <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger">
                Yes, Cancel Booking
            </button>
        </form>

        {{-- 5. 返回按钮 --}}
        <a href="{{ route('bookings.index') }}" class="btn btn-secondary">
            No, Go Back
        </a>
    </div>
@endsection