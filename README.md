# Amu — Multiplayer Game Platform Core

A **production-ready Laravel 12 platform core** for building modular multiplayer games. The platform handles authentication, lobby management, session lifecycle, real-time events, and operator tooling. Game-specific logic lives in isolated, swappable modules.

## What this is

Amu is the **foundation layer** — not a game itself. It provides:

- Player authentication and profiles (Sanctum + Spatie Permission)
- Game registry with feature-flag-based availability control
- Room (lobby) lifecycle with status state machine
- Session lifecycle with reconnect scaffolding
- Real-time events via Laravel Reverb (WebSocket)
- Admin/operator panel (Blade)
- Audit logging
- Comprehensive test suite

## What this is NOT

- Not a game (no poker, blackjack, etc. — those are future modules)
- Not a SPA frontend (Blade for admin only)
- Not a microservice architecture (intentional Laravel monolith)

## Tech stack

| Component       | Technology                    |
|-----------------|-------------------------------|
| Framework       | Laravel 12                    |
| Language        | PHP 8.3+                      |
| Database        | MariaDB 11 / MySQL 8           |
| Cache / Queue   | Redis                         |
| WebSocket       | Laravel Reverb                |
| Queue monitor   | Laravel Horizon               |
| Debug           | Laravel Telescope             |
| Auth            | Laravel Sanctum               |
| Roles           | Spatie Laravel Permission     |
| Containers      | Docker + Docker Compose       |
| Admin UI        | Blade                         |

## Quick start

```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
# → http://localhost:8000
# → http://localhost:8000/admin  (admin@example.com / password)
```

## Documentation

| Doc | Description |
|-----|-------------|
| [docs/architecture.md](docs/architecture.md) | Overall architecture and directory layout |
| [docs/core-domain.md](docs/core-domain.md) | Entities, statuses, and domain rules |
| [docs/game-module-guide.md](docs/game-module-guide.md) | How to build a game module |
| [docs/api-overview.md](docs/api-overview.md) | REST API and WebSocket channel reference |
| [docs/local-development.md](docs/local-development.md) | Docker setup and dev workflow |
| [docs/installation-deployment.md](docs/installation-deployment.md) | Installation/deployment via CLI, Docker Desktop, and Portainer |

## After this phase

The primary remaining implementation work is:

1. **Game modules** — implement actual games (blackjack, poker, trivia, etc.) following `docs/game-module-guide.md`
2. **Player-facing UI** — SPA or mobile app consuming the REST API and WebSocket events
3. **Optional extras** — leaderboards, economy, social features
