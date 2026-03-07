@extends('layouts.admin')

@section('title', 'Game: ' . $game->name)

@section('content')
<h1>{{ $game->name }} <code style="font-size:0.75rem;font-weight:400">({{ $game->slug }})</code></h1>

<div class="card">
    <table>
        <tr><th>Status</th><td>
            @if($game->enabled)<span class="badge badge-green">Enabled</span>
            @else<span class="badge badge-red">Disabled</span>@endif
        </td></tr>
        <tr><th>Description</th><td>{{ $game->description ?? '—' }}</td></tr>
        <tr><th>Module Class</th><td><code>{{ $game->module_class ?? 'Not bound' }}</code></td></tr>
        <tr><th>Version</th><td>{{ $game->version ?? '—' }}</td></tr>
        <tr><th>Players</th><td>{{ $game->min_players }} – {{ $game->max_players }}</td></tr>
        <tr><th>Teams</th><td>{{ $game->supports_teams ? 'Yes' : 'No' }}</td></tr>
        <tr><th>Play Count</th><td>{{ number_format($game->play_count) }}</td></tr>
        <tr><th>Default Config</th><td><pre>{{ json_encode($game->default_config, JSON_PRETTY_PRINT) }}</pre></td></tr>
    </table>
</div>

<div class="card">
    <h2>Rooms ({{ $game->rooms->count() }})</h2>
    @if($game->rooms->isEmpty())
        <p style="color:#888">No rooms for this game yet.</p>
    @else
        <table>
            <thead><tr><th>Code</th><th>Status</th><th>Visibility</th><th>Created</th></tr></thead>
            <tbody>
                @foreach($game->rooms as $room)
                <tr>
                    <td>{{ $room->code }}</td>
                    <td><span class="badge badge-gray">{{ $room->status }}</span></td>
                    <td>{{ $room->visibility }}</td>
                    <td>{{ $room->created_at->format('Y-m-d') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
