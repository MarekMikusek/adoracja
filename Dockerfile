# Stage 1: Base PHP image
FROM php:8.3-fpm

# Install system dependencies and PHP extensions prerequisites
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libsqlite3-dev \
    sqlite3 \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    gnupg \
    default-libmysqlclient-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Install Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js 20.x via NodeSource (recommended)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www

# Copy all application files to the working directory
# This needs to happen *before* composer install so artisan is available
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
COPY package.json package-lock.json 
RUN npm install && npm run build

# Create SQLite DB file if it doesn't exist and fix permissions
RUN mkdir -p database && touch database/database.sqlite && chmod -R 777 database

# Create symlink for image storage
RUN mkdir -p public/pict && ln -sf /var/www/resources/pict/adoracja.webp /var/www/public/pict/adoracja.webp

# Set ownership for Laravel files
RUN chown -R www-data:www-data /var/www

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Run PHP-FPM
CMD ["php-fpm"]
