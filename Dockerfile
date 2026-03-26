FROM php:8.3-fpm-alpine

# Arguments pour les versions (facile à maintenir)
ENV NGINX_VERSION=1.24.0
ENV PHP_INI_DIR=/usr/local/etc/php

# Install system dependencies & PHP extensions in one layer
RUN apk add --no-cache \
    nginx \
    wget \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    icu-dev \
    postgresql-dev \
    shadow \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_pgsql pgsql gd zip intl bcmath opcache

# Copy project
WORKDIR /var/www/html
COPY . .

# Configuration PHP personnalisée (On va créer ce fichier juste après)
COPY ./docker/php/custom.ini $PHP_INI_DIR/conf.d/custom.ini

# Install Composer & NPM
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Build Assets (Vite / Mix)
RUN apk add --no-cache nodejs npm \
    && npm install \
    && npm run build \
    && apk del nodejs npm
# On supprime node après le build pour alléger l'image

# Setup Nginx
COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf

# Permissions Laravel (Plus robuste avec chmod)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# Script de démarrage optimisé
CMD ["sh", "-c", "php artisan migrate --force && php artisan optimize:clear && php artisan storage:link && php artisan db:seed --force && (php-fpm -D && nginx -g 'daemon off;')"]
