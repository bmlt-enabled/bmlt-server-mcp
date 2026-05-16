# syntax=docker/dockerfile:1.7
FROM dunglas/frankenphp:1-php8.3 AS base

ENV SERVER_NAME=":8080"
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pcntl \
    opcache \
    intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-progress \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && php artisan storage:link || true \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s \
    CMD curl -fsS http://localhost:8080/up || exit 1

CMD ["frankenphp", "php-server", \
     "--listen", ":8080", \
     "--root", "/app/public", \
     "--access-log"]
