#!/bin/sh
set -e

# Railway can enable mpm_event/mpm_worker in the runtime container; Apache allows only one MPM, and mod_php needs prefork.
a2dismod mpm_event mpm_worker 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
a2enmod mpm_prefork
apache2ctl -t

PORT="${PORT:-10000}"
export PORT

sed -ri -e "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri -e "s/:80/:${PORT}/" /etc/apache2/sites-available/*.conf

php artisan migrate --force

exec apache2-foreground
