FROM php:8.1.24-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    curl \
    zip \
    vim \
    unzip \
    gzip \
    zlib1g-dev \
    libxml2-dev \
    libssl-dev \
    libicu-dev \
    cron

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo pdo_mysql soap intl  && a2enmod rewrite

RUN echo "\n[openssl_init]\nproviders = provider_sect\n[provider_sect]\ndefault = default_sect\nlegacy = legacy_sect\n[default_sect]\nactivate = 1\n[legacy_sect]\nactivate = 1" >> /etc/ssl/openssl.cnf

RUN mkdir /var/log/cron
RUN (crontab -l ; echo "0 */3 * * * /usr/local/bin/php /var/www/html/scripts/radar.php >> /var/log/cron/cron.log 2>&1") | crontab

CMD cron && apache2-foreground