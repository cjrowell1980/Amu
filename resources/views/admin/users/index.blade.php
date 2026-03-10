@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<h1>Users ({{ $users->total() }})</h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Display Name</th>
                <th>Roles</th>
                <th>Registered</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->profile?->display_name ?? '—' }}</td>
                <td>
                    @foreach($user->getRoleNames() as $role)
                        <span class="badge badge-blue">{{ $role }}</span>
                    @endforeach
                </td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td><a href="{{ route('admin.users.show', $user) }}" class="btn btn-primary btn-sm">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination">
        {{ $users->links('pagination::simple-tailwind') }}
    </div>
</div>
@endsection
