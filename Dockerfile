FROM php:8.2-cli

# تثبيت الأدوات الأساسية و PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    zip \
    libxml2-dev \
    libonig-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libpng-dev \
    libicu-dev \
    zlib1g-dev

# تثبيت الامتدادات المطلوبة
RUN docker-php-ext-install pdo pdo_mysql mbstring tokenizer xml zip intl

# تثبيت MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# تثبيت Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# إعداد مجلد العمل
WORKDIR /app

# نسخ كل الملفات
COPY . .

# تشغيل composer install
RUN composer install --no-interaction --optimize-autoloader

# فتح البورت
EXPOSE 8000

# تشغيل التطبيق
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]
