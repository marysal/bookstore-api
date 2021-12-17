#FROM phpdockerio/php73-fpm:latest
FROM php:7.3-fpm
WORKDIR "/application"

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install \
               pdo \
               pdo_pgsql \
               pgsql

RUN pecl install xdebug-2.8.1 && docker-php-ext-enable xdebug

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install  php7.3-pgsql \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install git
RUN apt-get update \
    && apt-get -y install git \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
