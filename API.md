# Vibe Finance API Documentation

This document provides information about the RESTful API endpoints available in the Vibe Finance application.

## API Version

All API endpoints are prefixed with `/api/v1/` to indicate they are part of version 1 of the API.

## Authentication

The API uses Laravel Sanctum for token-based authentication. To authenticate with the API:

1. Make a POST request to `/api/v1/login` with your email, password, and device name
2. The response will include a token that should be included in all subsequent requests
3. Include the token in the `Authorization` header as a Bearer token

Example:
```
Authorization: Bearer 1|your_token_here
```

### Authentication Endpoints

- **POST /api/v1/login** - Obtain an API token
- **GET /api/v1/user** - Get authenticated user information
- **POST /api/v1/logout** - Revoke the current API token
- **POST /api/v1/logout-all** - Revoke all API tokens for the user

## Rate Limiting

The API implements rate limiting to prevent abuse:

- Public endpoints: 30 requests per minute
- Authenticated endpoints: 60 requests per minute

## Available Endpoints

### Transactions

- **GET /api/v1/transactions** - List all transactions
  - Optional query parameters: `type`, `start_date`, `end_date`
- **POST /api/v1/transactions** - Create a new transaction
- **GET /api/v1/transactions/{id}** - View a specific transaction
- **PUT/PATCH /api/v1/transactions/{id}** - Update a transaction
- **DELETE /api/v1/transactions/{id}** - Delete a transaction
- **GET /api/v1/transactions/summary** - Get transaction summary statistics

### Categories

- **GET /api/v1/categories** - List all categories
  - Optional query parameters: `type`
- **POST /api/v1/categories** - Create a new category
- **GET /api/v1/categories/{id}** - View a specific category
- **PUT/PATCH /api/v1/categories/{id}** - Update a category
- **DELETE /api/v1/categories/{id}** - Delete a category

### Budgets

- **GET /api/v1/budgets** - List all budgets
  - Optional query parameters: `period`
- **POST /api/v1/budgets** - Create a new budget
- **GET /api/v1/budgets/{id}** - View a specific budget
- **PUT/PATCH /api/v1/budgets/{id}** - Update a budget
- **DELETE /api/v1/budgets/{id}** - Delete a budget
- **GET /api/v1/budgets/progress** - Get budget progress statistics

### Financial Goals

- **GET /api/v1/financial-goals** - List all financial goals
  - Optional query parameters: `is_completed`
- **POST /api/v1/financial-goals** - Create a new financial goal
- **GET /api/v1/financial-goals/{id}** - View a specific financial goal
- **PUT/PATCH /api/v1/financial-goals/{id}** - Update a financial goal
- **DELETE /api/v1/financial-goals/{id}** - Delete a financial goal
- **GET /api/v1/financial-goals/progress** - Get goal progress statistics

### Recurring Transactions

- **GET /api/v1/recurring-transactions** - List all recurring transactions
  - Optional query parameters: `type`, `frequency`
- **POST /api/v1/recurring-transactions** - Create a new recurring transaction
- **GET /api/v1/recurring-transactions/{id}** - View a specific recurring transaction
- **PUT/PATCH /api/v1/recurring-transactions/{id}** - Update a recurring transaction
- **DELETE /api/v1/recurring-transactions/{id}** - Delete a recurring transaction
- **GET /api/v1/recurring-transactions/due** - Get due recurring transactions

### Users

- **GET /api/v1/users** - List all users (admin only)
- **POST /api/v1/users** - Create a new user (admin only)
- **GET /api/v1/users/{id}** - View a specific user
- **PUT/PATCH /api/v1/users/{id}** - Update a user
- **DELETE /api/v1/users/{id}** - Delete a user

## Interactive Documentation

The API includes interactive documentation using Swagger/OpenAPI. You can access this at:

```
/api/documentation
```

This interactive documentation allows you to:

1. Browse all available endpoints
2. See request and response schemas
3. Test API calls directly from the browser
4. View model schemas and relationships

## Error Handling

The API returns standard HTTP status codes:

- 200: Success
- 201: Resource created
- 400: Bad request
- 401: Unauthorized
- 403: Forbidden
- 404: Not found
- 422: Validation error
- 429: Too many requests (rate limiting)
- 500: Server error

Error responses include a message and, for validation errors, specific field errors.
