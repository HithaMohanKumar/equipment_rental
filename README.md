# Equipment Rental Management System

**CMM007 – Intranet Systems Development**  
Robert Gordon University | Academic Year 2025-2026, Semester 2

## Overview

A web-based equipment rental management system built with PHP and MySQL. The system allows administrators to manage equipment inventory and user accounts, while users can search, rent, and return equipment.

## Features

### Login Module
- Secure authentication with hashed passwords (bcrypt)
- Role-based access control (Admin / User)
- Session management

### Admin Dashboard
- Overview statistics (equipment count, users, active/overdue rentals)
- Recent rental activity feed
- Quick access to management panels

### Equipment Management (Admin)
- Full CRUD operations for equipment inventory
- Track name, category, serial number, condition, quantity
- Protection against deleting equipment with active rentals

### User Management (Admin)
- Full CRUD operations for user accounts
- Set maximum rental limits per user
- View active rental counts per user

### User Dashboard
- Personal rental statistics
- Active and overdue rental overview
- Quick return functionality

### Search & Rent Equipment (User)
- Search by name, description, or serial number
- Filter by category and condition
- Selectable rental quantity and duration (7, 14, or 30 days)
- Real-time availability display

### Rental & Return System
- Automatic quantity tracking on rent/return
- Due date calculation and overdue detection
- Rental limit enforcement
- Full rental history with status filtering

## Technology Stack

- **Frontend:** HTML5, CSS3 (custom responsive design)
- **Backend:** PHP 7+
- **Database:** MySQL via phpMyAdmin
- **Server:** Apache (XAMPP)

## Installation

1. Install and start **XAMPP** (Apache + MySQL)
2. Copy the `equipment_rental` folder to `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux)
3. Open phpMyAdmin: `http://localhost/phpmyadmin`
4. Create a new database called `equipment_rental`
5. Import the SQL file: navigate to **Import** tab and select `sql/database.sql`
6. Update `includes/connection.php` if your MySQL credentials differ from the defaults
7. Visit `http://localhost/equipment_rental/` in your browser

## Default Login Credentials

| Role  | Username | Password    |
|-------|----------|-------------|
| Admin | admin    | admin123    |
| User  | john     | password123 |
| User  | jane     | password123 |

## File Structure

```
equipment_rental/
├── assets/
│   └── css/
│       └── style.css
├── includes/
│   ├── connection.php
│   ├── header.php
│   └── footer.php
├── sql/
│   └── database.sql
├── index.php              (Login page)
├── logout.php             (Session destroy)
├── admin_dashboard.php    (Admin home)
├── admin_equipment.php    (Equipment CRUD)
├── admin_users.php        (User CRUD)
├── admin_rentals.php      (View all rentals)
├── user_dashboard.php     (User home)
├── user_search.php        (Search & rent)
├── user_rent.php          (Process rental)
├── user_return.php        (Process return)
├── user_rentals.php       (Rental history)
└── README.md
```

## Database Schema

The system uses three main tables:
- **users** – stores user accounts with roles and rental limits
- **equipment** – stores equipment inventory with availability tracking
- **rentals** – stores rental transactions with status tracking

## Security Features

- Password hashing with `password_hash()` / `password_verify()` (bcrypt)
- Prepared statements to prevent SQL injection
- Input sanitisation with `htmlspecialchars()` and `mysqli_real_escape_string()`
- Session-based authentication
- Role-based access control on every protected page
- Transaction-based rental/return operations to prevent race conditions

## Author

CMM007 Coursework – Intranet Systems Development  
Robert Gordon University, Aberdeen
