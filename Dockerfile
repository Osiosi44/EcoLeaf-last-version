FROM php:8.4-apache
RUN docker-php-ext-install mysqli
COPY docker/apache.conf /etc/apache2/conf-enabled/ecoleaf.conf
WORKDIR /var/www/html
