#!/bin/bash
set -e

echo "==> Clearing cached config..."
php artisan config:clear
php artisan cache:clear

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting server on port ${PORT:-8080}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}