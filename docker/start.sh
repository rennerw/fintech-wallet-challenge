#!/bin/sh
set -e

php artisan migrate:reset --force
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force

npm run dev -- --host 0.0.0.0 --port 5173 & \
php artisan serve --host=0.0.0.0 --port="${PORT:-8000}" &
wait -n
