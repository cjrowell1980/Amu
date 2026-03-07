#!/bin/sh
set -e

# ---------------------------------------------------------------------------
# Configuration guard
# Fail fast with a clear message rather than letting Laravel crash cryptically.
# ---------------------------------------------------------------------------

# 1. Ensure .env exists
if [ ! -f /var/www/.env ]; then
    echo ""
    echo "WARN: /var/www/.env not found."
    echo ""
    if [ -f /var/www/.env.example ]; then
        echo "  Creating /var/www/.env from .env.example ..."
        cp /var/www/.env.example /var/www/.env
    else
        echo "ERROR: /var/www/.env.example not found, cannot bootstrap environment."
        echo ""
        exit 1
    fi
fi

# 2. Ensure dependencies are present when using bind mounts.
# Docker bind mounts can hide image-built /var/www/vendor.
if [ ! -f /var/www/vendor/autoload.php ]; then
    echo ""
    echo "WARN: /var/www/vendor/autoload.php not found. Running composer install ..."
    echo ""
    COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --prefer-dist
fi

# 3. Ensure APP_KEY is set (Laravel will throw a blank-screen error without it)
APP_KEY_VALUE=$(grep -E '^APP_KEY=' /var/www/.env | cut -d '=' -f2- | tr -d '[:space:]')
if [ -z "$APP_KEY_VALUE" ]; then
    echo ""
    echo "WARN: APP_KEY is not set in .env. Generating one ..."
    echo ""
    php artisan key:generate --force --ansi
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
