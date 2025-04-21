<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>User</th>
            <th>Room</th>
            <th>Check‑In</th>
            <th>Check‑Out</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $b)
            <tr>
                <td>{{ $b->id }}</td>
                <td>{{ $b->user->name }}<br><small>{{ $b->user->email }}</small></td>
                <td>{{ $b->room->name }}</td>
                <td>{{ \Carbon\Carbon::parse($b->check_in_date)->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($b->check_out_date)->format('Y-m-d') }}</td>
                <td>{{ ucfirst($b->status) }}</td>
                <td>
                    <a href="{{ route('admin.bookings.show', $b->id) }}" class="btn btn-sm btn-info">View</a>
                    @if($b->status !== 'cancelled')
                        <form action="{{ route('admin.bookings.forceCancel', $b->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Force cancel?');">
                                Cancel
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>