#!/bin/bash

# Start Docker containers
echo "Starting Docker containers..."
docker compose -f docker-compose.yml -f docker-compose.app.yml up -d

# Wait for MySQL to be fully initialized
echo "Waiting for MySQL to initialize..."
max_retries=30
counter=0
while ! docker exec mysql mysqladmin -uroot -ppassword ping --silent &> /dev/null; do
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
php artisan serve --port=8006

# Done
echo "Development environment setup complete!"
