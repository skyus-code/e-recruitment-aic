#!/usr/bin/env bash
# Deployment script for Laravel on Render
# This script runs essential Laravel deployment commands

set -e

echo "=== Starting Laravel Deployment ==="

# Install composer dependencies (production only)
echo "Installing composer dependencies..."
composer install --no-dev

# Cache configuration
echo "Caching configuration..."
php artisan config:cache

# Cache routes
echo "Caching routes..."
php artisan route:cache

# Cache views
echo "Caching views..."
php artisan view:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

echo "=== Deployment Complete ==="