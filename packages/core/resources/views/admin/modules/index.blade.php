@extends('core::admin.layout')

@section('content')
    <h2>Installed Modules</h2>
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Version</th>
            <th>Enabled</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($modules as $module)
            <tr>
                <td>{{ $module->name }}</td>
                <td>{{ $module->slug }}</td>
                <td>{{ $module->version }}</td>
                <td>{{ $module->enabled ? 'Yes' : 'No' }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.modules.toggle', $module) }}">
                        @csrf
                        <button type="submit">{{ $module->enabled ? 'Disable' : 'Enable' }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
