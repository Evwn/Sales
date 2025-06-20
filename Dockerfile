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
    libsqlite3-dev \
    nodejs \
    npm \
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo_sqlite

# Configure PHP
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini && \
    echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/upload-limit.ini && \
    echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/upload-limit.ini && \
    echo "max_execution_time=600" >> /usr/local/etc/php/conf.d/time-limit.ini

# Configure PHP-FPM
RUN sed -i 's/listen = 127.0.0.1:9000/listen = \/var\/run\/php-fpm.sock/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;listen.owner = www-data/listen.owner = www-data/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;listen.group = www-data/listen.group = www-data/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;listen.mode = 0660/listen.mode = 0660/g' /usr/local/etc/php-fpm.d/www.conf

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy the entire application first
COPY . .

# Create .env file if it doesn't exist
RUN if [ ! -f .env ]; then \
    cp .env.example .env; \
    fi

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Install Node.js dependencies
RUN npm install

# Fix all case sensitivity issues in one go
RUN cd resources/js && \
    for dir in */; do \
        if [ -d "$dir" ]; then \
            dir_name=$(basename "$dir"); \
            upper_name=$(echo "$dir_name" | tr '[:lower:]' '[:upper:]'); \
            if [ "$dir_name" != "$upper_name" ]; then \
                mv "$dir" "${upper_name}_temp" && \
                mv "${upper_name}_temp" "$upper_name"; \
            fi; \
        fi; \
    done

# Build assets
RUN npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    mkdir -p /var/log/nginx /var/run && \
    chown -R www-data:www-data /var/log/nginx /var/run && \
    chmod -R 755 /var/log/nginx /var/run

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Create startup script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose ports
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"] 