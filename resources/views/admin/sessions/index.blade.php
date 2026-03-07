@extends('layouts.admin')

@section('title', 'Sessions')

@section('content')
<h1>Game Sessions ({{ $sessions->total() }})</h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>UUID</th>
                <th>Game</th>
                <th>Room</th>
                <th>Status</th>
                <th>Participants</th>
                <th>Started</th>
                <th>Ended</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $session)
            <tr>
                <td><code>{{ substr($session->uuid, 0, 8) }}…</code></td>
                <td>{{ $session->game?->name ?? '—' }}</td>
                <td><code>{{ $session->room?->code ?? '—' }}</code></td>
                <td>
                    @php $color = match($session->status) {
                        'active' => 'green', 'completed' => 'blue',
                        'abandoned' => 'red', 'created' => 'yellow', default => 'gray'
                    }; @endphp
                    <span class="badge badge-{{ $color }}">{{ $session->status }}</span>
                </td>
                <td>{{ $session->participants_count }}</td>
                <td>{{ $session->started_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td>{{ $session->ended_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td><a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-primary btn-sm">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination">{{ $sessions->links() }}</div>
</div>
@endsection
