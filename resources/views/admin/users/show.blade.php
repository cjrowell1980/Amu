@extends('layouts.admin')

@section('title', 'User: ' . $user->name)

@section('content')
<h1>{{ $user->name }}</h1>

<div class="card">
    <h2>Account</h2>
    <table>
        <tr><th>ID</th><td>{{ $user->id }}</td></tr>
        <tr><th>Email</th><td>{{ $user->email }}</td></tr>
        <tr><th>Display Name</th><td>{{ $user->profile?->display_name ?? '—' }}</td></tr>
        <tr><th>Status</th><td>{{ $user->profile?->status ?? '—' }}</td></tr>
        <tr><th>Roles</th><td>
            @foreach($user->getRoleNames() as $role)
                <span class="badge badge-blue">{{ $role }}</span>
            @endforeach
        </td></tr>
        <tr><th>Registered</th><td>{{ $user->created_at->format('Y-m-d H:i') }}</td></tr>
    </table>
</div>

<div class="card">
    <h2>Hosted Rooms ({{ $user->hostedRooms->count() }})</h2>
    @if($user->hostedRooms->isEmpty())
        <p style="color:#888">No rooms hosted yet.</p>
    @else
        <table>
            <thead><tr><th>Code</th><th>Game</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
                @foreach($user->hostedRooms as $room)
                <tr>
                    <td>{{ $room->code }}</td>
                    <td>{{ $room->game?->name ?? '—' }}</td>
                    <td><span class="badge badge-gray">{{ $room->status }}</span></td>
                    <td>{{ $room->created_at->format('Y-m-d') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="card">
    <h2>Session Participations ({{ $user->sessionParticipations->count() }})</h2>
    @if($user->sessionParticipations->isEmpty())
        <p style="color:#888">No sessions yet.</p>
    @else
        <table>
            <thead><tr><th>Session UUID</th><th>Game</th><th>Role</th><th>Rank</th><th>Score</th></tr></thead>
            <tbody>
                @foreach($user->sessionParticipations as $p)
                <tr>
                    <td><code>{{ substr($p->session->uuid, 0, 8) }}…</code></td>
                    <td>{{ $p->session->game?->name ?? '—' }}</td>
                    <td>{{ $p->role }}</td>
                    <td>{{ $p->final_rank ?? '—' }}</td>
                    <td>{{ $p->score ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
