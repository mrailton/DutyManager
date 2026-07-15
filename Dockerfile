FROM node:23-alpine AS node

WORKDIR /app

COPY package.json package-lock.json .npmrc* ./

RUN npm ci --ignore-scripts --no-audit --no-fund && \
    npm rebuild && \
    npm cache clean --force

COPY . .

RUN npm run build

FROM composer/composer:2-bin AS composer-bin

FROM php:8.5-fpm-alpine AS vendor

RUN docker-php-ext-install -j$(nproc) pdo_mysql bcmath

COPY --from=composer-bin /composer /usr/bin/composer

RUN apk add --no-cache git unzip

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader --audit

FROM php:8.5-fpm-alpine

RUN set -eux; \
    apk add --no-cache \
        nginx \
        supervisor \
        curl \
    ; \
    docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        bcmath

COPY --from=vendor /app/vendor /var/www/html/vendor
COPY --from=node /app/public/build /var/www/html/public/build

COPY . /var/www/html

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini $PHP_INI_DIR/conf.d/99-production.ini

WORKDIR /var/www/html

RUN php artisan package:discover --ansi

RUN set -eux; \
    chown -R www-data:www-data \
        storage \
        bootstrap/cache \
        public/build \
    ; \
    chmod -R 775 storage bootstrap/cache

EXPOSE 80

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
