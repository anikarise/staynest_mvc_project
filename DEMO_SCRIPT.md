# StayNest Demo Script

Use this during your university demonstration.

## 1. Start With Homepage

Open:

```text
http://localhost/staynest_mvc_project/public
```

Explain that StayNest is a PHP MVC housing and property booking platform with role-based dashboards and database integration.

## 2. Show Public Property Browsing

Open:

```text
/property
```

Show that public visitors can browse approved properties only. Mention search, location filtering, availability filtering, property cards, and booking button.

## 3. Login as Main Admin

Use:

```text
admin@staynest.test
password123
```

Show:

- Main Admin dashboard
- Total users
- Total properties
- Total bookings
- Hosts
- Locations
- Revenue analytics
- Recent bookings/properties

## 4. Show Management Modules

Open each module:

```text
/user
/property
/booking
/host
/location
```

Explain that each module uses MVC controllers, models, views, validation, and MySQL foreign-key relationships.

## 5. Show Property Moderation

Login as host:

```text
host@staynest.test
password123
```

Create a property. Explain that host-submitted properties become pending.

Login as Booking & Property Admin:

```text
bookingadmin@staynest.test
password123
```

Approve or reject the property.

## 6. Show Booking Flow

Login as customer:

```text
customer@staynest.test
password123
```

Create a booking from an approved property.

Then login as Booking & Property Admin and confirm/reject the booking.

Explain:

- Check-in date cannot be in the past
- Check-out must be after check-in
- Active overlapping bookings are blocked
- Total price is calculated server-side

## 7. Show Reports

Open:

```text
/report
```

Show:

- Booking status
- Property status
- Property availability
- Category report
- Top locations
- Top hosts
- Recent bookings
- Recent properties
- Print report
- Export CSV

## 8. Finish With Architecture

Explain:

- Controllers handle requests
- Models handle database logic
- Views handle UI
- Auth class handles session, CSRF, and role access
- MySQL uses foreign keys and JOIN queries

Final closing line:

"StayNest is a complete role-based housing management and booking platform built using PHP MVC and MySQL, suitable for a realistic university project demonstration."
