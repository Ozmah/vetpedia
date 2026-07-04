#!/usr/bin/env sh

set -eu

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    database_path="${DB_DATABASE:-/app-data/vetpedia.sqlite}"

    mkdir -p "$(dirname "$database_path")"

    if [ ! -f "$database_path" ]; then
        touch "$database_path"
    fi
fi

if [ "${VETPEDIA_RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec frankenphp run --config /etc/caddy/Caddyfile
