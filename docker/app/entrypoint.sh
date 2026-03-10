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

# 1b. Sync DB password from shared runtime secret when DB_PASSWORD is blank.
DB_PASSWORD_VALUE=$(grep -E '^DB_PASSWORD=' /var/www/.env | cut -d '=' -f2- | tr -d '[:space:]')
DB_PASSWORD_NORMALIZED=$(printf '%s' "$DB_PASSWORD_VALUE" | tr '[:upper:]' '[:lower:]')
DB_PASSWORD_SECRET_FILE=/run/amu-secrets/db_password
if { [ -z "$DB_PASSWORD_VALUE" ] || [ "$DB_PASSWORD_NORMALIZED" = "null" ]; } \
    && [ -f "$DB_PASSWORD_SECRET_FILE" ] && [ -s "$DB_PASSWORD_SECRET_FILE" ]; then
    DB_PASSWORD_SECRET=$(cat "$DB_PASSWORD_SECRET_FILE")
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD_SECRET/" /var/www/.env
    echo "Injected DB_PASSWORD into /var/www/.env from shared secret store."
fi

# 1c. Ensure APP_KEY is stable.
# Priority:
#   1) APP_KEY from environment (stack.env / Portainer UI)
#   2) persisted secret in /run/amu-secrets/app_key
#   3) generated once and persisted to /run/amu-secrets/app_key
set_env_value() {
    KEY="$1"
    VALUE="$2"
    FILE="$3"
    ESCAPED_VALUE=$(printf '%s' "$VALUE" | sed 's/[&|]/\\&/g')
    if grep -q "^${KEY}=" "$FILE"; then
        sed -i "s|^${KEY}=.*|${KEY}=${ESCAPED_VALUE}|" "$FILE"
    else
        printf '\n%s=%s\n' "$KEY" "$VALUE" >> "$FILE"
    fi
}

is_blank_or_null() {
    VALUE="${1:-}"
    if [ -z "$VALUE" ]; then
        return 0
    fi
    NORMALIZED=$(printf '%s' "$VALUE" | tr -d '[:space:]' | tr '[:upper:]' '[:lower:]')
    [ "$NORMALIZED" = "null" ]
}

APP_KEY_SECRET_FILE=/run/amu-secrets/app_key
APP_KEY_SOURCE=""

if ! is_blank_or_null "${APP_KEY:-}"; then
    APP_KEY_EFFECTIVE="$APP_KEY"
    APP_KEY_SOURCE="environment"
    printf '%s' "$APP_KEY_EFFECTIVE" > "$APP_KEY_SECRET_FILE"
elif [ -f "$APP_KEY_SECRET_FILE" ] && [ -s "$APP_KEY_SECRET_FILE" ]; then
    APP_KEY_EFFECTIVE="$(cat "$APP_KEY_SECRET_FILE")"
    APP_KEY_SOURCE="secret store"
else
    APP_KEY_EFFECTIVE="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    APP_KEY_SOURCE="generated secret"
    printf '%s' "$APP_KEY_EFFECTIVE" > "$APP_KEY_SECRET_FILE"
fi

set_env_value "APP_KEY" "$APP_KEY_EFFECTIVE" /var/www/.env
echo "Ensured APP_KEY in /var/www/.env from $APP_KEY_SOURCE."

# 1d. Reassert writable runtime paths on every start.
# Portainer exec sessions often run as root for maintenance commands, which can
# leave log/cache files owned by root and break Laravel's runtime logging.
mkdir -p /var/www/storage/logs \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R ug+rwX /var/www/storage /var/www/bootstrap/cache

# 2. Ensure dependencies are present when using bind mounts.
# Docker bind mounts can hide image-built /var/www/vendor.
if [ ! -f /var/www/vendor/autoload.php ]; then
    echo ""
    echo "WARN: /var/www/vendor/autoload.php not found. Running composer install ..."
    echo ""
    COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --prefer-dist
fi

# 3. Ensure APP_KEY is set (fail fast if APP_KEY provisioning failed)
APP_KEY_VALUE=$(grep -E '^APP_KEY=' /var/www/.env | cut -d '=' -f2- | tr -d '[:space:]')
if [ -z "$APP_KEY_VALUE" ]; then
    echo ""
    echo "ERROR: APP_KEY is empty after startup provisioning."
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
