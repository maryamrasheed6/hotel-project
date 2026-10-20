# Royal Stay Hotel Booking System

Royal Stay is a full-stack PHP hotel booking website designed for a luxury hospitality brand. It combines a customer-facing reservation experience with a management dashboard for administrators to handle rooms, bookings, messages, reviews, and site settings. The project is built using PHP with PDO/MySQL, plain JavaScript for interactive front-end behavior, and a clean modular layout that separates business logic, security helpers, database configuration, and UI components.

## Project Overview

This system allows guests to browse available rooms, filter accommodations by date and guest count, review room details, and submit booking requests with pricing summaries and date validation. The booking process includes room availability checks, guest information collection, special requests, and payment option selection. On the administrative side, users can manage hotel rooms, track reservations, view submitted contact forms, approve customer reviews, and update global hotel settings such as tax and currency values.

## Key Features

- Luxury hotel landing page with featured rooms and amenities
- Room listing and filtering by type, date, and number of guests
- Room detail pages with image galleries and service highlights
- Reservation form with dynamic stay calculations and taxes
- Availability validation to prevent overlapping bookings
- User login and authentication flow for customers and admin users
- Booking confirmation and status tracking
- Admin dashboard for bookings, rooms, messages, and reviews
- Contact form and review submission functionality
- Secure form handling with CSRF protection and session-based flash messages

## Tech Stack

- PHP 8+
- MySQL / MariaDB
- PDO database layer
- HTML5, CSS3, JavaScript
- Custom PHP helper functions and session utilities
- XAMPP / WAMP / Apache + MySQL local stack

## Project Structure

```text
Hotel Booking System/
├── actions/                   # Booking, auth, contact, and review actions
├── admin/                     # Admin login, dashboard, and management pages
├── assets/                    # CSS, JavaScript, and image files
├── config/                    # Database configuration
├── database/                  # SQL schema and seed data
├── includes/                  # Reusable helpers, auth checks, CSRF, header/footer
├── auth.php                   # Customer login/register page
├── booking.php                # Reservation form page
├── booking-confirmation.php   # Booking success page
├── contact.php                # Contact page
├── index.php                  # Main landing page
├── my-bookings.php            # User booking history page
├── room-details.php           # Detailed room page
├── rooms.php                  # Room listing page
├── reviews.php                # Reviews listing page
├── logout.php                 # Logout handler
├── README.md                  # Project documentation
└── ...
```

## Requirements

Before running the project, make sure the following are installed and running:

- PHP
- MySQL or MariaDB
- Apache or another local web server
- Composer is optional unless you add dependencies later

A typical local setup uses XAMPP or WAMP, with the project folder placed inside the web root.

## Installation and Setup

1. Clone or copy the project into your local server directory.
2. Start Apache and MySQL.
3. Open your MySQL client and create a database named `royal_stay`.
4. Import the SQL file located in:
   - `database/database.sql`
5. Update the database credentials in:
   - `config/database.php`

Default database settings are:

```php
DB_HOST = '127.0.0.1'
DB_PORT = '3306'
DB_NAME = 'royal_stay'
DB_USER = 'root'
DB_PASS = ''
```

If your local environment uses a different MySQL username/password, update those values before running the app.

## Run the Application

Open the project in your browser using a URL similar to:

```text
http://localhost/Hotel%20Booking%20System/
```

If your document root points directly to the project folder, you can also access it through:

```text
http://localhost/
```

## Default Admin Account

The database seed data already includes a default admin account:

- Email: `admin@royalstay.com`
- Password: `Admin@12345`

Sample customer accounts are also provided for testing:

- `john.doe@example.com` / `Customer@12345`
- `sophia.miller@example.com` / `Customer@12345`

## Admin Access

To access the admin panel, open:

```text
http://localhost/Hotel%20Booking%20System/admin/login.php
```

Then log in with the admin credentials above.

## Notes

- The project includes a working seed dataset with sample rooms, bookings, reviews, and contact messages.
- Room availability is checked by comparing stay dates against existing bookings.
- The booking flow is intended for a hospitality demo or business prototype, not a production-grade payment gateway.
- Payment is configured as "Pay at Hotel" for the current implementation.

## Summary

Royal Stay is a complete hotel booking management project that balances a polished customer experience with an operational admin system. It demonstrates how a PHP/MySQL website can manage room inventory, guest reservations, customer interactions, and business settings in a single, cohesive platform. The project is well-suited for learning, demos, and customization for a real boutique or luxury hotel website.
