@extends('layouts.admin')

@section('title', 'Room: ' . $room->code)

@section('content')
<h1>Room <code>{{ $room->code }}</code> — {{ $room->name ?? 'Unnamed' }}</h1>

<div class="card">
    <table>
        <tr><th>Game</th><td>{{ $room->game?->name }}</td></tr>
        <tr><th>Host</th><td>{{ $room->host?->name }}</td></tr>
        <tr><th>Status</th><td><span class="badge badge-gray">{{ $room->status }}</span></td></tr>
        <tr><th>Visibility</th><td>{{ $room->visibility }}</td></tr>
        <tr><th>Max Players</th><td>{{ $room->effectiveMaxPlayers() }}</td></tr>
        <tr><th>Has Password</th><td>{{ $room->hasPassword() ? 'Yes' : 'No' }}</td></tr>
        <tr><th>Spectators Allowed</th><td>{{ $room->allow_spectators ? 'Yes' : 'No' }}</td></tr>
        <tr><th>Created</th><td>{{ $room->created_at->format('Y-m-d H:i') }}</td></tr>
    </table>
</div>

<div class="card">
    <h2>Active Members</h2>
    @if($room->activeMembers->isEmpty())
        <p style="color:#888">No active members.</p>
    @else
        <table>
            <thead><tr><th>User</th><th>Role</th><th>Ready</th><th>Team</th><th>Joined</th></tr></thead>
            <tbody>
                @foreach($room->activeMembers as $member)
                <tr>
                    <td>{{ $member->user?->name }}</td>
                    <td>{{ $member->role }}</td>
                    <td>{{ $member->is_ready ? '✓' : '—' }}</td>
                    <td>{{ $member->team_number ?? '—' }}</td>
                    <td>{{ $member->joined_at->format('H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="card">
    <h2>Sessions ({{ $room->sessions->count() }})</h2>
    @if($room->sessions->isEmpty())
        <p style="color:#888">No sessions yet.</p>
    @else
        <table>
            <thead><tr><th>UUID</th><th>Status</th><th>Started</th><th>Ended</th><th></th></tr></thead>
            <tbody>
                @foreach($room->sessions as $session)
                <tr>
                    <td><code>{{ substr($session->uuid, 0, 8) }}…</code></td>
                    <td><span class="badge badge-gray">{{ $session->status }}</span></td>
                    <td>{{ $session->started_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ $session->ended_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td><a href="{{ route('admin.sessions.show', $session) }}" class="btn btn-primary btn-sm">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
