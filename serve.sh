#!/bin/bash
###############################################################################
# serve.sh — Laravel Server with APP_PORT from .env
#
# Reads APP_PORT from .env and starts Laravel dev server on that port
#
###############################################################################

cd "$(dirname "$0")"

# Load APP_PORT from .env file
PORT=$(grep "^APP_PORT=" .env | cut -d '=' -f2 | tr -d ' ')
PORT=${PORT:-8122}  # Default to 8122 if not found

echo "🚀 Starting Laravel server on port $PORT..."
php artisan serve --port=$PORT
