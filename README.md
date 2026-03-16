# Gym Membership and Attendance Management System

A comprehensive Laravel-based system for managing gym memberships, member attendance tracking, and payment processing.

## Project Overview

This system provides a complete solution for gym management including membership handling, attendance tracking, and financial management.

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Core Models

- **Member** - Gym members with personal information and membership status
- **Membership** - Member subscriptions with package and billing information
- **Attendance** - Daily check-in/check-out records for members
- **Package** - Membership plans with duration and pricing
- **Payment** - Payment records with transaction tracking

## API Endpoints

### Members
- `GET /api/members` - List all members
- `POST /api/members` - Create new member
- `GET /api/members/{id}` - Get member details
- `PUT /api/members/{id}` - Update member
- `DELETE /api/members/{id}` - Delete member

### Attendance
- `POST /api/attendance/check-in` - Check in member
- `POST /api/attendance/check-out` - Check out member
- `GET /api/attendance/today` - Today's attendance records
- `GET /api/attendance/member/{id}` - Member attendance history
- `GET /api/attendance/stats` - Attendance statistics

### Memberships
- `POST /api/memberships` - Create membership
- `GET /api/memberships/{id}` - Get membership details
- `POST /api/memberships/{id}/renew` - Renew membership
- `POST /api/memberships/{id}/cancel` - Cancel membership
- `GET /api/memberships/active/all` - Active memberships
- `GET /api/memberships/expiring/soon` - Expiring memberships

### Payments
- `POST /api/payments` - Record payment
- `GET /api/payments/member/{id}` - Member payment history
- `GET /api/payments/stats` - Payment statistics You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
