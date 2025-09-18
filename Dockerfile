FROM php:8.2-apache

# تثبيت إضافات PHP التي يحتاجها Laravel + Postgres
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip git curl libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql mbstring zip gd bcmath

# تفعيل mod_rewrite في Apache
RUN a2enmod rewrite

# نسخ المشروع
COPY . /var/www/html

# تثبيت Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# صلاحيات لازمة للـ storage/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# جعل DocumentRoot يشير إلى مجلد public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80
CMD ["apache2-foreground"]
