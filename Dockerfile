# --- Étape 1 : compilation des assets front (Vite) ---
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY resources resources
COPY vite.config.js ./

RUN npm run build

# --- Étape 2 : application PHP ---
FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor curl zip unzip git libpq-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql opcache gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=assets /app/public/build public/build

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
