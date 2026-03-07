@extends('layouts.admin')

@section('title', 'Session ' . substr($session->uuid, 0, 8))

@section('content')
<h1>Session <code>{{ $session->uuid }}</code></h1>

<div class="card">
    <table>
        <tr><th>Game</th><td>{{ $session->game?->name }}</td></tr>
        <tr><th>Room</th><td><code>{{ $session->room?->code }}</code></td></tr>
        <tr><th>Status</th><td><span class="badge badge-gray">{{ $session->status }}</span></td></tr>
        <tr><th>Started</th><td>{{ $session->started_at?->format('Y-m-d H:i:s') ?? '—' }}</td></tr>
        <tr><th>Ended</th><td>{{ $session->ended_at?->format('Y-m-d H:i:s') ?? '—' }}</td></tr>
        <tr><th>Config</th><td><pre>{{ json_encode($session->session_config, JSON_PRETTY_PRINT) }}</pre></td></tr>
        <tr><th>Result</th><td><pre>{{ json_encode($session->result_summary, JSON_PRETTY_PRINT) }}</pre></td></tr>
    </table>
</div>

<div class="card">
    <h2>Participants ({{ $session->participants->count() }})</h2>
    <table>
        <thead><tr><th>User</th><th>Role</th><th>Team</th><th>Rank</th><th>Score</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($session->participants as $p)
            <tr>
                <td>{{ $p->user?->name }}</td>
                <td>{{ $p->role }}</td>
                <td>{{ $p->team_number ?? '—' }}</td>
                <td>{{ $p->final_rank ?? '—' }}</td>
                <td>{{ $p->score ?? '—' }}</td>
                <td><span class="badge badge-gray">{{ $p->connection_status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
