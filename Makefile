.PHONY: default start stop restart build migrate seed test clean help

# Default action - start development environment
default: start

# Start development environment
start:
	@echo "Starting development environment..."
	@chmod +x scripts/start-dev.sh
	@./scripts/start-dev.sh

# Stop Docker containers
stop:
	@echo "Stopping Docker containers..."
	@docker compose down

# Restart Docker containers
restart: stop start

# Build Docker containers
build:
	@echo "Building Docker containers..."
	@docker compose -f docker-compose.yml -f docker-compose.app.yml build

# Run migrations
migrate:
	@echo "Running migrations..."
	@php artisan migrate

# Seed database
seed:
	@echo "Seeding database..."
	@php artisan db:seed

# Generate sample transactions
sample:
	@echo "Generating sample transactions..."
	@php artisan app:generate-sample-transactions

# Run tests
test:
	@echo "Running tests..."
	@php artisan test

# Run parallel tests
parallel-test:
	@echo "Running tests in parallel..."
	@php artisan test --parallel

# Clean up
clean:
	@echo "Cleaning up..."
	@docker compose down -v
	@rm -rf vendor/facade/ignition 2>/dev/null || true
	@echo "Removing Docker volumes..."

# Reset everything and start fresh
reset: clean
	@echo "Resetting environment..."
	@docker system prune -f
	@docker volume prune -f
	@echo "Starting fresh environment..."
	@$(MAKE) start

# Show help information
help:
	@echo "Finance App Development Commands:"
	@echo "--------------------------------"
	@echo "make                    - Start development environment (default)"
	@echo "make start              - Start development environment"
	@echo "make stop               - Stop Docker containers"
	@echo "make restart            - Restart Docker containers"
	@echo "make build              - Build Docker containers"
	@echo "make migrate            - Run database migrations"
	@echo "make seed               - Seed the database"
	@echo "make sample             - Generate sample transactions"
	@echo "make test               - Run tests"
	@echo "make parallel-test      - Run tests in parallel"
	@echo "make clean              - Remove Docker volumes and clean up"
	@echo "make reset              - Reset everything and start fresh"
