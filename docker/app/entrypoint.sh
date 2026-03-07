#!/bin/sh
set -e

# Run deferred composer scripts now that .env and APP_KEY are available.
# package:discover registers service providers from all installed packages.
php artisan package:discover --ansi

# Optimise config/routes/views for production (safe to re-run).
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
