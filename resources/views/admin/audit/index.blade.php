@extends('layouts.admin')

@section('title', 'Audit Log')

@section('content')
<h1>Audit Log</h1>

<form method="GET" action="{{ route('admin.audit.index') }}" style="margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap">
    <select name="event" style="padding:6px 10px;border:1px solid #ccc;border-radius:4px">
        <option value="">All Events</option>
        @foreach($distinctEvents as $evt)
            <option value="{{ $evt }}" @selected(request('event') === $evt)>{{ $evt }}</option>
        @endforeach
    </select>
    <input type="text" name="user_id" placeholder="User ID" value="{{ request('user_id') }}"
           style="padding:6px 10px;border:1px solid #ccc;border-radius:4px;width:120px">
    <button type="submit" style="padding:6px 16px;background:#333;color:#fff;border:none;border-radius:4px;cursor:pointer">Filter</button>
    <a href="{{ route('admin.audit.index') }}" style="padding:6px 10px;color:#666">Reset</a>
</form>

<div class="card">
    <table style="width:100%;font-size:0.85rem;border-collapse:collapse">
        <thead>
            <tr style="background:#f0f0f0">
                <th style="padding:8px 10px;text-align:left">Time</th>
                <th style="padding:8px 10px;text-align:left">Event</th>
                <th style="padding:8px 10px;text-align:left">User</th>
                <th style="padding:8px 10px;text-align:left">Subject</th>
                <th style="padding:8px 10px;text-align:left">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr style="border-bottom:1px solid #eee">
                <td style="padding:6px 10px;white-space:nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td style="padding:6px 10px"><code>{{ $log->event }}</code></td>
                <td style="padding:6px 10px">{{ $log->user?->name ?? '(system)' }}</td>
                <td style="padding:6px 10px;font-size:0.8rem;color:#666">
                    {{ $log->subject_type ? class_basename($log->subject_type) . '#' . $log->subject_id : '—' }}
                </td>
                <td style="padding:6px 10px;color:#999">{{ $log->ip_address ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:12px 10px;color:#999">No audit logs found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">
    {{ $logs->links() }}
</div>
@endsection
