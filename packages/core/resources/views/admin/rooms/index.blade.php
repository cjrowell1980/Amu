@extends('core::admin.layout')

@section('content')
    <h2>Rooms</h2>
    <table>
        <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Module</th>
            <th>Status</th>
            <th>Visibility</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($rooms as $room)
            <tr>
                <td>{{ $room->code }}</td>
                <td>{{ $room->name }}</td>
                <td>{{ $room->module->name }}</td>
                <td>{{ $room->status->value }}</td>
                <td>{{ $room->visibility->value }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
