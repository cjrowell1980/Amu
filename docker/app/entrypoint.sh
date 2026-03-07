#!/bin/sh
set -e

# ---------------------------------------------------------------------------
# Configuration guard
# Fail fast with a clear message rather than letting Laravel crash cryptically.
# ---------------------------------------------------------------------------

# 1. Ensure .env exists
if [ ! -f /var/www/.env ]; then
    echo ""
    echo "ERROR: /var/www/.env not found."
    echo ""
    echo "  Copy .env.example to .env and set the required values:"
    echo "    cp .env.example .env"
    echo "    # then set APP_KEY, DB_*, REDIS_*, REVERB_* etc."
    echo ""
    exit 1
fi

# 2. Ensure APP_KEY is set (Laravel will throw a blank-screen error without it)
APP_KEY_VALUE=$(grep -E '^APP_KEY=' /var/www/.env | cut -d '=' -f2- | tr -d '[:space:]')
if [ -z "$APP_KEY_VALUE" ]; then
    echo ""
    echo "ERROR: APP_KEY is not set in .env."
    echo ""
    echo "  Generate one with:"
    echo "    php artisan key:generate"
    echo "  or inside the container:"
    echo "    docker compose exec app php artisan key:generate"
    echo ""
    exit 1
fi

# ---------------------------------------------------------------------------
# Deferred Laravel bootstrap
# These commands require .env + APP_KEY and cannot run at image build time.
# ---------------------------------------------------------------------------

echo "--> Running php artisan package:discover ..."
php artisan package:discover --ansi

echo "--> Caching config, routes, and views ..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---------------------------------------------------------------------------
# Hand off to the main process (php-fpm, horizon, reverb, etc.)
# ---------------------------------------------------------------------------
exec "$@"
