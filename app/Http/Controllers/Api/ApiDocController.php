<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Vibe Finance API Documentation",
 *     description="RESTful API endpoints for the Vibe Finance application",
 *
 *     @OA\Contact(
 *         email="support@vibefinance.app",
 *         name="Vibe Finance Support"
 *     ),
 *
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Vibe Finance API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="sanctum"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 * @OA\Tag(
 *     name="Transactions",
 *     description="API Endpoints for managing transactions"
 * )
 * @OA\Tag(
 *     name="Categories",
 *     description="API Endpoints for managing categories"
 * )
 * @OA\Tag(
 *     name="Budgets",
 *     description="API Endpoints for managing budget plans"
 * )
 * @OA\Tag(
 *     name="Financial Goals",
 *     description="API Endpoints for managing financial goals"
 * )
 * @OA\Tag(
 *     name="Recurring Transactions",
 *     description="API Endpoints for managing recurring transactions"
 * )
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for user management"
 * )
 */
class ApiDocController extends Controller
{
    // This controller does not need any methods
    // It's only used for Swagger annotations
}
