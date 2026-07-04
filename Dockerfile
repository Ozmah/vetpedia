FROM dunglas/frankenphp:php8.5-bookworm AS runtime-base

RUN install-php-extensions \
    pcntl \
    pdo_sqlite \
    sqlite3 \
    sockets \
    zip

ARG UID=1000
ARG GID=1000

RUN groupadd --gid "${GID}" vetpedia \
    && useradd --uid "${UID}" --gid vetpedia --shell /bin/sh --create-home vetpedia \
    && mkdir -p \
        /app \
        /app-data \
        /data \
        /config \
        /home/vetpedia/.cache/composer \
        /home/vetpedia/.bun \
    && chown -R vetpedia:vetpedia \
        /app \
        /app-data \
        /data \
        /config \
        /home/vetpedia

ENV HOME=/home/vetpedia \
    COMPOSER_CACHE_DIR=/home/vetpedia/.cache/composer

COPY Caddyfile /etc/caddy/Caddyfile

WORKDIR /app


FROM runtime-base AS development

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=oven/bun:1 /usr/local/bin/bun /usr/local/bin/bun

RUN ln -s /usr/local/bin/bun /usr/local/bin/bunx \
    && ln -s /usr/local/bin/bun /usr/local/bin/node

USER vetpedia

COPY --chown=vetpedia:vetpedia composer.json composer.lock* ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --optimize-autoloader

COPY --chown=vetpedia:vetpedia package.json bun.lock* ./

RUN bun install

# Worker mode is intentionally disabled during the bootstrap phase.
# Enable it only after the application lifecycle and deployment needs are clear.
# ENV FRANKENPHP_CONFIG="worker ./public/index.php"

COPY --chown=vetpedia:vetpedia . /app

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

ENTRYPOINT ["sh", "/app/docker/entrypoint.sh"]


FROM runtime-base AS php-vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

USER vetpedia

COPY --chown=vetpedia:vetpedia composer.json composer.lock* ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --no-autoloader

COPY --chown=vetpedia:vetpedia . /app

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative


FROM php-vendor AS frontend-assets

USER root

COPY --from=oven/bun:1 /usr/local/bin/bun /usr/local/bin/bun

RUN ln -s /usr/local/bin/bun /usr/local/bin/bunx \
    && ln -s /usr/local/bin/bun /usr/local/bin/node

USER vetpedia

RUN bun install --frozen-lockfile

RUN ./node_modules/.bin/vp build


FROM runtime-base AS production

USER vetpedia

COPY --chown=vetpedia:vetpedia . /app
COPY --from=php-vendor --chown=vetpedia:vetpedia /app/vendor /app/vendor
COPY --from=php-vendor --chown=vetpedia:vetpedia /app/bootstrap/cache /app/bootstrap/cache
COPY --from=frontend-assets --chown=vetpedia:vetpedia /app/public/build /app/public/build

ENTRYPOINT ["sh", "/app/docker/entrypoint.sh"]
