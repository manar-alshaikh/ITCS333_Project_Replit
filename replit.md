# ITCS333 Course Page - Team 49

## Overview
A web-based course management system for the ITCS333 Internet Software Development course. Built with PHP and PostgreSQL, it provides functionality for students and administrators to manage course content, weekly breakdowns, assignments, resources, and discussions.

## Project Structure
```
.
├── config/
│   └── Config.php          # Database configuration and authentication helpers
├── database/
│   ├── course_page.sql     # Original MySQL schema
│   └── schema_postgres.sql # PostgreSQL schema for Replit
├── src/
│   ├── admin/              # Admin user management
│   ├── assignments/        # Assignment management
│   ├── auth/               # Authentication (login, dashboard, admin portal)
│   ├── common/             # Shared CSS and JS files
│   ├── discussion/         # Discussion boards
│   ├── errors/             # Error pages
│   ├── resources/          # Course resources management
│   └── weekly/             # Weekly breakdown management
├── assets/                 # Fonts and images
└── index.php               # Main router
```

## Tech Stack
- **Backend**: PHP 8.2
- **Database**: PostgreSQL (converted from MySQL)
- **Frontend**: HTML, CSS, JavaScript with animated backgrounds

## Running the Application
The application runs on port 5000 using PHP's built-in development server:
```bash
php -S 0.0.0.0:5000
```

## Database
Uses PostgreSQL with environment variables:
- DATABASE_URL, PGHOST, PGPORT, PGUSER, PGPASSWORD, PGDATABASE

## Default Users
- Admin: `admin` / `password123` (email: admin@example.com)
- Instructor: `instructor1` / `password123`
- Student: `student1` / `password123`

## Key Routes
- `/login` - Login page
- `/dashboard` - User dashboard
- `/admin` - Admin portal
- `/weekly/list` - Weekly breakdown list
- `/resources/list` - Course resources
- `/assignments/list` - Assignments

## Recent Changes
- **2025-12-12**: Migrated from MySQL to PostgreSQL for Replit compatibility
- Updated Config.php to use environment variables for database connection
- Fixed all include paths to use __DIR__ for proper routing
- Fixed all CSS/JS resource paths to use absolute paths
