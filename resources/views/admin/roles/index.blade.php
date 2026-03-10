@extends('layouts.admin')

@section('title', 'Roles')

@section('content')
<h1>Roles</h1>

<div class="card">
    <h2>Create Role</h2>
    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        <div style="display:grid;gap:1rem;">
            <label>
                <div style="margin-bottom:0.35rem;font-weight:600;">Role Name</div>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    style="width:100%;padding:0.7rem;border:1px solid #ddd;border-radius:6px;"
                    required
                >
            </label>

            <div>
                <div style="margin-bottom:0.5rem;font-weight:600;">Permissions</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:0.6rem;">
                    @foreach($permissions as $permission)
                        <label style="display:flex;align-items:center;gap:0.5rem;padding:0.7rem;border:1px solid #ddd;border-radius:8px;">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}">
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">Create Role</button>
            </div>
        </div>
    </form>
</div>

@foreach($roles as $role)
    <div class="card">
        <h2>{{ $role->name }}</h2>
        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf
            @method('PUT')

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:0.6rem;margin-bottom:1rem;">
                @foreach($permissions as $permission)
                    <label style="display:flex;align-items:center;gap:0.5rem;padding:0.7rem;border:1px solid #ddd;border-radius:8px;">
                        <input
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->name }}"
                            @checked($role->permissions->contains('name', $permission->name))
                        >
                        <span>{{ $permission->name }}</span>
                    </label>
                @endforeach
            </div>

            <button type="submit" class="btn btn-primary">Save Permissions</button>
        </form>
    </div>
@endforeach
@endsection
