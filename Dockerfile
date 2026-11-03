# 0. بناء ملفات الواجهة (Vite) : public/build غير مُدرج في Git.
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

FROM php:8.3-cli

# 1. تثبيت الحزم وإضافات PHP
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
    libonig-dev libxml2-dev libicu-dev libzip-dev zip \
    && rm -rf /var/lib/apt/lists/*

# GD : نسخ WebP للصور المرفوعة ومصغرات PDF عروض الأسعار.
# calendar : مطلوبة من إحدى حزم Composer (composer check-platform-reqs).
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo_mysql intl zip bcmath gd calendar

# 2. جلب Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
COPY --from=assets /app/public/build ./public/build

# 3. تثبيت حزم PHP
RUN composer install --no-dev --optimize-autoloader

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