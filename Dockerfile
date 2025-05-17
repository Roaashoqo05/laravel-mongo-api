FROM php:8.2-apache

# تثبيت التبعيات المطلوبة
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libpng-dev \
    libicu-dev \
    zlib1g-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring xml zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# تثبيت MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# تفعيل mod_rewrite في Apache
RUN a2enmod rewrite

# إعداد مجلد العمل
WORKDIR /var/www/html

# نسخ كل الملفات للمجلد داخل الحاوية
COPY . .

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت التبعيات
RUN composer install --no-interaction --optimize-autoloader

# إعطاء صلاحيات للمجلدات المهمة
RUN chown -R www-data:www-data storage bootstrap/cache

# إعداد Apache للعمل مع مجلد public
COPY ./vhost.conf /etc/apache2/sites-available/000-default.conf

# فتح البورت 80
EXPOSE 80

CMD ["apache2-foreground"]
