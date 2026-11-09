#!/bin/sh
set -e

# Fix permissions for storage and bootstrap/cache
mkdir -p /var/www/storage/framework/views /var/www/storage/framework/sessions /var/www/storage/framework/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 777 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

if [ "$1" = "php-fpm" ]; then
    exec "$@"
else
    exec "$@"
fi
