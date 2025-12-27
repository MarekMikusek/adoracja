# Use the official PHP image with FPM
FROM php:8.3-fpm

# Install required system packages
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Install Laravel globally
RUN composer global require laravel/installer

# Set permissions
RUN chown -R www-data:www-data /var/www

CMD ["php-fpm"]
