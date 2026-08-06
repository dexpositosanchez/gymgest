#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Checking if database needs seeding..."
MUSCLE_GROUPS_COUNT=$(php artisan tinker --execute="echo \App\Infrastructure\Persistence\Eloquent\MuscleGroupEloquentModel::count();" 2>/dev/null | tail -1)

if [ "$MUSCLE_GROUPS_COUNT" = "0" ]; then
  echo "Database is empty - running base seeders"
  php artisan db:seed --class=MuscleGroupSeeder --force
  php artisan db:seed --class=DefaultExerciseSeeder --force

  if [ "$APP_SEED_DEV_DATA" = "true" ]; then
    echo "Loading dev data..."
    php artisan db:seed --class=DevDataSeeder --force
  fi
fi

echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

echo "Starting Laravel Scheduler in background..."
php artisan schedule:work &

echo "Starting Apache..."
exec apache2-foreground