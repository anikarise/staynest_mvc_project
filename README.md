# StayNest – Housing & Property Booking Management System

StayNest is a full-stack PHP MVC housing and property booking management system built for a university project demonstration and portfolio presentation.

## Current Version

**Phase 6 – Final dashboards, analytics, reports, UI polishing, and demo documentation**

## Technology Stack

- PHP MVC Architecture
- MySQL Database
- HTML/CSS/JavaScript
- XAMPP Localhost
- phpMyAdmin
- VS Code

## Main Features

### Authentication and Roles

- User registration
- User login/logout
- Password hashing
- Session handling
- CSRF protection
- Role-based access control

Supported roles:

- Customer
- Host
- Staff
- Main Admin
- Booking & Property Admin
- Host & Location Admin

### User Management

- Main Admin can view user records
- Users can update profile information
- Users can change password

### Host Management

- Add, view, edit, and delete hosts
- Host profile linked with user account
- Host self-profile page
- Host delete protection when properties exist

### Location Management

- Add, view, edit, and delete locations
- City, area, country, and postal code support
- Location delete protection when properties exist

### Property Management

- Add, view, edit, and delete property listings
- Property image upload
- Property categories
- Availability handling
- Admin approval/rejection moderation
- Host can manage only own properties
- Public users see approved properties only

### Booking Management

- Customer booking requests
- Booking edit/cancel flow
- Admin confirmation/rejection
- Booking date validation
- Overlap prevention
- Booking history
- Host booking overview

### Phase 6 Dashboards and Reports

- Role-specific dashboard analytics
- Booking status graphs
- Property status graphs
- Property availability graphs
- User role breakdown
- Monthly confirmed revenue chart
- Top host report
- Top location report
- Recent bookings panel
- Recent properties panel
- Printable report page
- CSV report export

## Installation

1. Copy the full project folder to:

```text
C:\xampp\htdocs\staynest_mvc_project
```

2. Start XAMPP:

```text
Apache
MySQL
```

3. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

4. Drop the old database if it exists:

```text
staynest_db
```

5. Import:

```text
C:\xampp\htdocs\staynest_mvc_project\database\staynest_db.sql
```

6. Open the project:

```text
http://localhost/staynest_mvc_project/public
```

## Demo Accounts

All demo accounts use this password:

```text
password123
```

| Role | Email |
|---|---|
| Main Admin | admin@staynest.test |
| Booking & Property Admin | bookingadmin@staynest.test |
| Host & Location Admin | hostadmin@staynest.test |
| Host | host@staynest.test |
| Second Host | nordichost@staynest.test |
| Customer | customer@staynest.test |
| Second Customer | customer2@staynest.test |
| Staff | staff@staynest.test |
| Host user without profile | availablehost@staynest.test |

## Important URLs

```text
http://localhost/staynest_mvc_project/public
http://localhost/staynest_mvc_project/public/auth/login
http://localhost/staynest_mvc_project/public/auth/register
http://localhost/staynest_mvc_project/public/dashboard
http://localhost/staynest_mvc_project/public/property
http://localhost/staynest_mvc_project/public/booking
http://localhost/staynest_mvc_project/public/host
http://localhost/staynest_mvc_project/public/location
http://localhost/staynest_mvc_project/public/report
http://localhost/staynest_mvc_project/public/report/export
```

## Project Folder Structure

```text
staynest_mvc_project/
├── app/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── core/
├── config/
├── database/
├── public/
│   ├── assets/
│   └── uploads/
└── README.md
```

## Notes

This project is designed for localhost demonstration. Before using it online, additional production hardening would be needed, such as HTTPS, production error handling, stricter upload scanning, email verification, logging, and stronger account controls.
