#!/bin/sh
set -eu

SECRETS_DIR="/run/amu-secrets"
DB_PASSWORD_FILE="$SECRETS_DIR/db_password"
DB_ROOT_PASSWORD_FILE="$SECRETS_DIR/db_root_password"

mkdir -p "$SECRETS_DIR"

generate_password() {
    tr -dc 'A-Za-z0-9' </dev/urandom | head -c 32
}

# Root password: use provided value, else persisted/generated value.
if [ -n "${DB_ROOT_PASSWORD:-}" ]; then
    ROOT_PASSWORD="$DB_ROOT_PASSWORD"
    printf '%s' "$ROOT_PASSWORD" > "$DB_ROOT_PASSWORD_FILE"
elif [ -f "$DB_ROOT_PASSWORD_FILE" ] && [ -s "$DB_ROOT_PASSWORD_FILE" ]; then
    ROOT_PASSWORD="$(cat "$DB_ROOT_PASSWORD_FILE")"
else
    ROOT_PASSWORD="$(generate_password)"
    printf '%s' "$ROOT_PASSWORD" > "$DB_ROOT_PASSWORD_FILE"
    echo "Generated random DB_ROOT_PASSWORD (saved to $DB_ROOT_PASSWORD_FILE)."
fi

# App DB password: use provided value, else persisted/generated value.
if [ -n "${DB_PASSWORD:-}" ]; then
    APP_DB_PASSWORD="$DB_PASSWORD"
    printf '%s' "$APP_DB_PASSWORD" > "$DB_PASSWORD_FILE"
elif [ -f "$DB_PASSWORD_FILE" ] && [ -s "$DB_PASSWORD_FILE" ]; then
    APP_DB_PASSWORD="$(cat "$DB_PASSWORD_FILE")"
else
    APP_DB_PASSWORD="$(generate_password)"
    printf '%s' "$APP_DB_PASSWORD" > "$DB_PASSWORD_FILE"
    echo "Generated random DB_PASSWORD (saved to $DB_PASSWORD_FILE)."
fi

export MARIADB_ROOT_PASSWORD="$ROOT_PASSWORD"
export MARIADB_DATABASE="${DB_DATABASE:-amu}"
export MARIADB_USER="${DB_USERNAME:-amu}"
export MARIADB_PASSWORD="$APP_DB_PASSWORD"

exec docker-entrypoint.sh "$@"
