ARG PHP_VERSION=8.5
FROM node:24-bookworm AS frontend

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    python3 \
    g++ \
    make \
    && rm -rf /var/lib/apt/lists/*

COPY . .
RUN if [ -f package-lock.json ]; then \
        npm ci --no-audit --no-fund; \
    else \
        npm install --no-audit --no-fund; \
    fi \
    && npm install-scripts approve vue-demi --all --no-allow-scripts-pin \
    && npm install --no-audit --no-fund \
    && npm run vite:build

FROM php:${PHP_VERSION}-fpm AS app

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmemcached-dev \
    libpng-dev \
    libzip-dev \
    libpq-dev \
    zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_pgsql zip \
    && pecl install memcached-3.4.0 \
    && pecl install redis \
    && docker-php-ext-enable memcached redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && mkdir -p app/cache app/logs app/sessions app/storage \
    && groupmod --non-unique --gid "${APP_GID}" www-data \
    && usermod --non-unique --uid "${APP_UID}" --gid "${APP_GID}" www-data \
    && chown -R www-data:www-data app/cache app/logs app/sessions app/storage

COPY --from=frontend /app/public/assets/.vite/manifest.json /app/public/assets/.vite/manifest.json
COPY docker/app/php/production.ini /usr/local/etc/php/conf.d/production.ini
USER www-data

FROM nginxinc/nginx-unprivileged:alpine AS web

COPY docker/nginx/default.production.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY --from=frontend /app/public /app/public
USER nginx
