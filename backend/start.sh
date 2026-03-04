#!/bin/bash
set -e

echo "==> Setting up SQLite on persistent volume..."
mkdir -p /data
chmod 777 /data

if [ ! -s /data/database.sqlite ]; then
    echo "==> Fresh database detected — running migrate:fresh --seed"
    touch /data/database.sqlite
    chmod 666 /data/database.sqlite
    php artisan migrate:fresh --seed --force
else
    echo "==> Existing database detected — running migrate"
    chmod 666 /data/database.sqlite
    php artisan migrate --force
fi

echo "==> Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "==> Starting server..."
exec php artisan serve --host=0.0.0.0 --port=8080
