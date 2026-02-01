#!/bin/sh
set -e

# Wait for database to be ready
echo "Waiting for database to be ready..."
until php -r "
try {
    \$host = getenv('DB_HOST') ?: 'ip-db';
    \$port = getenv('DB_PORT') ?: '3306';
    \$database = getenv('DB_DATABASE') ?: 'ip_db';
    \$username = getenv('DB_USERNAME') ?: 'root';
    \$password = getenv('DB_PASSWORD') ?: 'root';

    \$pdo = new PDO(
        \"mysql:host=\$host;port=\$port;dbname=\$database\",
        \$username,
        \$password,
        [PDO::ATTR_TIMEOUT => 3, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    \$pdo->query('SELECT 1');
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Wait a moment for PHP-FPM to be ready
sleep 2

# Test nginx configuration
if ! nginx -t; then
    echo "ERROR: Nginx configuration test failed!"
    exit 1
fi

# Start nginx in foreground
echo "Starting nginx..."
exec nginx -g "daemon off;"
