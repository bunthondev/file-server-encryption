<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development/)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Authentication System

This application provides a secure authentication system using Laravel Sanctum for token-based authentication.

## API Endpoints

### Register

```http
POST /api/register
```

Request body:

```json
{
    "username": "your_username",
    "password": "your_password",
    "password_confirmation": "your_password"
}
```

Response:

```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "username": "your_username"
    },
    "token": "your_auth_token"
}
```

### Login

```http
POST /api/login
```

Request body:

```json
{
    "username": "your_username",
    "password": "your_password"
}
```

Response:

```json
{
    "message": "Logged in successfully",
    "user": {
        "id": 1,
        "username": "your_username"
    },
    "token": "your_auth_token"
}
```

### Logout

```http
POST /api/logout
```

Headers:

```
Authorization: Bearer your_auth_token
```

Response:

```json
{
    "message": "Logged out successfully"
}
```

### Get Current User

```http
GET /api/user
```

Headers:

```
Authorization: Bearer your_auth_token
```

Response:

```json
{
    "id": 1,
    "username": "your_username"
}
```

## Security Features

-   Token-based authentication using Laravel Sanctum
-   Rate limiting: 60 requests per minute per user/IP
-   Password hashing
-   JSON-only responses
-   Protected routes requiring authentication

## Usage Example

1. Register a new user:

```bash
curl -X POST http://your-domain/api/register \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"password123","password_confirmation":"password123"}'
```

2. Login:

```bash
curl -X POST http://your-domain/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"password123"}'
```

3. Access protected routes:

```bash
curl -X GET http://your-domain/api/user \
  -H "Authorization: Bearer your_auth_token"
```

4. Logout:

```bash
curl -X POST http://your-domain/api/logout \
  -H "Authorization: Bearer your_auth_token"
```

## Error Responses

The API returns appropriate error responses in JSON format:

-   Invalid credentials:

```json
{
    "message": "The provided credentials are incorrect."
}
```

-   Validation errors:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field": ["Error message"]
    }
}
```

-   Unauthenticated:

```json
{
    "message": "Unauthenticated."
}
```

## Rate Limiting

The API implements rate limiting:

-   60 requests per minute per user/IP
-   Rate limit headers are included in responses:
    -   `X-RateLimit-Limit`: Maximum requests allowed
    -   `X-RateLimit-Remaining`: Remaining requests
    -   `X-RateLimit-Reset`: Time until limit resets
