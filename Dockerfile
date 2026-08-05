FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

FROM nginx:1.27-alpine AS web
WORKDIR /var/www/html
COPY public ./public
COPY --from=frontend /app/public/build ./public/build
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
RUN ln -s /var/www/html/storage/app/public /var/www/html/public/storage

FROM composer:2 AS dependencies
WORKDIR /app
RUN docker-php-ext-install exif
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        icu-libs libpng libjpeg-turbo freetype libzip oniguruma \
    && apk add --no-cache --virtual .build-deps \
        icu-dev libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev oniguruma-dev linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) bcmath exif gd intl mbstring opcache pcntl pdo_mysql zip \
    && apk del .build-deps

WORKDIR /var/www/html
COPY . .
COPY --from=dependencies /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY docker/php.ini /usr/local/etc/php/conf.d/production.ini
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint
RUN BROADCAST_CONNECTION=log php artisan package:discover --ansi \
    && chmod +x /usr/local/bin/app-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]
