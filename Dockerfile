FROM php:8.2-cli

# تثبيت الأدوات الأساسية و PHP extensions المطلوبة للبناء والتشغيل
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
    zlib1g-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring xml zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# تثبيت MongoDB extension عبر PECL وتفعيله
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# تثبيت Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# إعداد مجلد العمل
WORKDIR /app

# نسخ كل الملفات للمجلد داخل الحاوية
COPY . .

# تثبيت تبعيات المشروع عبر composer
RUN composer install --no-interaction --optimize-autoloader

# فتح بورت 8080 (تأكد انه نفس البورت في CMD)
EXPOSE 8080

# تشغيل التطبيق على المضيف 0.0.0.0 والمنفذ 8080
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8080"]
