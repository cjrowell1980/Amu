@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<h1>Platform Dashboard</h1>

<div class="stats-grid">
    <div class="stat">
        <div class="value">{{ number_format($stats['users']) }}</div>
        <div class="label">Total Users</div>
    </div>
    <div class="stat">
        <div class="value">{{ number_format($stats['games']) }}</div>
        <div class="label">Registered Games</div>
    </div>
    <div class="stat">
        <div class="value">{{ number_format($stats['active_rooms']) }}</div>
        <div class="label">Active Rooms</div>
    </div>
    <div class="stat">
        <div class="value">{{ number_format($stats['active_sessions']) }}</div>
        <div class="label">Active Sessions</div>
    </div>
</div>

<div class="card">
    <h2>Quick Links</h2>
    <p style="margin-top:0.5rem;font-size:0.9rem;color:#555;">
        Use the navigation above to inspect users, games, rooms, and sessions.
        Use <a href="/telescope" target="_blank">Telescope</a> for request/query inspection
        and <a href="/horizon" target="_blank">Horizon</a> for queue monitoring.
    </p>
</div>
@endsection
