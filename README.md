# Amu

Amu is a Laravel 12 modular game platform built around a generic core package and installable game packages. The host app stays thin while game logic lives inside Composer packages under `packages/`.

## Current structure

- `packages/core`: module registry, rooms, sessions, participants, wallet/accounting, admin pages
- `packages/blackjack`: the first real game module, implemented as a self-contained Blackjack MVP package
- `app/`: host Laravel shell for auth, package loading, and app-level bootstrap

## Included today

- Generic module system with enable/disable support
- Generic room and session lifecycle
- Generic player participation and wallet transactions
- Basic admin pages for modules, rooms, and sessions
- Blackjack MVP gameplay through package-owned APIs

## Not included yet

- Advanced casino features such as split, double down, insurance, or side bets
- A polished player-facing frontend
- Additional game packages beyond Blackjack

## Quick start

```bash
cp .env.example .env
docker compose up -d
docker compose exec app php artisan migrate --seed
```

Then open:

- `http://localhost:8000`
- `http://localhost:8000/admin`

`docker-compose.yml` is the canonical compose file for both local use and Portainer Git-stack deployment.

`docker-compose.example.yml` is kept only as a reference example for a bind-mounted workflow.

## Useful commands

```bash
docker compose exec app php artisan test
docker compose exec app php artisan migrate:fresh --seed
docker compose logs -f app
```

## Docs

- [docs/game-module-guide.md](docs/game-module-guide.md)
- [docs/local-development.md](docs/local-development.md)
