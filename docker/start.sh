#!/bin/sh
set -e

# Se o banco for novo, migrate:reset pode falhar - isso é esperado e ignorado
php artisan migrate:reset --force || true
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force
npm run build
php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"