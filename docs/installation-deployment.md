# Installation & Deployment Guide (Docker)

This guide covers three deployment paths:

1. CLI (`docker compose`) on a Docker host
2. Docker Desktop
3. Portainer (Git-based stack)

It also documents environment variable overrides, port conflict handling, and VLAN network overrides.

## Prerequisites

- Docker Engine 24+ and Docker Compose v2+
- Git
- Open ports you plan to expose (`APP_PORT`, `DB_PORT`, `REDIS_PORT`, `REVERB_PORT`)

## Option 1: CLI Installation (Docker Host)

```bash
git clone https://github.com/cjrowell1980/Amu.git
cd Amu
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan migrate --seed --force
```

App URLs:

- `http://<host>:${APP_PORT}` (default `8000`)
- `http://<host>:${APP_PORT}/admin`

Default seeded users:

- `admin@example.com / password`
- `operator@example.com / password`

## Option 2: Docker Desktop

1. Clone repo.
2. Copy `.env.example` to `.env`.
3. Start stack from terminal:
   - `docker compose up -d --build`
4. Run migrations:
   - `docker compose exec app php artisan migrate --seed --force`
5. Open:
   - `http://localhost:8000`

## Option 3: Portainer Stack (Git)

1. In Portainer, create stack from Git repository.
2. Repository URL:
   - `https://github.com/cjrowell1980/Amu.git`
3. Compose path:
   - `docker-compose.yml`
4. Set stack env vars as needed (see table below).
5. Deploy stack.

After first deploy, run migrations from Portainer console on `amu_app`:

```bash
php artisan migrate --seed --force
```

## Environment Variables You Can Override

Use `.env` (CLI/Desktop) or stack environment variables (Portainer).

| Variable | Default | Purpose |
|---|---|---|
| `APP_ENV` | `local` | Laravel environment |
| `APP_DEBUG` | `true` | Debug mode |
| `APP_URL` | `http://localhost:8000` | Public app URL |
| `APP_PORT` | `8000` | Host HTTP port mapped to nginx `80` |
| `DB_HOST` | `mysql` | DB host inside Docker network |
| `DB_PORT` | `3306` | Host DB port mapping |
| `DB_DATABASE` | `amu` | Database name |
| `DB_USERNAME` | `amu` | Database user |
| `DB_PASSWORD` | (blank) | Database user password; if blank, generated randomly and stored in runtime secret volume |
| `DB_ROOT_PASSWORD` | (blank) | MariaDB root password; if blank, generated randomly and stored in runtime secret volume |
| `REDIS_HOST` | `redis` | Redis host inside Docker network |
| `REDIS_PORT` | `6379` | Host Redis port mapping |
| `REVERB_HOST` | `reverb` | Reverb host inside Docker network |
| `REVERB_PORT` | `8080` | Host WebSocket port mapping |
| `REVERB_APP_ID` | `amu` | Reverb app ID |
| `REVERB_APP_KEY` | `amu-key` | Reverb app key |
| `REVERB_APP_SECRET` | `amu-secret` | Reverb app secret |
| `ADMIN_EMAIL` | `admin@example.com` | Seeded admin user |
| `ADMIN_PASSWORD` | `password` | Seeded admin password |
| `CLOUDFLARED_TUNNEL_TOKEN` | (empty) | Cloudflared tunnel token (token-based tunnel mode) |
| `CLOUDFLARED_LOGLEVEL` | `info` | Cloudflared log level (`debug`, `info`, `warn`, `error`) |
| `EXTERNAL_NETWORK_NAME` | `ipvlan-10` | External Docker network name used by ipvlan override compose |
| `NGINX_STATIC_IP` | (blank) | Optional static IP for nginx on external network |
| `CLOUDFLARED_STATIC_IP` | (blank) | Optional static IP for cloudflared on external network |

### Cloudflared profile

`cloudflared` runs only when compose profile `tunnel` is enabled.

- CLI:
  - `COMPOSE_PROFILES=tunnel docker compose up -d`
- Portainer:
  - Enable profile `tunnel` and set `CLOUDFLARED_TUNNEL_TOKEN`.

If profile `tunnel` is not enabled, tunnel service is ignored.

Token-based mode does not require tunnel name in compose.  
If you want named-tunnel/cert-file mode later, that can be added as a separate profile.

## Port Already in Use

If deployment fails due to a port collision, override host ports:

```env
APP_PORT=8088
DB_PORT=3307
REDIS_PORT=6380
REVERB_PORT=8081
```

Then redeploy.

## Password Behavior (Requested)

- If `DB_PASSWORD` is blank: a random password is generated.
- If `DB_PASSWORD` is set: that value is used.
- If `DB_ROOT_PASSWORD` is blank: a random password is generated.
- If `DB_ROOT_PASSWORD` is set: that value is used.
- If your template UI does not allow empty values, string `null` is treated as blank.

Generated passwords are persisted in Docker volume `amu_runtime_secrets`:

- `/run/amu-secrets/db_password`
- `/run/amu-secrets/db_root_password`

## VLAN / External Docker Network (your `ipvlan-10`)

If you already have:

```yaml
networks:
  ipvlan10:
    name: ipvlan-10
    external: true
```

use included override file `docker-compose.ipvlan.yml`.

Deploy with external network attachment:

```bash
docker compose -f docker-compose.yml -f docker-compose.ipvlan.yml up -d --build
```

Optional static IP deployment (only when you set `NGINX_STATIC_IP` and `CLOUDFLARED_STATIC_IP`):

```bash
docker compose -f docker-compose.yml -f docker-compose.ipvlan.yml -f docker-compose.ipvlan-static.yml up -d --build
```

## Operational Commands

```bash
docker compose ps
docker compose logs --tail 200 app
docker compose logs --tail 200 nginx
docker compose logs --tail 200 mysql
docker compose exec app php artisan about
```
