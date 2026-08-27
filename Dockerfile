FROM php:8.2-fpm

# تثبيت مكتبات النظام الإضافية
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev \
    libicu-dev libzip-dev zip

# تثبيت إضافات PHP المطلوبة
RUN docker-php-ext-install pdo_mysql intl zip bcmath

# جلب Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# تثبيت الحزم وتجاهل قيود المنصة
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}