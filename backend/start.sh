#!/bin/bash
set -e

# Create writable directories at runtime
mkdir -p database storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache

# Create SQLite database
touch database/database.sqlite

# Fix permissions so php-fpm (www-data) can write
chmod 664 database/database.sqlite
chmod 775 database
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data database storage bootstrap/cache 2>/dev/null || true

# Copy .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Run migrations
php artisan migrate:fresh --seed --force --no-interaction

# Start php-fpm in background + nginx in foreground
(php-fpm -D) && nginx -g 'daemon off;'
