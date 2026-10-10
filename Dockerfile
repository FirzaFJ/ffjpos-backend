FROM php:8.2-fpm

# Install sistem dependencies & ekstensi PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx

RUN docker-php-ext-install pdo_mysql mbstring exts gd bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy seluruh file proyek ke container
COPY . /var/www

# Install dependensi Laravel via Composer
RUN composer install --no-dev --optimize-autoloader

# Berikan izin direktori storage & cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port yang digunakan oleh Render
EXPOSE 8080

# Jalankan server bawaan Laravel mengarah ke port Render ($PORT)
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
