@extends('layouts.admin')

@section('title', 'User: ' . $user->name)

@section('content')
<h1>{{ $user->name }}</h1>

<div class="card">
    <h2>Account</h2>
    <table>
        <tr><th>ID</th><td>{{ $user->id }}</td></tr>
        <tr><th>Email</th><td>{{ $user->email }}</td></tr>
        <tr><th>Display Name</th><td>{{ $user->profile?->display_name ?? '—' }}</td></tr>
        <tr><th>Status</th><td>{{ $user->profile?->status ?? '—' }}</td></tr>
        <tr><th>Roles</th><td>
            @foreach($user->getRoleNames() as $role)
                <span class="badge badge-blue">{{ $role }}</span>
            @endforeach
        </td></tr>
        <tr><th>Registered</th><td>{{ $user->created_at->format('Y-m-d H:i') }}</td></tr>
    </table>
</div>

<div class="card">
    <h2>Roles</h2>
    <form method="POST" action="{{ route('admin.users.roles.update', $user) }}">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:0.75rem;margin-bottom:1rem;">
            @foreach($roles as $role)
                <label style="display:flex;align-items:center;gap:0.5rem;padding:0.75rem;border:1px solid #ddd;border-radius:8px;">
                    <input
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->name }}"
                        @checked($user->hasRole($role->name))
                    >
                    <span>{{ $role->name }}</span>
                </label>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Save Roles</button>
    </form>
</div>
@endsection
