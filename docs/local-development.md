# Local Development

## Quick start

```bash
cp .env.example .env
docker compose up -d
docker compose exec app php artisan migrate --seed
```

Open:

- `http://localhost:8000`
- `http://localhost:8000/admin`

Default seeded admin:

- `admin@example.com` / `password`

## Compose files

- Use `docker-compose.yml` as the default local stack.
- `docker-compose.example.yml` is a reference/example file only.
- Do not point Portainer at `docker-compose.example.yml`.

## Package workflow

- The default stack builds the application into the containers instead of bind-mounting the repo.
- If you want a bind-mounted workflow for experimentation, use `docker-compose.example.yml` as a reference and create your own local override.
- If `vendor/` is missing, the app entrypoint runs `composer install` automatically.
- If you change Composer dependencies, rerun `docker compose exec app composer install`.

## Useful commands

```bash
docker compose exec app php artisan test
docker compose exec app php artisan test packages/blackjack/tests/Feature/BlackjackGameplayTest.php
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan tinker
docker compose logs -f app
```

## Services

- App: `http://localhost:8000`
- Admin: `http://localhost:8000/admin`
- Reverb: `ws://localhost:8080`
- MariaDB: `localhost:3306`
- Redis: `localhost:6379`

## Adding a new game package

1. Create `packages/your-game` as a Composer package.
2. Add the package to the root `composer.json` via a path repository.
3. Implement a package service provider and a concrete core `GameModule`.
4. Keep game-specific migrations, routes, models, and services inside that package.
5. Register the provider in `bootstrap/providers.php`.
6. Follow [game-module-guide.md](game-module-guide.md) for the expected structure.
