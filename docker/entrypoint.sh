#!/bin/sh
set -e

PORT="${PORT:-10000}"

# Render assigns the listen port at runtime via $PORT; Apache's config is
# fixed at build time, so point it at the right port on every boot.
sed -ri "s/Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:[0-9]+>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Idempotent — safe to run on every boot/restart.
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
