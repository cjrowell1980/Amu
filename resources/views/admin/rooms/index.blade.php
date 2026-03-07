@extends('layouts.admin')

@section('title', 'Rooms')

@section('content')
<h1>Game Rooms ({{ $rooms->total() }})</h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Game</th>
                <th>Host</th>
                <th>Status</th>
                <th>Visibility</th>
                <th>Members</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($rooms as $room)
            <tr>
                <td><code>{{ $room->code }}</code></td>
                <td>{{ $room->name ?? '—' }}</td>
                <td>{{ $room->game?->name ?? '—' }}</td>
                <td>{{ $room->host?->name ?? '—' }}</td>
                <td>
                    @php $color = match($room->status) {
                        'waiting' => 'blue', 'in_game' => 'green', 'finished' => 'gray',
                        'cancelled' => 'red', default => 'gray'
                    }; @endphp
                    <span class="badge badge-{{ $color }}">{{ $room->status }}</span>
                </td>
                <td>{{ $room->visibility }}</td>
                <td>{{ $room->active_members_count }}</td>
                <td>{{ $room->created_at->format('Y-m-d') }}</td>
                <td><a href="{{ route('admin.rooms.show', $room) }}" class="btn btn-primary btn-sm">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination">{{ $rooms->links() }}</div>
</div>
@endsection
