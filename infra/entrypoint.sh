#!/bin/sh
# Prepara o volume, roda as migrations e otimiza o Laravel antes de subir o PHP-FPM.
set -e
cd /var/www/html

mkdir -p storage/database storage/app/public/pets storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
[ -f storage/database/database.sqlite ] || touch storage/database/database.sqlite
chown -R www-data:www-data storage

php artisan migrate --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
