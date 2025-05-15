# نستخدم صورة PHP الرسمية مع CLI ونسخة 8.1
FROM php:8.1-cli

# تحديث نظام التشغيل وتثبيت أدوات ضرورية مثل unzip، git، curl
RUN apt-get update && apt-get install -y unzip git curl libzip-dev zip

# تثبيت MongoDB PHP extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# تثبيت Composer (مدير الحزم لPHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# تعيين مجلد العمل داخل الحاوية
WORKDIR /app

# نسخ كل ملفات المشروع للحاوية
COPY . /app

# تثبيت اعتمادات PHP باستخدام Composer
RUN composer install --no-interaction --optimize-autoloader

# كشف المنفذ اللي التطبيق راح يشتغل عليه
EXPOSE 10000

# الأمر لتشغيل تطبيق Laravel
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "10000"]
