# StayNest Demonstration Script

This script is used during the final university sprint demonstration and prototype presentation of the StayNest Housing and Property Booking Management System.

---

# 1. Introduction and Homepage

Open:

```text
http://localhost/staynest_mvc_project/public
```

Explain:

StayNest is a centralized PHP MVC housing and property booking management platform developed using Agile methodologies, MySQL database integration, and role-based system architecture.

The platform allows:
- customers to browse and reserve housing properties,
- hosts to manage accommodation listings,
- administrators to manage bookings, users, locations, reports, and operational workflows.

Mention:
- MVC architecture
- role-based dashboards
- MySQL database integration
- responsive frontend design
- centralized housing operations

---

# 2. Public Property Browsing

Open:

```text
/property
```

Explain:

Public users can browse approved housing properties without authentication.

Demonstrate:
- property cards
- property images
- location information
- pricing
- availability
- property categories
- property details page
- search and filtering system

Mention:

Only approved properties become publicly visible after administrative moderation.

---

# 3. Authentication and Role-Based Access

Open login page:

```text
/auth/login
```

Explain:

The system supports multiple user roles with role-based authorization and protected dashboard access.

Supported roles:
- Customer
- Host
- Staff
- Booking & Property Admin
- Host & Location Admin
- Main Admin

Mention:
- session handling
- password hashing
- CSRF protection
- access restrictions
- protected routes

---

# 4. Main Admin Dashboard

Login:

```text
admin@staynest.test
password123
```

Show dashboard analytics:

- Total users
- Total properties
- Total bookings
- Hosts
- Locations
- Booking statistics
- Revenue analytics
- Recent bookings
- Recent properties

Explain:

The dashboard centralizes operational monitoring and provides administrators with analytical insight into platform activity.

Mention:
- role-specific dashboard rendering
- graph generation
- reporting queries
- statistics calculation

---

# 5. User Management

Open:

```text
/user
```

Show:
- user listing
- role management
- account status handling
- user search/filtering
- user deletion
- pending host/staff approvals

Explain:

Customers can register instantly while Host and Staff accounts require administrative approval before receiving access to the system.

Mention:
- account validation
- role protection
- approval workflow
- restricted administrative operations

---

# 6. Host and Location Management

Open:

```text
/host
/location
```

Demonstrate:
- host profile management
- location CRUD operations
- search/filter functionality
- relational protection

Explain:

Hosts are connected with properties while locations organize housing data geographically.

Mention:
- foreign key relationships
- relational database validation
- protected deletion logic

---

# 7. Property Management Workflow

Login as Host:

```text
host@staynest.test
password123
```

Create a property listing.

Explain:
- image upload
- availability handling
- property categories
- host-specific property ownership

Mention:

New properties remain pending until approved by the Booking & Property Admin.

Login as Booking & Property Admin:

```text
bookingadmin@staynest.test
password123
```

Approve/reject property.

Explain:
- moderation workflow
- approval system
- public visibility control

---

# 8. Booking Management Workflow

Login as Customer:

```text
customer@staynest.test
password123
```

Create a booking from an approved property.

Demonstrate:
- property details page
- booking form
- check-in/check-out validation
- booking history
- booking status

Explain validation rules:
- past check-in dates are rejected
- checkout must be after check-in
- overlapping active bookings are blocked
- total booking price is calculated server-side

Login as Booking & Property Admin.

Approve/reject booking request.

Explain:
- booking moderation
- booking lifecycle
- status management
- reservation protection logic

---

# 9. Search, Filtering, and Reports

Open:

```text
/report
```

Demonstrate:
- booking status reports
- property reports
- location reports
- revenue analytics
- top host report
- recent booking activity
- CSV export
- printable reports

Explain:

The reporting system provides centralized operational analytics for administrative decision-making.

Mention:
- aggregate SQL queries
- chart rendering
- dashboard analytics
- export functionality

---

# 10. Security and Validation

Explain:

The platform includes multiple security and validation layers including:

- CSRF protection
- session authentication
- password hashing
- server-side validation
- input sanitization
- role-based route protection
- booking overlap prevention
- protected administrative actions

---

# 11. MVC Architecture Explanation

Explain the system architecture:

## Controllers
Handle requests, routing, validation, and business workflow processing.

## Models
Handle database operations, relationships, SQL queries, and system logic.

## Views
Handle frontend rendering, dashboards, forms, and user interfaces.

## Database Layer
Stores users, bookings, properties, locations, roles, and reporting information using MySQL relational database integration.

---

# Final Closing Statement

“StayNest is a fully integrated role-based housing and property booking management platform developed using PHP MVC architecture and MySQL database integration. The system combines authentication, property management, booking workflows, reporting, validation, and administrative operations into one centralized web application developed through Agile software development practices.”
