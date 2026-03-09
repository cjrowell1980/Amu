<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} | Amu</title>
    <meta name="description" content="{{ $page->meta_description ?: $page->hero_body }}">
    <style>
        :root {
            color-scheme: light;
            --bg: #f3ecdf;
            --surface: rgba(255, 249, 239, 0.84);
            --surface-strong: #fff8ee;
            --ink: #1b2330;
            --muted: #5e6875;
            --line: rgba(52, 42, 27, 0.12);
            --accent: #c5532f;
            --accent-deep: #7b2d14;
            --shadow: 0 24px 70px rgba(53, 35, 16, 0.14);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(197, 83, 47, 0.2), transparent 24%),
                linear-gradient(135deg, #efe4d2 0%, #f8f3ea 48%, #efe6d7 100%);
        }

        a { color: inherit; text-decoration: none; }

        .page {
            width: min(1120px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 1.25rem 0 3rem;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 0 1.5rem;
        }

        .brand { font-size: 1.2rem; font-weight: 700; }
        .brand span { color: var(--accent-deep); }

        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
            align-items: center;
        }

        .nav a,
        .button {
            padding: 0.75rem 1rem;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.42);
            transition: transform 160ms ease;
        }

        .nav a.active {
            color: #fff7ef;
            border-color: transparent;
            background: linear-gradient(135deg, var(--accent), var(--accent-deep));
        }

        .button {
            display: inline-block;
            color: #fff7ef;
            border-color: transparent;
            background: linear-gradient(135deg, var(--accent), var(--accent-deep));
            box-shadow: 0 14px 30px rgba(123, 45, 20, 0.22);
        }

        .nav a:hover,
        .button:hover {
            transform: translateY(-1px);
        }

        .hero,
        .content,
        .games-grid {
            border: 1px solid var(--line);
            border-radius: 30px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .hero {
            padding: 3rem;
            margin-bottom: 1.25rem;
        }

        .eyebrow {
            margin: 0 0 0.9rem;
            color: var(--accent-deep);
            letter-spacing: 0.15em;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        h1 {
            margin: 0;
            max-width: 11ch;
            font-size: clamp(2.8rem, 7vw, 5rem);
            line-height: 0.94;
            letter-spacing: -0.05em;
        }

        .hero p {
            max-width: 48rem;
            margin: 1.25rem 0 0;
            color: var(--muted);
            font-size: 1.08rem;
            line-height: 1.75;
        }

        .hero-actions {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .secondary {
            display: inline-block;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.48);
        }

        .content {
            padding: 1.8rem;
        }

        .copy {
            color: var(--muted);
            line-height: 1.85;
            font-size: 1.02rem;
        }

        .copy > :first-child {
            margin-top: 0;
        }

        .copy > :last-child {
            margin-bottom: 0;
        }

        .copy h1,
        .copy h2,
        .copy h3,
        .copy h4 {
            color: var(--ink);
            line-height: 1.1;
            margin: 1.5rem 0 0.7rem;
        }

        .copy p,
        .copy ul,
        .copy ol,
        .copy blockquote {
            margin: 0 0 1rem;
        }

        .copy ul,
        .copy ol {
            padding-left: 1.2rem;
        }

        .copy li + li {
            margin-top: 0.35rem;
        }

        .copy a {
            color: var(--accent-deep);
            text-decoration: underline;
            text-underline-offset: 0.14em;
        }

        .copy strong {
            color: var(--ink);
        }

        .copy blockquote {
            padding-left: 1rem;
            border-left: 3px solid rgba(123, 45, 20, 0.24);
            color: #4b5563;
        }

        .copy code {
            padding: 0.1rem 0.35rem;
            border-radius: 6px;
            background: rgba(27, 35, 48, 0.06);
            font-family: "Courier New", monospace;
            font-size: 0.92em;
        }

        .games-grid {
            margin-top: 1.25rem;
            padding: 1.4rem;
        }

        .games-grid h2 {
            margin: 0 0 1rem;
            font-size: 1.5rem;
        }

        .game-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .game-card {
            border-radius: 22px;
            padding: 1rem;
            background: var(--surface-strong);
            border: 1px solid rgba(52, 42, 27, 0.08);
        }

        .game-card strong {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 1.1rem;
        }

        .game-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
            font-size: 0.95rem;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            margin-top: 1rem;
            color: var(--muted);
            font-size: 0.92rem;
        }

        @media (max-width: 820px) {
            .topbar,
            .footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .hero {
                padding: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="topbar">
            <a class="brand" href="{{ route('home') }}">Amu <span>Platform</span></a>

            <nav class="nav">
                @foreach ($navPages as $navPage)
                    <a
                        href="{{ $navPage->path }}"
                        class="{{ $navPage->id === $page->id ? 'active' : '' }}"
                    >
                        {{ $navPage->nav_label }}
                    </a>
                @endforeach
                <a class="button" href="{{ route('login') }}">Admin Sign In</a>
            </nav>
        </header>

        <section class="hero">
            <p class="eyebrow">{{ $page->nav_label }}</p>
            <h1>{{ $page->hero_title }}</h1>
            <p>{{ $page->hero_body }}</p>

            <div class="hero-actions">
                @if ($page->cta_label && $page->cta_link)
                    <a class="button" href="{{ $page->cta_link }}">{{ $page->cta_label }}</a>
                @endif
                <a class="secondary" href="{{ route('login') }}">Operator Console</a>
            </div>
        </section>

        <section class="content">
            <div class="copy">{!! $page->rendered_body !!}</div>
        </section>

        @if ($page->slug === \App\Models\SitePage::SLUG_GAMES)
            <section class="games-grid">
                <h2>Current game registry</h2>
                <div class="game-list">
                    @forelse ($enabledGames as $game)
                        <article class="game-card">
                            <strong>{{ $game->name }}</strong>
                            <p>{{ $game->description ?: 'Description not yet added.' }}</p>
                        </article>
                    @empty
                        <article class="game-card">
                            <strong>No games published yet</strong>
                            <p>Add games through the admin area and they can be surfaced here when you are ready.</p>
                        </article>
                    @endforelse
                </div>
            </section>
        @endif

        <footer class="footer">
            <span>Public navigation and page copy are managed from the admin area.</span>
            <a href="{{ route('login') }}">Admin Sign In</a>
        </footer>
    </div>
</body>
</html>
