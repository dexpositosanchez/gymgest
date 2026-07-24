#!/bin/bash
set -e

echo "Waiting for PostgreSQL to be ready..."
until php artisan db:show --json 2>/dev/null | grep -q "driver"; do
  >&2 echo "PostgreSQL is unavailable - sleeping"
  sleep 2
done

echo "PostgreSQL is up - executing migrations"
php artisan migrate --force

echo "Checking if database needs seeding..."
MUSCLE_GROUPS_COUNT=$(php artisan tinker --execute="echo \App\Infrastructure\Persistence\Eloquent\MuscleGroupEloquentModel::count();" 2>/dev/null | tail -1)

if [ "$MUSCLE_GROUPS_COUNT" = "0" ]; then
  echo "Database is empty - running base seeders"
  php artisan db:seed --class=MuscleGroupSeeder --force
  php artisan db:seed --class=DefaultExerciseSeeder --force
fi

echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

echo "Starting Laravel Scheduler in background..."
php artisan schedule:work &

echo "Starting PHP-FPM..."
exec php-fpm