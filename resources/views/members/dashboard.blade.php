<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Area - Amu Platform</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f3efe6;
            --panel: #fffaf1;
            --ink: #1d2a35;
            --muted: #586774;
            --line: #d7ccbc;
            --accent: #b74f2d;
            --accent-dark: #873513;
            --badge: #e7edf8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(183, 79, 45, 0.16), transparent 30%),
                linear-gradient(135deg, #efe8dc 0%, #f8f4ed 100%);
        }
        .page {
            width: min(1120px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 1.5rem 0 3rem;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .brand {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent-dark);
            text-decoration: none;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .button,
        .ghost {
            display: inline-block;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            text-decoration: none;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.55);
            color: var(--ink);
        }
        .button {
            color: #fff8f1;
            border-color: transparent;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 1.25rem;
            box-shadow: 0 18px 40px rgba(40, 32, 24, 0.08);
        }
        .kicker {
            margin: 0 0 0.5rem;
            font-size: 0.78rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--accent-dark);
        }
        h1, h2 {
            margin: 0 0 0.75rem;
        }
        p {
            margin: 0 0 1rem;
            line-height: 1.6;
            color: var(--muted);
        }
        .roles {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            background: var(--badge);
            font-size: 0.85rem;
        }
        form { margin: 0; }
        button {
            border: 0;
            cursor: pointer;
            font: inherit;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <a class="brand" href="{{ route('home') }}">Amu Members</a>
            <div class="actions">
                <a class="ghost" href="{{ route('pages.show', 'games') }}">Public Games Page</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ghost">Sign Out</button>
                </form>
            </div>
        </div>

        <div class="grid">
            <section class="card">
                <p class="kicker">Members Area</p>
                <h1>Welcome back, {{ $user->profile?->display_name ?? $user->name }}</h1>
                <p>
                    This is the authenticated landing area for members. From here you can move into the
                    player experience, and if your account has administrative access you can also open the
                    management console.
                </p>

                <div class="roles">
                    @forelse($user->getRoleNames() as $role)
                        <span class="badge">{{ $role }}</span>
                    @empty
                        <span class="badge">no roles assigned</span>
                    @endforelse
                </div>
            </section>

            <section class="card">
                <p class="kicker">Play</p>
                <h2>Player Access</h2>
                <p>
                    Your account stays in the normal member flow even if it also has admin permissions.
                    Admins can still play games and access the same member routes as everyone else.
                </p>
                <a class="button" href="{{ route('pages.show', 'games') }}">Browse Games</a>
            </section>

            @if($canAccessAdmin)
                <section class="card">
                    <p class="kicker">Administration</p>
                    <h2>Admin Area</h2>
                    <p>
                        Your account has administrative access. Use the admin area to manage users, roles,
                        modules, rooms, sessions, and operational tooling.
                    </p>
                    <a class="button" href="{{ route('admin.dashboard') }}">Open Admin Area</a>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
