FROM richarvey/nginx-php-fpm:3.1.6

# Set environment variables for production
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    CI=true

# Set the working directory
WORKDIR /var/www/html

# Copy the application code
COPY . .

# Install composer dependencies (production only)
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --no-scripts

# Cache configuration
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Run migrations forcefully
RUN php artisan migrate --force

# Create storage links and directories
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && php artisan storage:link

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose port 8080 (Richarvey default)
EXPOSE 8080

# Start the application
CMD ["php-fpm"]