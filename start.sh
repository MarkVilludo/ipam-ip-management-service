#!/bin/sh
set -e

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
