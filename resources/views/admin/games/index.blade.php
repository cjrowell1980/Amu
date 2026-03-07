@extends('layouts.admin')

@section('title', 'Games')

@section('content')
<h1>Game Registry ({{ $games->count() }})</h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Slug</th>
                <th>Name</th>
                <th>Players</th>
                <th>Teams</th>
                <th>Rooms</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($games as $game)
            <tr>
                <td>{{ $game->id }}</td>
                <td><code>{{ $game->slug }}</code></td>
                <td>{{ $game->name }}</td>
                <td>{{ $game->min_players }}–{{ $game->max_players }}</td>
                <td>{{ $game->supports_teams ? 'Yes' : 'No' }}</td>
                <td>{{ $game->rooms_count }}</td>
                <td>
                    @if($game->trashed())
                        <span class="badge badge-gray">Deleted</span>
                    @elseif($game->enabled)
                        <span class="badge badge-green">Enabled</span>
                    @else
                        <span class="badge badge-red">Disabled</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.games.show', $game) }}" class="btn btn-primary btn-sm">View</a>
                    @if(!$game->trashed())
                    <form method="POST" action="{{ route('admin.games.toggle', $game) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="background:#e5e7eb">
                            {{ $game->enabled ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
