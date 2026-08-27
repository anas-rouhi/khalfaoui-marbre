# 1️⃣ Step 1: Build Assets using Node 20
FROM node:20-alpine AS build-assets
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# 2️⃣ Step 2: PHP Setup
FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev \
    libicu-dev libzip-dev zip

RUN docker-php-ext-install pdo_mysql intl zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
COPY --from=build-assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

EXPOSE 8000

CMD php artisan storage:link --force && php artisan filament:assets && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}