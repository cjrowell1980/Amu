# Local Development

## Prerequisites

- Docker Desktop (or Docker Engine + Docker Compose)
- No local PHP or Node required (everything runs in containers)

## Quick start

```bash
# 1. Clone and configure
cp .env.example .env

# 2. Start all services
docker compose up -d

# 3. Install dependencies and generate key
docker compose exec app composer install
docker compose exec app php artisan key:generate

# 4. Run migrations and seed
docker compose exec app php artisan migrate --seed

# 5. Open the app
open http://localhost:8000

# Admin panel
open http://localhost:8000/admin
# admin@example.com / password
# operator@example.com / password
```

## Services

| Service  | URL                        | Credentials         |
|----------|----------------------------|---------------------|
| App      | http://localhost:8000      |                     |
| Admin    | http://localhost:8000/admin| admin@example.com   |
| Telescope| http://localhost:8000/telescope |                |
| Horizon  | http://localhost:8000/horizon   |                |
| Reverb   | ws://localhost:8080        |                     |
| MariaDB  | localhost:3306             | user/password (see .env) |
| Redis    | localhost:6379             |                     |

## Useful commands

```bash
# Run tests
docker compose exec app php artisan test

# Run specific test
docker compose exec app php artisan test tests/Feature/Lifecycle/RoomLifecycleTest.php

# Fresh migration with seed
docker compose exec app php artisan migrate:fresh --seed

# Tail logs
docker compose logs -f app

# Open tinker REPL
docker compose exec app php artisan tinker

# Generate IDE helpers (if installed)
docker compose exec app php artisan ide-helper:generate
```

## Environment variables (key ones)

```
APP_ENV=local
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=amu
DB_USERNAME=amu
DB_PASSWORD=password

REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=reverb
REVERB_PORT=8080
```

## Running tests locally

```bash
# All tests
docker compose exec app php artisan test

# With coverage (requires pcov/xdebug)
docker compose exec app php artisan test --coverage

# Filter by test name
docker compose exec app php artisan test --filter="can_create_room"
```

## Adding a new game module (quick reference)

1. Create `app/Modules/YourGame/YourGameModule.php` extending `AbstractGameModule`
2. Register in `AppServiceProvider::registerGameModules()`
3. Add DB row in `GameSeeder.php` with `availability => 'hidden'`
4. Run `php artisan migrate:fresh --seed`
5. See [game-module-guide.md](game-module-guide.md) for full details
