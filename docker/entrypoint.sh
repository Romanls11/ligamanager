#!/bin/sh
set -e

: "${PORT:=10000}"
sed "s/\${PORT}/$PORT/g" /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

cd /var/www/html

php artisan config:clear
php artisan migrate --force
php artisan db:seed --class=RolesSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec supervisord -c /etc/supervisord.conf
