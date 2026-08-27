FROM php:8.2-cli

# 1. تثبيت الحزم وإضافات PHP
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev \
    libicu-dev libzip-dev zip

RUN docker-php-ext-install pdo_mysql intl zip bcmath

# 2. جلب Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# 3. تثبيت حزم PHP
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 4. إنشاء مجلدات storage وتحديد الصلاحيات الكاملة (حل مشكل 500 نهائياً)
RUN mkdir -p storage/logs \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache

EXPOSE 8000

# 5. أمر التشغيل المباشر مع توجيه الـ Public
CMD ["sh", "-c", "php artisan storage:link --force && php artisan filament:assets --force && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]