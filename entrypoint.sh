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
MUSCLE_GROUPS_COUNT=$(php artisan tinker --execute="echo App\\Infrastructure\\Persistence\\Eloquent\\MuscleGroupEloquentModel::count();")

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

echo "Configuring and starting Nginx..."
# Create Nginx config
mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled

cat > /etc/nginx/sites-available/default << 'NGINX_CONFIG'
server {
    listen 8080 default_server;
    listen [::]:8080 default_server;
    
    server_name _;
    root /var/www/public;
    index index.php index.html index.htm;

    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    gzip on;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~ /\. {
        deny all;
    }
}
NGINX_CONFIG

ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default 2>/dev/null || true

echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
exec nginx -g "daemon off;"

