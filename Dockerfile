FROM php:8.3-apache

WORKDIR /var/www/html

RUN a2enmod rewrite