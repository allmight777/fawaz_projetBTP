#!/bin/sh
php artisan migrate:fresh --seed --force
php artisan config:cache
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
