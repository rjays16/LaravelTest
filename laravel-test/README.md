# Laravel Test Booking - Event Booking System

A Laravel-based Event Booking System with authentication, event management, ticket booking, and payment processing.

## Features

- User Authentication (Register, Login, Logout) with Sanctum
- Role-based Access Control (Admin, Organizer, Customer)
- Event Management (Create, Read, Update, Delete)
- Ticket Management
- Booking System with Double Booking Prevention
- Mock Payment Processing
- Notifications on Booking Confirmation
- RESTful API
- Beautiful Responsive Frontend UI

## Requirements

- PHP 8.2+
- Composer
- MySQL / XAMPP

## Step by Step Setup

### 1. Install Dependencies
```bash
cd laravel-test
composer install
```

### 2. Configure Environment
Copy `.env.example` to `.env` and update database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Generate Application Key
```bash
php artisan key:generate
```

### 4. Create Database
Create a MySQL database named `laravel_db` in phpMyAdmin.

### 5. Run Migrations and Seeders
```bash
php artisan migrate --seed
```

### 6. Start the Server
```bash
php artisan serve
```

### 7. Access the Application
Open browser: `http://127.0.0.1:8000`

## Seeded Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | lizzie18@example.com | password |
| Organizer | schmitt.magnolia@example.org | password |
| Customer | cberge@example.org | password |

## Running Tests

```bash
php artisan test
```

## Postman Collection

Import `postman_collection.json` into Postman to test all API endpoints.

## License

MIT License
