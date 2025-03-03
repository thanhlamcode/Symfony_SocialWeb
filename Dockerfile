# Chọn PHP 8.3 với Apache
FROM php:8.3-apache

# Cài đặt các extension cần thiết
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git curl libpng-dev \
    && docker-php-ext-install pdo pdo_pgsql gd \
    && pecl install apcu \
    && docker-php-ext-enable apcu

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Chuyển vào thư mục Symfony
WORKDIR /var/www/html
COPY . .

# Cài đặt Symfony
RUN composer install --no-dev --optimize-autoloader

# Thiết lập quyền cho cache & logs
RUN chown -R www-data:www-data /var/www/html/var/cache /var/www/html/var/log

# Expose cổng 8000
EXPOSE 8000

# Chạy Symfony
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
