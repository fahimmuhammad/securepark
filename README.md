# SecurePark

A full-stack car parking management web application built with PHP, MySQL, and Bootstrap 5.

## Features

- User registration and login with session-based authentication
- Real-time parking slot availability map
- Slot booking with date/time and price calculation
- Booking history, receipts, and cancellation
- Admin panel — manage slots, bookings, and users
- Dark / light theme toggle (persisted via localStorage)
- Responsive design (mobile-friendly)

## Tech Stack

| Layer    | Technology                        |
|----------|-----------------------------------|
| Backend  | PHP 8+ (procedural)               |
| Database | MySQL via MySQLi                  |
| Frontend | Bootstrap 5.3, Font Awesome 6.5   |
| Server   | Apache (XAMPP)                    |

## Project Structure

```
securepark/
├── admin/              # Admin panel pages
├── api/                # AJAX endpoints (JSON responses)
├── assets/
│   ├── css/style.css   # All custom styles + theme variables
│   └── js/main.js      # Shared JS — toast, theme, booking logic
├── config/db.php       # Database connection
├── includes/           # Shared partials (auth, nav, sidebar)
├── index.php           # Public landing page
├── login.php           # Login / registration
├── dashboard.php       # User dashboard
├── book.php            # Slot booking page
├── my-bookings.php     # User booking history
├── booking-receipt.php # Individual booking receipt
├── profile.php         # User profile editor
├── logout.php
├── setup.php           # One-time DB setup script
└── securepark.sql      # Full database schema + seed data
```

## Setup

### Requirements
- XAMPP (Apache + MySQL + PHP 8+)

### Steps

1. Clone or copy the project into `xampp/htdocs/securepark/`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel
3. Open your browser and visit:
   ```
   http://localhost/securepark/setup.php
   ```
   This creates the `securepark_db` database and seeds demo data automatically.
4. Go to the app:
   ```
   http://localhost/securepark/
   ```

### Database Config

Edit `config/db.php` if your MySQL credentials differ from the XAMPP defaults:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'securepark_db');
```

### Demo Accounts

| Role  | Email                   | Password |
|-------|-------------------------|----------|
| Admin | admin@securepark.com    | admin123 |
| User  | john@example.com        | user123  |
