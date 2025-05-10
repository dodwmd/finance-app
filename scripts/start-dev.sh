#!/bin/bash

# Start Docker containers
echo "Starting Docker containers..."
docker compose --env-file .env -f docker-compose.yml up -d

MYSQL_ROOT_PASSWORD=$(cat .env | grep MYSQL_ROOT_PASSWORD | cut -d'=' -f2)

# Wait for MySQL to be fully initialized
echo "Waiting for MySQL to initialize..."
max_retries=30
counter=0
while ! docker compose exec db mysqladmin -uroot -p${MYSQL_ROOT_PASSWORD} ping --silent &> /dev/null; do
  counter=$((counter+1))
  if [ $counter -ge $max_retries ]; then
    echo "Error: MySQL did not become ready in time. Exiting."
    exit 1
  fi
  echo "Waiting for MySQL to be ready... ($counter/$max_retries)"
  sleep 1
done
echo "MySQL is ready!"

# Run migrations
echo "Running database migrations..."
php artisan migrate

# Seed the database with test data
echo "Seeding the database..."
php artisan db:seed

# Generate sample transactions
echo "Generating sample transactions..."
php artisan app:generate-sample-transactions

# Skip the front-end asset build for now due to Tailwind issues
echo "Skipping front-end asset build..."
# npm run build

# Start the Laravel development server with a different port
echo "Starting Laravel development server..."
php artisan serve

# Done
echo "Development environment setup complete!"
