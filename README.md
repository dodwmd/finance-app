# Finance App - Personal Finance Tracker

A modern, Laravel 11-based personal finance tracking application that helps users track income, expenses, and financial goals.

## Features

- Dashboard with financial overview
- Transaction tracking (income, expenses, transfers)
- Transaction categorization
- Financial reporting and insights
- User authentication and data security
- Responsive design for all devices
- RESTful API for integration with other services

## Technology Stack

- **Laravel 11**: Modern PHP framework
- **MySQL/SQLite**: Database storage
- **Laravel Sanctum**: API Authentication
- **Domain-Driven Design**: Clean architecture with repositories and services
- **Eloquent ORM**: Database abstractions and models
- **Laravel Dusk**: Browser automation and testing

## Project Structure

This project follows Laravel 11 best practices with a domain-driven design approach:

- `app/Services/`: Business logic services
- `app/Repositories/`: Data access repositories
- `app/Contracts/`: Interfaces for dependency injection
- `app/Http/Controllers/`: Route controllers
- `app/Http/Resources/`: API resource transformers
- `app/Models/`: Eloquent models
- `app/Events/` & `app/Listeners/`: Event-driven functionality
- `tests/Browser/`: Laravel Dusk end-to-end tests

## Setup Instructions

### Prerequisites

- PHP 8.3+
- Composer
- MySQL or SQLite
- Chrome (for Dusk testing)

### Installation

1. Clone the repository:
   ```
   git clone <repository-url>
   cd finance-app
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Set up environment:
   ```
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure your database in `.env`

5. Run migrations:
   ```
   php artisan migrate
   ```

6. Seed the database with sample data:
   ```
   php artisan db:seed
   ```

7. Start the development server:
   ```
   php artisan serve
   ```

8. Generate sample transactions (optional):
   ```
   php artisan app:generate-sample-transactions
   ```

## Testing

The application includes comprehensive testing using Laravel Dusk for end-to-end browser testing.

### Setting Up Dusk

1. Install Laravel Dusk:
   ```
   composer require --dev laravel/dusk
   php artisan dusk:install
   ```

2. Create a dedicated environment file for testing:
   ```
   cp .env .env.dusk.local
   ```

3. Update the `.env.dusk.local` file with testing configuration:
   ```
   APP_ENV=testing
   APP_URL=http://localhost:8000
   DB_CONNECTION=sqlite
   DB_DATABASE=:memory:
   ```

4. Generate an application key for the testing environment:
   ```
   php artisan key:generate --env=dusk
   ```

### Running Dusk Tests

1. Start a development server in a separate terminal:
   ```
   php artisan serve
   ```

2. Run Dusk tests using the custom command:
   ```
   php artisan app:test-dusk
   ```

3. Run with specific options:
   ```
   # Run specific test
   php artisan app:test-dusk --filter=LoginTest
   
   # Fresh database migrations before testing
   php artisan app:test-dusk --fresh
   
   # Seed the database with test data
   php artisan app:test-dusk --seed
   
   # Just set up the environment without running tests
   php artisan app:test-dusk --setup
   ```

4. Or run the standard Dusk command:
   ```
   php artisan dusk
   ```

### Available Tests

- **LoginTest**: Tests user authentication functionality
- **DashboardTest**: Tests financial dashboard components and navigation
- **TransactionTest**: Tests transaction management (create, read, update, delete)

### Continuous Integration

The project includes a GitHub Actions workflow file (`.github/workflows/dusk.yml`) that automatically runs Dusk tests on push or pull request to main branches.

## API Documentation

### Authentication

- `POST /api/v1/register`: Register new user
- `POST /api/v1/login`: Login and receive API token

### Transactions

- `GET /api/v1/transactions`: List all transactions
- `POST /api/v1/transactions`: Create a new transaction
- `GET /api/v1/transactions/{id}`: Get transaction details
- `PUT /api/v1/transactions/{id}`: Update a transaction
- `DELETE /api/v1/transactions/{id}`: Delete a transaction
- `GET /api/v1/transactions/summary`: Get transaction summary

## Custom Commands

- `php artisan app:generate-sample-transactions`: Generate sample transactions for testing
  - Options:
    - `--user=ID`: Specify user ID (creates test user if not provided)
    - `--count=30`: Number of transactions to generate (default: 30)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
