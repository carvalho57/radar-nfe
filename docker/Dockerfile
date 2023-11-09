FROM php:8.1.24-apache

RUN apt-get update && apt-get install -y \
    curl \
    zip \
    vim \
    unzip \
    gzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo pdo_mysql && a2enmod rewrite

WORKDIR /var/www/html