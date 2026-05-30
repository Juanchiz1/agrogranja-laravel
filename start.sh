#!/bin/bash
set -e

echo "==> Generating .env from environment variables..."

php -r "
\$lines = [
    'APP_NAME=' . getenv('APP_NAME') ?: 'AgroGranja',
    'APP_ENV=' . (getenv('APP_ENV') ?: 'production'),
    'APP_KEY=' . (getenv('APP_KEY') ?: ''),
    'APP_DEBUG=' . (getenv('APP_DEBUG') ?: 'false'),
    'APP_URL=' . (getenv('APP_URL') ?: 'http://localhost'),
    '',
    'LOG_CHANNEL=' . (getenv('LOG_CHANNEL') ?: 'stack'),
    'LOG_LEVEL=' . (getenv('LOG_LEVEL') ?: 'debug'),
    '',
    'DB_CONNECTION=mysql',
    'DB_HOST=' . (getenv('DB_HOST') ?: '127.0.0.1'),
    'DB_PORT=' . (getenv('DB_PORT') ?: '3306'),
    'DB_DATABASE=' . (getenv('DB_DATABASE') ?: 'railway'),
    'DB_USERNAME=' . (getenv('DB_USERNAME') ?: 'root'),
    'DB_PASSWORD=' . (getenv('DB_PASSWORD') ?: ''),
    '',
    'BROADCAST_DRIVER=' . (getenv('BROADCAST_DRIVER') ?: 'log'),
    'CACHE_DRIVER=' . (getenv('CACHE_DRIVER') ?: 'array'),
    'FILESYSTEM_DISK=' . (getenv('FILESYSTEM_DISK') ?: 'local'),
    'QUEUE_CONNECTION=' . (getenv('QUEUE_CONNECTION') ?: 'sync'),
    'SESSION_DRIVER=' . (getenv('SESSION_DRIVER') ?: 'array'),
    'SESSION_LIFETIME=' . (getenv('SESSION_LIFETIME') ?: '120'),
    '',
    'SUPABASE_URL=' . (getenv('SUPABASE_URL') ?: ''),
    'SUPABASE_KEY=' . (getenv('SUPABASE_KEY') ?: ''),
];
file_put_contents('/app/.env', implode(PHP_EOL, \$lines) . PHP_EOL);
echo 'DB_HOST=' . (getenv('DB_HOST') ?: 'NOT SET') . PHP_EOL;
echo 'DB_DATABASE=' . (getenv('DB_DATABASE') ?: 'NOT SET') . PHP_EOL;
"

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting server on port ${PORT:-8080}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}