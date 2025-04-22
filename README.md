# Vibe Finance - Personal Finance Tracker

[![Laravel CI](https://github.com/dodwmd/finance-app/actions/workflows/laravel.yml/badge.svg)](https://github.com/dodwmd/finance-app/actions/workflows/laravel.yml)
[![Laravel Dusk Tests](https://github.com/dodwmd/finance-app/actions/workflows/dusk.yml/badge.svg)](https://github.com/dodwmd/finance-app/actions/workflows/dusk.yml)
[![Docker Lint](https://github.com/dodwmd/finance-app/actions/workflows/docker-lint.yml/badge.svg)](https://github.com/dodwmd/finance-app/actions/workflows/docker-lint.yml)

A modern, Laravel 11-based personal finance tracking application that helps users track income, expenses, and financial goals with a clean, intuitive interface.

## Features

- **Dashboard with financial overview** - Get a quick glance at your financial health
- **Transaction management** - Track income, expenses, and transfers with ease
- **Transaction categorization** - Organize spending and income for better insights
- **Recurring transactions** - Set up automatic transaction tracking for regular payments
- **Budget planning** - Create budgets and monitor your spending against them
- **Financial goals** - Set and track progress towards your financial objectives
- **Expense analytics** - Visualize your spending patterns with charts and reports
- **User authentication** - Secure access with email/password or social logins (Google, GitHub)
- **RESTful API** - Integrate with other services or build your own mobile app
- **Responsive design** - Works on desktop, tablet, and mobile devices

## Technology Stack

- **Laravel 11**: Modern PHP framework for robust web applications
- **MySQL/SQLite**: Flexible database options
- **Laravel Sanctum**: Secure API authentication
- **Laravel Socialite**: OAuth integration for social logins
- **Domain-Driven Design**: Clean architecture with repositories and services
- **Eloquent ORM**: Intuitive database interactions
- **Laravel Dusk**: End-to-end browser testing
- **Tailwind CSS**: Utility-first CSS framework for responsive design
- **Docker**: Containerized development environment

## Project Structure

This project follows domain-driven design principles with a clean architecture:

- `app/Services/`: Business logic services
- `app/Repositories/`: Data access repositories
- `app/Contracts/`: Interfaces for dependency injection
- `app/Http/Controllers/`: Route controllers
- `app/Http/Resources/`: API resource transformers
- `app/Models/`: Eloquent models
- `app/Events/` & `app/Listeners/`: Event-driven functionality
- `tests/Unit/`: Unit tests for repositories and services
- `tests/Feature/`: Feature tests for application functionality
- `tests/Browser/`: Laravel Dusk end-to-end tests

## CI/CD Pipelines

The project uses GitHub Actions for continuous integration:

- **Laravel CI** - Runs unit and feature tests, static analysis with PHPStan
- **Laravel Dusk Tests** - Runs browser-based end-to-end tests
- **Docker Lint** - Validates Docker configuration files

## Setup Instructions

### Prerequisites

- PHP 8.3+
- Composer
- MySQL or SQLite
- Node.js 18+ and npm
- Docker & Docker Compose (optional)

### Standard Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd finance-app
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure your database in `.env`

5. Run migrations:
   ```bash
   php artisan migrate
   ```

6. Build frontend assets:
   ```bash
   npm run build
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

### Docker Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd finance-app
   ```

2. Copy environment file:
   ```bash
   cp .env.example .env
   ```

3. Start the Docker containers:
   ```bash
   docker compose up -d
   ```

4. Access the application at http://localhost:8000

5. Access phpMyAdmin at http://localhost:8080 (username: root, password: root_password)

### Sample Data Generation

Generate sample transactions for testing:
```bash
php artisan app:generate-sample-transactions --count=30
```

## Testing

The application includes comprehensive testing at multiple levels:

### Unit and Feature Tests

Run all PHPUnit tests:
```bash
php artisan test
```

### Browser Tests with Laravel Dusk

Run Dusk tests using the custom command:
```bash
php artisan app:test-dusk
```

With options:
```bash
# Run specific test
php artisan app:test-dusk --filter=LoginTest

# Fresh database migrations before testing
php artisan app:test-dusk --fresh

# Seed the database with test data
php artisan app:test-dusk --seed
```

## API Documentation

The application provides a RESTful API for integration with other services.

### Authentication

- `POST /api/v1/register`: Register new user
- `POST /api/v1/login`: Login and receive API token
- `POST /api/v1/social/redirect/{provider}`: Redirect to social login provider
- `GET /api/v1/social/callback/{provider}`: Handle social login callback

### Transactions

- `GET /api/v1/transactions`: List transactions
- `POST /api/v1/transactions`: Create transaction
- `GET /api/v1/transactions/{id}`: Get transaction details
- `PUT /api/v1/transactions/{id}`: Update transaction
- `DELETE /api/v1/transactions/{id}`: Delete transaction

### Categories

- `GET /api/v1/categories`: List categories
- `POST /api/v1/categories`: Create category
- `GET /api/v1/categories/{id}`: Get category details
- `PUT /api/v1/categories/{id}`: Update category
- `DELETE /api/v1/categories/{id}`: Delete category

### Budgets

- `GET /api/v1/budgets`: List budgets
- `POST /api/v1/budgets`: Create budget
- `GET /api/v1/budgets/{id}`: Get budget details
- `PUT /api/v1/budgets/{id}`: Update budget
- `DELETE /api/v1/budgets/{id}`: Delete budget

### Financial Goals

- `GET /api/v1/financial-goals`: List financial goals
- `POST /api/v1/financial-goals`: Create financial goal
- `GET /api/v1/financial-goals/{id}`: Get financial goal details
- `PUT /api/v1/financial-goals/{id}`: Update financial goal
- `DELETE /api/v1/financial-goals/{id}`: Delete financial goal

### Recurring Transactions

- `GET /api/v1/recurring-transactions`: List recurring transactions
- `POST /api/v1/recurring-transactions`: Create recurring transaction
- `GET /api/v1/recurring-transactions/{id}`: Get recurring transaction details
- `PUT /api/v1/recurring-transactions/{id}`: Update recurring transaction
- `DELETE /api/v1/recurring-transactions/{id}`: Delete recurring transaction

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
