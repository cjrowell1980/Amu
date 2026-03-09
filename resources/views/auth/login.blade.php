<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Amu Platform</title>
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
            --error-bg: #fde9e3;
            --error-ink: #8f2d16;
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
            display: grid;
            place-items: center;
            padding: 1.5rem;
        }
        .shell {
            width: min(100%, 420px);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(40, 32, 24, 0.14);
            overflow: hidden;
        }
        .hero {
            padding: 1.5rem 1.5rem 1rem;
            background: linear-gradient(135deg, rgba(183, 79, 45, 0.12), rgba(255, 250, 241, 0));
            border-bottom: 1px solid var(--line);
        }
        .eyebrow {
            margin: 0 0 0.5rem;
            font-size: 0.75rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--accent-dark);
        }
        h1 {
            margin: 0;
            font-size: 2rem;
            line-height: 1;
        }
        .hero p {
            margin: 0.75rem 0 0;
            color: var(--muted);
            font-size: 0.98rem;
            line-height: 1.5;
        }
        form {
            padding: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.45rem;
            font-size: 0.9rem;
            font-weight: 700;
        }
        .field {
            margin-bottom: 1rem;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 0.9rem 1rem;
            font: inherit;
            background: #fff;
        }
        input:focus {
            outline: 2px solid rgba(183, 79, 45, 0.2);
            border-color: var(--accent);
        }
        .error {
            margin-bottom: 1rem;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            background: var(--error-bg);
            color: var(--error-ink);
            font-size: 0.92rem;
        }
        .checkbox {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.25rem;
            color: var(--muted);
            font-size: 0.92rem;
        }
        .checkbox label {
            margin: 0;
            font-weight: 400;
        }
        button {
            width: 100%;
            border: 0;
            border-radius: 999px;
            padding: 0.95rem 1.2rem;
            font: inherit;
            font-weight: 700;
            color: #fff9f2;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            cursor: pointer;
        }
        button:hover {
            filter: brightness(1.03);
        }
        .hint {
            padding: 0 1.5rem 1.5rem;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.5;
        }
        .hint code {
            font-family: "Courier New", monospace;
            color: var(--ink);
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="hero">
            <p class="eyebrow">Amu Admin Access</p>
            <h1>Sign In</h1>
            <p>Use an operator, moderator, or admin account to access the management console.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>

            <div class="checkbox">
                <input id="remember" type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <label for="remember">Keep me signed in on this browser</label>
            </div>

            <button type="submit">Enter Admin Console</button>
        </form>

        <div class="hint">
            Default seeded accounts use <code>admin@example.com</code> or <code>operator@example.com</code> unless overridden by deployment environment values.
        </div>
    </div>
</body>
</html>
