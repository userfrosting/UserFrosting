# Use the official PHP 8.2 FPM image
FROM php:8.2-fpm

# Set environment variables for non-interactive installations
ENV DEBIAN_FRONTEND=noninteractive

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
    zip \
    default-mysql-client \
    # Clean up the package cache to reduce image size
    && rm -rf /var/lib/apt/lists/*

# Add NodeJS 20 Repository and install nodejs
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install XDebug via PECL
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer
RUN curl -sSfo /tmp/composer.phar https://getcomposer.org/installer \
    && php /tmp/composer.phar --install-dir=/usr/local/bin --filename=composer

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql zip

WORKDIR /app
