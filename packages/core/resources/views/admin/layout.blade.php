<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Platform Admin</title>
    <style>
        body { font-family: Georgia, serif; background: #f6f1e8; color: #1e1b18; margin: 0; }
        header, main { max-width: 960px; margin: 0 auto; padding: 24px; }
        nav a { margin-right: 16px; color: #5b2a1d; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; background: #fffdf8; }
        th, td { border-bottom: 1px solid #dfd2bf; padding: 12px; text-align: left; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin: 24px 0; }
        .card { background: linear-gradient(135deg, #fff8ef, #ecd6bd); padding: 18px; border: 1px solid #d9b78f; }
        button { background: #5b2a1d; color: #fff; border: 0; padding: 8px 12px; cursor: pointer; }
    </style>
</head>
<body>
<header>
    <h1>Platform Admin</h1>
    <nav>
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.modules.index') }}">Modules</a>
        <a href="{{ route('admin.rooms.index') }}">Rooms</a>
        <a href="{{ route('admin.sessions.index') }}">Sessions</a>
    </nav>
</header>
<main>
    @yield('content')
</main>
</body>
</html>
