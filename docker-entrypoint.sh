#!/bin/sh
set -e

if [ ! -f .env ] && [ -z "$APP_KEY" ] && [ -z "$DB_CONNECTION" ] && [ -z "$APP_ENV" ]; then
    echo "ERROR: No .env file found and no environment variables set."
    echo "Mount a .env file or pass environment variables (-e APP_KEY=... -e DB_CONNECTION=...)."
    exit 1
fi

if [ -z "$APP_KEY" ] && ! grep -q '^APP_KEY=' .env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate --force --no-interaction
fi

echo "Caching config..."
php artisan config:cache --no-interaction

echo "Caching routes..."
php artisan route:cache --no-interaction

echo "Caching views..."
php artisan view:cache --no-interaction

echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
