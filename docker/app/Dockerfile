ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-fpm

ARG APP_UID=1000
ARG APP_GID=1000

# Set environment variables for non-interactive installations
ENV DEBIAN_FRONTEND=noninteractive
ENV NPM_CONFIG_CACHE=/tmp/npm-cache

# Update and install all necessary system dependencies in a single RUN command
RUN apt-get update && apt-get install -y apt-utils curl git unzip gnupg2 \
    libpq-dev \
    libicu-dev \
    libkrb5-dev \
    libssl-dev \
    libxslt1-dev \
    libxml2-dev \
    locales \
    zlib1g-dev \
    libzip-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libjpeg-dev \
    libmemcached-dev \
    zip \
    default-mysql-client \
    # Clean up the package cache to reduce image size
    && rm -rf /var/lib/apt/lists/*

# Add NodeJS 24 Repository and install nodejs
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install XDebug via PECL
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN pecl install memcached redis \
    && docker-php-ext-enable memcached redis

# Install Composer
RUN curl -sSfo /tmp/composer.phar https://getcomposer.org/installer \
    && php /tmp/composer.phar --install-dir=/usr/local/bin --filename=composer

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) pcntl pdo pdo_mysql pdo_pgsql zip

WORKDIR /app

RUN groupmod --non-unique --gid "${APP_GID}" www-data \
    && usermod --non-unique --uid "${APP_UID}" --gid "${APP_GID}" www-data \
    && mkdir -p /app/node_modules \
    && chown www-data:www-data /app/node_modules

USER www-data
