#!/bin/sh
set -e

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

if [ "$1" = "php-fpm" ]; then
    echo "Đang chờ MySQL..."
    until php -r '
        try {
            new PDO(
                "mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT") . ";dbname=" . getenv("DB_DATABASE"),
                getenv("DB_USERNAME"),
                getenv("DB_PASSWORD")
            );
        } catch (Throwable $e) {
            exit(1);
        }
    ' >/dev/null 2>&1; do
        sleep 2
    done

    php artisan migrate --force
    php artisan storage:link >/dev/null 2>&1 || true
    php artisan config:cache
    php artisan view:cache
fi

exec "$@"
