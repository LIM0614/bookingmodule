@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Bookings</h2>
        </div>

        @if($bookings->isEmpty())
            <div class="alert alert-info">
                You have no bookings yet.
            </div>
        @else
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Check‑In</th>
                        <th>Check‑Out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $b)
                            @php
                                // 转换成 Carbon 实例，方便判断是否是“今天”
                                $ci = \Carbon\Carbon::parse($b->check_in_date);
                            @endphp
                            <tr>
                                <td>{{ $b->roomType->name}}</td>
                                <td>{{ $ci->format('d‑M‑Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($b->check_out_date)->format('d‑M‑Y') }}</td>
                                <td>{{ ucfirst($b->status) }}</td>
                                <td>
                                    <a href="{{ route('bookings.show', $b->id) }}" class="btn btn-sm btn-info me-1">
                                        View
                                    </a>

                                    {{-- 只有“pending”且不是今天，才允许编辑或取消 --}}
                                    @if($b->status === 'pending' && !$ci->isToday())
                                        <a href="{{ route('bookings.edit', $b->id) }}" class="btn btn-sm btn-primary me-1">
                                            Edit
                                        </a>
                                        <a href="{{ route('bookings.cancel.confirm', $b->id) }}" class="btn btn-sm btn-danger">
                                            Cancel
                                        </a>
                                    @endif
                                </td>
                            </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection