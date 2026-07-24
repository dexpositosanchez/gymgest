#!/bin/bash
set -e

echo "Waiting for PostgreSQL to be ready..."
until PGPASSWORD="$PGPASSWORD" psql -h "$PGHOST" -U "$PGUSER" -d "$PGDATABASE" -c '\q'; do
  >&2 echo "PostgreSQL is unavailable - sleeping"
  sleep 1
done

echo "PostgreSQL is up - executing migrations"
php artisan migrate --force

echo "Checking if database needs seeding..."
MUSCLE_GROUPS_COUNT=$(php artisan tinker --execute="echo App\\Infrastructure\\Persistence\\Eloquent\\MuscleGroupEloquentModel::count();")

if [ "$MUSCLE_GROUPS_COUNT" -eq "0" ]; then
  echo "Database is empty - running base seeders (muscle groups + exercises)"
  php artisan db:seed --force
else
  echo "Database already has data - skipping seeders"
fi

echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

echo "Starting services with Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

