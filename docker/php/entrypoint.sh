#!/bin/sh
set -e

echo "🚀 Starting Days Battery application..."

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
  echo "  Database is unavailable - sleeping"
  sleep 2
done
echo "✅ Database is ready!"

# Run migrations
echo "🔄 Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Clear and warm up cache
echo "🔥 Warming up cache..."
php bin/console cache:clear --no-warmup
php bin/console cache:warmup

echo "✅ Application is ready!"

# Start PHP-FPM
exec php-fpm
