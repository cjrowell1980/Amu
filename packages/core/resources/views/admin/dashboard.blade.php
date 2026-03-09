@extends('core::admin.layout')

@section('content')
    <div class="stats">
        <div class="card">
            <strong>Installed Modules</strong>
            <div>{{ $moduleCount }}</div>
        </div>
        <div class="card">
            <strong>Active Rooms</strong>
            <div>{{ $activeRoomCount }}</div>
        </div>
        <div class="card">
            <strong>Sessions</strong>
            <div>{{ $sessionCount }}</div>
        </div>
    </div>
@endsection
