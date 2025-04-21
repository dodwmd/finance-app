#!/bin/bash

# Start Docker containers
echo "Starting Docker containers..."
docker compose -f docker-compose.yml -f docker-compose.app.yml up -d

# Wait for MySQL to be fully initialized
echo "Waiting for MySQL to initialize..."
sleep 10

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
php artisan serve --port=8005

# Done
echo "Development environment setup complete!"
