<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About B2B Application

This is a B2B (Business-to-Business) application built with Laravel framework. It provides vendor management capabilities where users can submit vendor requests and administrators can approve or reject them.

## Setup

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and configure your database settings
4. Run `php artisan migrate --seed`
5. Run `php artisan key:generate`
6. Run `php artisan serve` to start the development server

## Vendor Request Status Values

The application uses numerical status values for vendor requests:

- `0`: Pending - Vendor request has been submitted but not yet reviewed
- `1`: Approved - Vendor request has been approved by administrator
- `2`: Rejected - Vendor request has been rejected by administrator

## API Endpoints

All API endpoints are under `/api/v1` and protected with `auth:sanctum` and `verify.device` (except auth and token routes).

- Auth (public)
  - `POST /api/v1/register` — Register
  - `POST /api/v1/login` — Login
  - `POST /api/v1/login/google` — Google login
  - `POST /api/v1/refresh-token` — Refresh token
- Auth (protected)
  - `GET /api/v1/user` — Current user
  - `POST /api/v1/logout` — Logout
- Products
  - `GET /api/v1/products`
  - `GET /api/v1/products/{product}`
  - `POST /api/v1/products`
  - `PUT /api/v1/products/{product}`
  - `DELETE /api/v1/products/{product}`
- Orders
  - `GET /api/v1/orders`
  - `GET /api/v1/orders/{order}`
  - `POST /api/v1/orders`
  - `PUT /api/v1/orders/{order}`
  - `DELETE /api/v1/orders/{order}`
- Wishlist
  - `GET /api/v1/wishlist`
  - `POST /api/v1/wishlist/{product}`
  - `DELETE /api/v1/wishlist/{product}`
- Vendor Details
  - `POST /api/v1/vendor-details`
  - `PUT /api/v1/vendor-requests/{vendorRequest}/status`

## Database Migrations

To refresh the database and apply all migrations:
```bash
php artisan migrate:refresh --seed
```

## Contributing

Thank you for considering contributing to the B2B application!

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to the development team.

## License

The B2B application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
## Device Verification Middleware

This project includes device verification to mitigate token theft:

- Middleware alias: `verify.device` (see `AppServiceProvider`)
- Requires headers: `X-Device-Fingerprint`, `X-Device-Type`, `X-Browser`, `X-Platform`
- Frontend caches a stable fingerprint to avoid 403s when toggling mobile view

To relax checks in development, you can swap to a softer device verification or bypass in local environments.

## Orders Address Handling

- Frontend `Orders.jsx` collects structured address fields: pincode, city, state, village, landmark
- These are composed into a single `shipping_address` string and submitted to `POST /api/v1/orders`
- If you prefer storing structured address, add columns via migration and update `OrderController@store` and model accordingly