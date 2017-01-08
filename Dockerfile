FROM php:7.0-apache
RUN apt-get update
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get install -y git unzip php5-gd
RUN a2enmod rewrite
COPY userfrosting /var/www/userfrosting
RUN mv /var/www/userfrosting/config-userfrosting-example.php /var/www/userfrosting/config-userfrosting.php
RUN perl -p -i.bak -e 's/mysql/sqlite/' /var/www/userfrosting/config-userfrosting.php
RUN cd /var/www/userfrosting && composer install --no-dev
COPY public/ /var/www/html
RUN touch /var/www/html/userfrosting && chown www-data:www-data /var/www/html/userfrosting
