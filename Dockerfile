FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libsqlite3-dev \
    sqlite3

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (replacing pdo_pgsql with pdo_sqlite)
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Create symbolic link for pictures
RUN mkdir -p public/pict
RUN ln -s /var/www/resources/pict/adoracja.webp /var/www/public/pict/adoracja.webp

# Copy existing application directory
COPY . .

# Create SQLite DB file if it doesn't exist
RUN mkdir -p database && touch database/database.sqlite && chmod -R 777 database

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

RUN php artisan config:cache
RUN php artisan route:cache

RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www

# Start with artisan serve for Cloud Run (exposes port 8080)
EXPOSE 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
