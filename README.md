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

## Project Structure

This project follows Laravel 11 best practices with a domain-driven design approach:

- `app/Services/`: Business logic services
- `app/Repositories/`: Data access repositories
- `app/Contracts/`: Interfaces for dependency injection
- `app/Http/Controllers/`: Route controllers
- `app/Http/Resources/`: API resource transformers
- `app/Models/`: Eloquent models
- `app/Events/` & `app/Listeners/`: Event-driven functionality

## Setup Instructions

### Prerequisites

- PHP 8.3+
- Composer
- MySQL or SQLite

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
