# ---- php dependencies ----
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev


# ---- frontend assets (needs vendor for flux blade sources) ----
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
RUN npm ci

COPY resources ./resources
COPY --from=vendor /app/vendor ./vendor
RUN npm run build


# ---- runtime ----
FROM serversideup/php:8.4-fpm-nginx AS app

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# never ship cached manifests from the build machine, they reference dev
# packages that do not exist in this image
RUN rm -f bootstrap/cache/*.php

USER www-data
