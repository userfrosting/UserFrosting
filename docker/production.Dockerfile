FROM node:24-bookworm AS frontend

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    python3 \
    g++ \
    make \
    && rm -rf /var/lib/apt/lists/*

COPY . .
RUN npm ci --no-audit --no-fund \
    && npm run vite:build

FROM php:8.5-fpm AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmemcached-dev \
    libpng-dev \
    libzip-dev \
    zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql zip \
    && pecl install memcached-3.4.0 \
    && docker-php-ext-enable memcached \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && mkdir -p app/cache app/logs app/sessions app/storage \
    && chown -R www-data:www-data app/cache app/logs app/sessions app/storage

COPY --from=frontend /app/public/assets/.vite/manifest.json /app/public/assets/.vite/manifest.json
COPY docker/app/php/production.ini /usr/local/etc/php/conf.d/production.ini

FROM nginx:alpine AS web

COPY docker/nginx/default.production.conf /etc/nginx/conf.d/default.conf
COPY --from=frontend /app/public /app/public
