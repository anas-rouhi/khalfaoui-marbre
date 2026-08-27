FROM php:8.2-fpm

# تثبيت المكتبات وإضافات PHP
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev \
    libicu-dev libzip-dev zip nodejs npm

RUN docker-php-ext-install pdo_mysql intl zip bcmath

# جلب Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# تثبيت حزم PHP و Node
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs
RUN npm install && npm run build

# تجهيز الـ Storage والـ Assets د Filament
RUN php artisan storage:link || true
RUN php artisan filament:assets || true

EXPOSE 8000

# أمر التشغيل النهائي
CMD php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}