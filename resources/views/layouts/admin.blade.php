<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Amu Platform</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f5f5f5; color: #222; }
        .nav { background: #1a1a2e; color: #eee; display: flex; align-items: center; gap: 1.5rem; padding: 0.75rem 1.5rem; }
        .nav a { color: #ccc; text-decoration: none; font-size: 0.9rem; }
        .nav a:hover { color: #fff; }
        .nav .brand { color: #fff; font-weight: 700; font-size: 1.1rem; margin-right: auto; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        h1 { margin-bottom: 1.25rem; font-size: 1.5rem; }
        h2 { margin-bottom: 0.75rem; font-size: 1.2rem; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.08); padding: 1.25rem; margin-bottom: 1.25rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.08); padding: 1rem 1.25rem; }
        .stat .value { font-size: 2rem; font-weight: 700; color: #1a1a2e; }
        .stat .label { font-size: 0.8rem; color: #666; margin-top: 0.25rem; text-transform: uppercase; letter-spacing: 0.05em; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { text-align: left; padding: 0.6rem 0.75rem; background: #f0f0f0; border-bottom: 2px solid #ddd; }
        td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #eee; }
        tr:hover td { background: #fafafa; }
        .badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .btn { display: inline-block; padding: 0.4rem 0.9rem; border-radius: 5px; font-size: 0.85rem; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: #1a1a2e; color: #fff; }
        .btn-sm { padding: 0.25rem 0.6rem; font-size: 0.78rem; }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .pagination { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .pagination a, .pagination span { padding: 0.35rem 0.7rem; border-radius: 4px; font-size: 0.85rem; text-decoration: none; border: 1px solid #ddd; }
        .pagination .active { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
        a { color: #1a1a2e; }
    </style>
</head>
<body>
<nav class="nav">
    <span class="brand">Amu Admin</span>
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('admin.users.index') }}">Users</a>
    <a href="{{ route('admin.games.index') }}">Games</a>
    <a href="{{ route('admin.rooms.index') }}">Rooms</a>
    <a href="{{ route('admin.sessions.index') }}">Sessions</a>
    @hasrole('admin|operator')
    <a href="{{ route('admin.audit.index') }}">Audit</a>
    @endhasrole
    <a href="/telescope" target="_blank">Telescope</a>
    <a href="/horizon" target="_blank">Horizon</a>
    <form method="POST" action="{{ route('logout') }}" style="margin-left: auto;">
        @csrf
        <button type="submit" class="btn btn-sm btn-primary">Sign Out</button>
    </form>
</nav>

<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @yield('content')
</div>
</body>
</html>
