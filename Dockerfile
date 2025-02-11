# Use PHP 8.4 FPM image
FROM php:8.4.1-fpm

LABEL authors="Ksawery Czapczyński"

WORKDIR /var/www

# Install system dependencies including MySQL, Nginx, and required libraries
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    curl \
    git \
    mariadb-server \
    mariadb-client \
    libonig-dev \
    supervisor \
    nginx \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath

# Install Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Copy existing application files
COPY . .

# Ensure Laravel storage and vendor directories exist
RUN mkdir -p /var/www/storage/framework/cache /var/www/storage/framework/views /var/www/storage/framework/sessions
RUN mkdir -p /var/www/bootstrap/cache /var/www/vendor

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Set permissions for Laravel storage and vendor
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/vendor /var/www/public/build
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy Nginx configuration
COPY nginx/nginx.conf /etc/nginx/nginx.conf

# Create a Supervisor configuration file
RUN echo "[supervisord]\nnodaemon=true\n\n\
[program:php-fpm]\ncommand=php-fpm -F\nautostart=true\nautorestart=true\n\n\
[program:mariadb]\ncommand=mysqld_safe\nautostart=true\nautorestart=true\n\n\
[program:nginx]\ncommand=nginx -g 'daemon off;'\nautostart=true\nautorestart=true" > /etc/supervisord.conf

# Ensure database is created and migrations are run at startup
RUN echo "#!/bin/bash\n\
service mariadb start\n\
sleep 10\n\
mysql -u root -e \"CREATE DATABASE IF NOT EXISTS HaarlemFestival;\"\n\
mysql -u root -e \"CREATE USER IF NOT EXISTS 'haarlemfestival'@'%' IDENTIFIED BY '!HaarlemFestival2025'\"\n\
mysql -u root -e \"GRANT ALL PRIVILEGES ON HaarlemFestival.* TO 'haarlemfestival'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;\"\n\
php artisan migrate --force\n\
php artisan db:seed --force\n\
exit 0" > /var/www/setup.sh && chmod +x /var/www/setup.sh

# Expose Nginx (HTTP) and MySQL ports
EXPOSE 80 3306

# Start Supervisor and Run Database Setup
CMD ["/bin/bash", "-c", "/var/www/setup.sh && supervisord -c /etc/supervisord.conf"]
