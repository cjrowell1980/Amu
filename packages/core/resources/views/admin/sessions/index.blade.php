@extends('core::admin.layout')

@section('content')
    <h2>Sessions</h2>
    <table>
        <thead>
        <tr>
            <th>UUID</th>
            <th>Room</th>
            <th>Module</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($sessions as $session)
            <tr>
                <td>{{ $session->uuid }}</td>
                <td>{{ $session->room->name }}</td>
                <td>{{ $session->module->name }}</td>
                <td>{{ $session->status->value }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
