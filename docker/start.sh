#!/bin/sh
set -e

PORT="${PORT:-10000}"
export PORT

sed -ri -e "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri -e "s/:80/:${PORT}/" /etc/apache2/sites-available/*.conf

php artisan migrate --force

exec apache2-foreground
