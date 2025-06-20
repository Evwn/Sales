#!/bin/bash
set -e

# Ensure .env file exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Set proper permissions for .env
chown www-data:www-data .env
chmod 644 .env

# Set proper permissions for storage and cache directories
chown -R www-data:www-data /var/www
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Generate application key if not set
php artisan key:generate --no-interaction

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Enable error reporting in PHP
echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
echo "log_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
echo "error_log = /var/log/php_errors.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Create PHP error log file
touch /var/log/php_errors.log
chown www-data:www-data /var/log/php_errors.log

# Start Nginx
service nginx start

# Start PHP-FPM as www-data
exec php-fpm -F 