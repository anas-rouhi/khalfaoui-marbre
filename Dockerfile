# Step 1: Build Assets with Node
FROM node:18 AS build-assets
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Step 2: Set up PHP & Nginx Environment
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx git unzip curl libpng-dev libonig-dev libxml2-dev \
    libicu-dev libzip-dev zip

RUN docker-php-ext-install pdo_mysql intl zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=build-assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Nginx Configuration
RUN echo 'server {\n\
    listen 8000;\n\
    root /var/www/html/public;\n\
    index index.php index.html;\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
}' > /etc/nginx/sites-available/default

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

CMD php-fpm -D && php artisan storage:link --force && php artisan filament:assets && php artisan migrate --force && nginx -g 'daemon off;'