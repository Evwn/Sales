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
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
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

# Start Nginx
service nginx start

# Start PHP-FPM
exec "$@" 