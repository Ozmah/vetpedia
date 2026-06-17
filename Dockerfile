FROM dunglas/frankenphp:php8.5-bookworm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=oven/bun:1 /usr/local/bin/bun /usr/local/bin/bun

RUN ln -s /usr/local/bin/bun /usr/local/bin/bunx

RUN install-php-extensions \
    pcntl \
    pdo_sqlite \
    sqlite3 \
    sockets \
    zip

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --optimize-autoloader

COPY package.json bun.lock* ./

RUN bun install

# Worker mode is intentionally disabled during the bootstrap phase.
# Enable it only after the application lifecycle and deployment needs are clear.
# ENV FRANKENPHP_CONFIG="worker ./public/index.php"

COPY . /app

RUN mkdir -p database storage bootstrap/cache \
    && touch database/vetpedial.sqlite

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

ENTRYPOINT ["bun", "x", "varlock", "run", "--", "frankenphp", "run", "--config", "/app/Caddyfile"]
