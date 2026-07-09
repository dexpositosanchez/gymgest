#!/bin/bash
set -e

echo "Waiting for PostgreSQL to be ready..."
until PGPASSWORD=$DB_PASSWORD psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c '\q'; do
  >&2 echo "PostgreSQL is unavailable - sleeping"
  sleep 1
done

echo "PostgreSQL is up - executing migrations"
php artisan migrate --force

echo "Checking if database needs seeding..."
MUSCLE_GROUPS_COUNT=$(php artisan tinker --execute="echo App\Infrastructure\Persistence\Eloquent\MuscleGroupEloquentModel::count();")

if [ "$MUSCLE_GROUPS_COUNT" -eq "0" ]; then
  echo "Database is empty - running base seeders (muscle groups + exercises)"
  php artisan db:seed --force

  # Preguntar si cargar datos de desarrollo
  echo ""
  echo "¿Deseas cargar datos de desarrollo? (s/n)"
  echo "(10 trainers, 13 gyms, 30 students con diferentes estados de cuota)"
  read -r response
  if [ "$response" = "s" ] || [ "$response" = "S" ]; then
    echo "Cargando datos de desarrollo..."
    php artisan db:seed --class=DevDataSeeder --force
  else
    echo "Saltando datos de desarrollo"
  fi
else
  echo "Database already has data - skipping seeders"
fi

echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

echo "Starting Laravel Scheduler in background..."
php artisan schedule:work &

echo "Starting PHP-FPM..."
exec php-fpm
