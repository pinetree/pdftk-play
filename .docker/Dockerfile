FROM php:7.1-fpm

MAINTAINER Descamps Antoine <antoine.descamps@ineat-conseil.fr>

RUN apt-get update && mkdir -p /usr/share/man/man1mkdir -p /usr/share/man/man1 \
  && apt-get install -y pdftk && apt-get install -y \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN mkdir -p ~/work && chmod 755 ~/work
RUN chown www-data ~/work
USER www-data
