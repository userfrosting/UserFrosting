FROM php:7.2-fpm

RUN apt-get update

RUN apt-get install -y apt-utils

# Add NodeJS LTS Repository
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash -

RUN apt-get install -y \
    libc-client-dev \
    libicu-dev \
    libkrb5-dev \
    libssl-dev \
    libxslt1-dev \
    libxml2-dev \
    locales \
    zlib1g-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg-dev \
    zip \
    nodejs

# COMPOSER INSTALL
RUN curl -sSfo /tmp/composer.phar https://getcomposer.org/installer
RUN php /tmp/composer.phar --install-dir=/usr/local/bin --filename=composer
# Force Composer Stable Version 1
RUN composer selfupdate --1

# Install and configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql \
    && docker-php-ext-install -j$(nproc) zip

WORKDIR /app
