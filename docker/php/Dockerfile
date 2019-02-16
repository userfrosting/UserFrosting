FROM php:7.2-fpm-alpine

# Update and install packages
RUN apk update
RUN apk add \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    zip

# Install and configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql \
    && docker-php-ext-install -j$(nproc) zip

WORKDIR /app