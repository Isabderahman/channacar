#!/bin/sh
set -e
cd /var/www

# Install PHP dependencies if they are missing (fresh checkout / new packages).
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] installing composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
fi

# Writable directories for Laravel.
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
         storage/logs storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# public/storage -> storage/app/public (uploaded car images & contract documents).
[ -L public/storage ] || php artisan storage:link || true

# Production caches (config/routes/views). Safe to run without the DB.
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
