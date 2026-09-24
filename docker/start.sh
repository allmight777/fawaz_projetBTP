#!/bin/sh
php artisan migrate --force
if [ "$RUN_SEED" = "true" ]; then php artisan db:seed --force; fi
php artisan config:cache
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
