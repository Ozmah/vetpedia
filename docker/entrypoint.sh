#!/usr/bin/env sh
set -eu

if [ -z "${DB_DATABASE:-}" ]; then
    echo "DB_DATABASE is required."
    exit 1
fi

mkdir -p "$(dirname "$DB_DATABASE")"

if [ ! -f "$DB_DATABASE" ]; then
    touch "$DB_DATABASE"
fi

php artisan migrate --force

exec frankenphp run --config /app/Caddyfile
