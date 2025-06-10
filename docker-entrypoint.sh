#!/bin/bash
set -e

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

# Start PHP-FPM
exec "$@" 