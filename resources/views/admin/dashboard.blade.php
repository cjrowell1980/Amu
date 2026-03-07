@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<h1>Platform Dashboard</h1>

<div class="stats-grid">
    <div class="stat">
        <div class="value">{{ number_format($stats['total_users']) }}</div>
        <div class="label">Total Users</div>
    </div>
    <div class="stat">
        <div class="value">{{ number_format($stats['enabled_games']) }} / {{ number_format($stats['total_games']) }}</div>
        <div class="label">Enabled Games</div>
    </div>
    <div class="stat">
        <div class="value">{{ number_format($stats['waiting_rooms']) }}</div>
        <div class="label">Waiting Rooms</div>
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

<div class="card" style="margin-top:2rem">
    <h2>Quick Links</h2>
    <p style="margin-top:0.5rem;font-size:0.9rem;color:#555;">
        <a href="/telescope" target="_blank">Telescope</a> &nbsp;|&nbsp;
        <a href="/horizon" target="_blank">Horizon</a> &nbsp;|&nbsp;
        <a href="{{ route('admin.audit.index') }}">Audit Log</a>
    </p>
</div>

<div class="card" style="margin-top:2rem">
    <h2>Recent Activity</h2>
    <table style="width:100%;font-size:0.85rem;border-collapse:collapse;margin-top:0.75rem">
        <thead>
            <tr style="background:#f0f0f0">
                <th style="padding:6px 10px;text-align:left">Time</th>
                <th style="padding:6px 10px;text-align:left">Event</th>
                <th style="padding:6px 10px;text-align:left">User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentAuditLogs as $log)
            <tr style="border-bottom:1px solid #eee">
                <td style="padding:6px 10px;white-space:nowrap">{{ $log->created_at->diffForHumans() }}</td>
                <td style="padding:6px 10px"><code>{{ $log->event }}</code></td>
                <td style="padding:6px 10px">{{ $log->user?->name ?? '(system)' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="padding:12px 10px;color:#999">No activity yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
