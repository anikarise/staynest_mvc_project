# StayNest Testing Checklist

## Environment

- [ ] XAMPP Apache started
- [ ] XAMPP MySQL started
- [ ] Project folder is inside `htdocs`
- [ ] `staynest_db.sql` imported successfully
- [ ] Homepage loads from `/public`

## Authentication

- [ ] Login works
- [ ] Logout works
- [ ] Register works for customer
- [ ] Register works for host
- [ ] Wrong password shows error
- [ ] Guest cannot access dashboard
- [ ] CSRF-protected forms work

## Role Access

- [ ] Main Admin can access all modules
- [ ] Booking & Property Admin can access properties/bookings/reports
- [ ] Host & Location Admin can access hosts/locations/reports
- [ ] Host can access own profile, own properties, own booking overview
- [ ] Customer can browse and book properties
- [ ] Staff can view operational pages and reports

## Host and Location

- [ ] Add location
- [ ] Edit location
- [ ] Search location
- [ ] Delete unused location
- [ ] Linked location cannot be deleted
- [ ] Add host profile
- [ ] Edit host profile
- [ ] Delete unused host profile
- [ ] Host with properties cannot be deleted

## Property

- [ ] Add property
- [ ] Edit property
- [ ] Upload image
- [ ] Filter/search property
- [ ] Approve property
- [ ] Reject property
- [ ] Public users see approved properties only
- [ ] Host sees own properties only
- [ ] Property with bookings cannot be deleted

## Booking

- [ ] Customer creates booking
- [ ] Past check-in date is rejected
- [ ] Check-out before check-in is rejected
- [ ] Overlapping booking is blocked
- [ ] Admin confirms booking
- [ ] Admin rejects booking
- [ ] Customer cancels booking
- [ ] Host sees own property bookings only

## Dashboards and Reports

- [ ] Main dashboard analytics load
- [ ] Booking status graph loads
- [ ] Property status graph loads
- [ ] Revenue graph loads
- [ ] Recent bookings load
- [ ] Recent properties load
- [ ] `/report` opens
- [ ] CSV export works
- [ ] Print report works

## Final Check

- [ ] CSS loads properly
- [ ] Navbar links work
- [ ] 404 page works
- [ ] No PHP syntax errors
- [ ] No database connection error
