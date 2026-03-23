#!/bin/bash
# Railway injects $PORT dynamically — Apache must listen on it

PORT="${PORT:-80}"

echo "Starting Apache on port $PORT..."

# Update Apache to listen on the correct port
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

# Start Apache in foreground
apache2-foreground
