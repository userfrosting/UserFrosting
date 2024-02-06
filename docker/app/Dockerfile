FROM php:8.2-fpm

RUN apt-get update

RUN apt-get install -y apt-utils

# Add NodeJS 18 Repository
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

RUN apt-get install -y \
    libc-client-dev \
    libicu-dev \
    libkrb5-dev \
    libssl-dev \
    libxslt1-dev \
    libxml2-dev \
    locales \
    zlib1g-dev \
    libzip-dev\
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libjpeg-dev \
    zip \
    nodejs

# Optional XDebug Install
#RUN curl -fsSL 'https://xdebug.org/files/xdebug-2.9.4.tgz' -o xdebug.tar.gz \
#    && mkdir -p xdebug \
#    && tar -xf xdebug.tar.gz -C xdebug --strip-components=1 \
#    && rm xdebug.tar.gz \
#    && ( \
#    cd xdebug \
#    && phpize \
#    && ./configure --enable-xdebug \
#    && make -j$(nproc) \
#    && make install \
#    ) \
#    && rm -r xdebug \
#    && docker-php-ext-enable xdebug   

# COMPOSER INSTALL
RUN curl -sSfo /tmp/composer.phar https://getcomposer.org/installer
RUN php /tmp/composer.phar --install-dir=/usr/local/bin --filename=composer

# Install and configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql \
    && docker-php-ext-install -j$(nproc) zip

WORKDIR /app