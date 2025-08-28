#!/bin/sh
set -e

# Wait for db
until pg_isready -h database -U "$POSTGRES_USER" -d "$POSTGRES_DB"; do
  echo "Waiting for database..."
  sleep 1
done

# Run migrations
php bin/console doctrine:database:create --if-not-exists || true
php bin/console doctrine:migrations:migrate --no-interaction || true

# Run server
exec "$@"
