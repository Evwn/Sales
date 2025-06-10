#!/bin/bash
set -e

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Start PHP-FPM
exec "$@" 