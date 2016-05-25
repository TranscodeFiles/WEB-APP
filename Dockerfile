# Symfony
FROM php:7.0-apache

MAINTAINER Aymeric Daurelle <aymeric42680@gmail.com>

COPY vhost.conf /etc/apache2/sites-enabled/000-default.conf
COPY entrypoint.sh /entrypoint.sh
COPY . /var/www/html
COPY config/php.ini /usr/local/etc/php/

RUN apt-get update && \
    apt-get install -y \
        curl \
        git \
        unzip \
        zip \
        libicu-dev && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install pdo_mysql intl && \
    curl -o /usr/local/bin/composer https://getcomposer.org/composer.phar && \
    chmod +x /usr/local/bin/composer && \
    a2enmod rewrite && \
    apt-get clean

WORKDIR /var/www/html

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]