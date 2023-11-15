FROM php:8.1.24-apache

RUN apt-get update && apt-get install -y \
    curl \
    zip \
    vim \
    unzip \
    gzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo pdo_mysql && a2enmod rewrite

RUN echo "\n[openssl_init]\nproviders = provider_sect\n[provider_sect]\ndefault = default_sect\nlegacy = legacy_sect\n[default_sect]\nactivate = 1\n[legacy_sect]\nactivate = 1" >> /etc/ssl/openssl.cnf

WORKDIR /var/www/html