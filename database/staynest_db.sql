-- StayNest Housing & Property Booking Management System
-- Phase 6 Database Structure with Dashboards, Reports, Analytics, and Final Demo Data
-- Import this file in phpMyAdmin. It recreates the demo database.

CREATE DATABASE IF NOT EXISTS staynest_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE staynest_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS hosts;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NULL,
    role ENUM('customer','host','staff','main_admin','booking_property_admin','host_location_admin') NOT NULL DEFAULT 'customer',
    status ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    area VARCHAR(100) NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Denmark',
    postal_code VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE hosts (
    host_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    company_description TEXT NULL,
    contact_information VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_hosts_users
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    host_id INT NOT NULL,
    location_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    address VARCHAR(180) NULL,
    property_type VARCHAR(80) NOT NULL DEFAULT 'Apartment',
    bedrooms TINYINT UNSIGNED NOT NULL DEFAULT 1,
    bathrooms DECIMAL(3,1) NOT NULL DEFAULT 1.0,
    image VARCHAR(255) NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    category VARCHAR(80) NOT NULL DEFAULT 'Apartment',
    availability ENUM('available','unavailable') NOT NULL DEFAULT 'available',
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_properties_hosts
        FOREIGN KEY (host_id) REFERENCES hosts(host_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_properties_locations
        FOREIGN KEY (location_id) REFERENCES locations(location_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    booking_date DATE NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    booking_status ENUM('pending','confirmed','rejected','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_users
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_properties
        FOREIGN KEY (property_id) REFERENCES properties(property_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_properties_status ON properties(status);
CREATE INDEX idx_properties_availability ON properties(availability);
CREATE INDEX idx_properties_category ON properties(category);
CREATE INDEX idx_bookings_status ON bookings(booking_status);
CREATE INDEX idx_bookings_dates ON bookings(property_id, check_in_date, check_out_date);
CREATE INDEX idx_locations_city ON locations(city);

-- Demo password for every seeded user: password123
-- Hash generated using PHP password_hash('password123', PASSWORD_BCRYPT)
INSERT INTO users (name, email, password, phone, role, status) VALUES
('Main Admin', 'admin@staynest.test', '$2y$12$phB6e0/cVSUqmjk3/mS94.VJqU0gEqQEOjl5O4yy2.DUUDrxm7SpO', '+4500000001', 'main_admin', 'active'),
('Booking Property Admin', 'bookingadmin@staynest.test', '$2y$12$phB6e0/cVSUqmjk3/mS94.VJqU0gEqQEOjl5O4yy2.DUUDrxm7SpO', '+4500000002', 'booking_property_admin', 'active'),
('Host Location Admin', 'hostadmin@staynest.test', '$2y$12$phB6e0/cVSUqmjk3/mS94.VJqU0gEqQEOjl5O4yy2.DUUDrxm7SpO', '+4500000003', 'host_location_admin', 'active'),
('Demo Host', 'host@staynest.test', '$2y$12$phB6e0/cVSUqmjk3/mS94.VJqU0gEqQEOjl5O4yy2.DUUDrxm7SpO', '+4500000004', 'host', 'active'),
('Demo Customer', 'customer@staynest.test', '$2y$12$phB6e0/cVSUqmjk3/mS94.VJqU0gEqQEOjl5O4yy2.DUUDrxm7SpO', '+4500000005', 'customer', 'active'),
('Demo Staff', 'staff@staynest.test', '$2y$12$phB6e0/cVSUqmjk3/mS94.VJqU0gEqQEOjl5O4yy2.DUUDrxm7SpO', '+4500000006', 'staff', 'active'),
('Available Host User', 'availablehost@staynest.test', '$2y$12$phB6e0/cVSUqmjk3/mS94.VJqU0gEqQEOjl5O4yy2.DUUDrxm7SpO', '+4500000007', 'host', 'active'),
('Nordic Host', 'nordichost@staynest.test', '$2y$12$phB6e0/cVSUqmjk3/mS94.VJqU0gEqQEOjl5O4yy2.DUUDrxm7SpO', '+4500000008', 'host', 'active'),
('Second Customer', 'customer2@staynest.test', '$2y$12$phB6e0/cVSUqmjk3/mS94.VJqU0gEqQEOjl5O4yy2.DUUDrxm7SpO', '+4500000009', 'customer', 'active');

INSERT INTO locations (city, area, country, postal_code) VALUES
('Copenhagen', 'Nørrebro', 'Denmark', '2200'),
('Copenhagen', 'Amager', 'Denmark', '2300'),
('Roskilde', 'City Centre', 'Denmark', '4000'),
('Aalborg', 'City Centre', 'Denmark', '9000'),
('Odense', 'University Area', 'Denmark', '5230'),
('Frederiksberg', 'Frederiksberg C', 'Denmark', '2000'),
('Copenhagen', 'Vesterbro', 'Denmark', '1700'),
('Copenhagen', 'Østerbro', 'Denmark', '2100'),
('Copenhagen', 'Valby', 'Denmark', '2500'),
('Copenhagen', 'Sydhavn', 'Denmark', '2450'),
('Copenhagen', 'Nordhavn', 'Denmark', '2150'),
('Copenhagen', 'Islands Brygge', 'Denmark', '2300'),
('Copenhagen', 'Copenhagen K', 'Denmark', '1050');

INSERT INTO hosts (user_id, company_name, company_description, contact_information) VALUES
(4, 'StayNest Demo Hosting', 'A sample host company for university demonstration.', 'host@staynest.test'),
(8, 'Nordic Urban Stays', 'Modern furnished accommodation for students and professionals.', 'nordichost@staynest.test');

INSERT INTO properties (host_id, location_id, title, description, image, price, category, availability, status) VALUES
(1, 1, 'Modern Studio Apartment', 'A clean modern studio apartment near public transport, cafes, and university facilities.', NULL, 750.00, 'Studio', 'available', 'approved'),
(1, 2, 'Cozy Family Room', 'Comfortable family-friendly room with essential facilities and easy access to the city centre.', NULL, 950.00, 'Family Housing', 'available', 'approved'),
(1, 3, 'Roskilde Student Housing', 'Budget-friendly student accommodation close to the station and campus transport routes.', 'property17.jpg', 600.00, 'Student Housing', 'available', 'pending'),
(1, 4, 'Aalborg Private Apartment', 'Private apartment suitable for short stays and working professionals.', 'property2.jpg', 850.00, 'Apartment', 'unavailable', 'rejected'),
(2, 1, 'Nørrebro Shared Room', 'Affordable shared housing option close to metro, supermarkets, and student facilities.', NULL, 520.00, 'Room', 'available', 'approved'),
(2, 5, 'Odense University Studio', 'Compact studio close to Odense university area with strong transport access.', NULL, 680.00, 'Student Housing', 'available', 'approved'),
(2, 2, 'Amager Business Apartment', 'Fully furnished apartment suitable for short professional stays.', NULL, 1100.00, 'Apartment', 'available', 'approved'),
(2, 3, 'Roskilde Family House', 'Spacious family house with private facilities and quiet surroundings.', 'property31.jpg', 1250.00, 'House', 'unavailable', 'pending');

INSERT INTO properties (host_id, location_id, title, description, address, property_type, bedrooms, bathrooms, image, price, category, availability, status) VALUES
(1, 6, 'Frederiksberg Classic Luxury Apartment', 'Elegant furnished apartment near Frederiksberg Have with bright living space, premium kitchen, workspace, and quick metro access.', 'Gammel Kongevej 121, 2000 Frederiksberg', 'Luxury Apartment', 3, 2.0, 'property1.jpg', 1850.00, 'Apartment', 'available', 'approved'),
(2, 6, 'Frederiksberg Student Studio', 'Compact student-friendly studio close to CBS, metro, grocery stores, and quiet green streets.', 'Falkoner Alle 34, 2000 Frederiksberg', 'Studio Apartment', 1, 1.0, 'property2.jpg', 690.00, 'Student Housing', 'available', 'approved'),
(1, 2, 'Amager Beach Modern Studio', 'Modern studio apartment with balcony, simple Scandinavian furniture, and easy access to Amager Strand and the metro.', 'Amager Strandvej 112, 2300 Copenhagen S', 'Studio Apartment', 1, 1.0, 'property3.jpg', 875.00, 'Studio', 'available', 'approved'),
(2, 2, 'Amager Family Apartment Near Metro', 'Practical family apartment with two bedrooms, open kitchen, courtyard access, and a short walk to Lergravsparken metro.', 'Holmbladsgade 78, 2300 Copenhagen S', 'Family Apartment', 2, 1.0, 'property4.jpg', 1250.00, 'Family Housing', 'available', 'approved'),
(1, 7, 'Vesterbro Premium City Room', 'Premium private city room in a shared apartment near cafes, restaurants, Kødbyen, and Copenhagen Central Station.', 'Istedgade 61, 1650 Copenhagen V', 'Premium City Room', 1, 1.0, 'property5.jpg', 725.00, 'Room', 'available', 'approved'),
(2, 7, 'Vesterbro Renovated Two-Bed Apartment', 'Renovated two-bedroom apartment with high ceilings, dining area, modern bathroom, and lively neighborhood access.', 'Vesterbrogade 95, 1620 Copenhagen V', 'Apartment', 2, 1.0, 'property6.jpg', 1380.00, 'Apartment', 'unavailable', 'approved'),
(1, 8, 'Østerbro Parkside Family Apartment', 'Spacious apartment close to Fælledparken with three bedrooms, large living room, and family-friendly surroundings.', 'Østerbrogade 142, 2100 Copenhagen Ø', 'Family Apartment', 3, 1.5, 'property7.jpg', 1590.00, 'Family Housing', 'available', 'approved'),
(2, 8, 'Østerbro Quiet Student Room', 'Affordable furnished room in a calm Østerbro apartment with shared kitchen, desk, and bicycle storage.', 'Nordre Frihavnsgade 54, 2100 Copenhagen Ø', 'Student Room', 1, 1.0, 'property8.jpg', 560.00, 'Student Housing', 'available', 'pending'),
(1, 1, 'Nørrebro Urban Studio Loft', 'Bright urban studio loft near Assistens Cemetery with smart storage, fast internet, and easy access to cafes and bus lines.', 'Jægersborggade 27, 2200 Copenhagen N', 'Studio Apartment', 1, 1.0, 'property9.jpg', 820.00, 'Studio', 'available', 'approved'),
(2, 1, 'Nørrebro Affordable Shared Apartment', 'Affordable shared apartment option with private bedroom, shared kitchen, laundry access, and public transport nearby.', 'Rantzausgade 18, 2200 Copenhagen N', 'Shared Apartment', 1, 1.0, 'property10.jpg', 540.00, 'Room', 'available', 'approved'),
(1, 9, 'Valby Modern Family Home', 'Comfortable family apartment in Valby with two bedrooms, balcony, modern appliances, and quick S-train connections.', 'Valby Langgade 88, 2500 Valby', 'Family Apartment', 2, 1.0, 'property11.jpg', 1180.00, 'Family Housing', 'available', 'approved'),
(2, 9, 'Valby Budget Student Apartment', 'Budget-friendly student apartment near Valby Station with compact kitchen, workspace, and simple furnishings.', 'Toftegårds Alle 23, 2500 Valby', 'Student Apartment', 1, 1.0, 'property12.jpg', 640.00, 'Student Housing', 'unavailable', 'pending'),
(1, 10, 'Sydhavn Waterfront Studio', 'Fresh waterfront studio with harbor views, balcony, elevator access, and direct public transport to central Copenhagen.', 'Teglholmsgade 45, 2450 Copenhagen SV', 'Studio Apartment', 1, 1.0, 'property13.jpg', 980.00, 'Studio', 'available', 'approved'),
(2, 10, 'Sydhavn New Family Apartment', 'Newer family apartment with two bedrooms, open-plan kitchen, playground nearby, and harbor swimming options.', 'Alliancevej 14, 2450 Copenhagen SV', 'Family Apartment', 2, 1.5, 'property14.jpg', 1325.00, 'Family Housing', 'available', 'approved'),
(1, 11, 'Nordhavn Luxury Harbor Apartment', 'High-end harbor apartment with panoramic windows, designer furniture, two bathrooms, and access to Nordhavn dining.', 'Göteborg Plads 9, 2150 Nordhavn', 'Luxury Apartment', 3, 2.0, 'property15.jpg', 2250.00, 'Apartment', 'available', 'approved'),
(2, 11, 'Nordhavn Premium Business Room', 'Premium furnished room for short stays with desk, shared designer kitchen, and quick metro connection.', 'Århusgade 126, 2150 Nordhavn', 'Premium City Room', 1, 1.0, 'property16.jpg', 790.00, 'Room', 'unavailable', 'approved'),
(1, 12, 'Islands Brygge Canal Apartment', 'Modern canal-side apartment with balcony, bright living room, one bedroom, and walking distance to the harbor bath.', 'Islands Brygge 32, 2300 Copenhagen S', 'Apartment', 1, 1.0, 'property17.jpg', 1195.00, 'Apartment', 'available', 'approved'),
(2, 12, 'Islands Brygge Student Studio', 'Well-planned student studio close to IT University and University of Copenhagen South Campus.', 'Njalsgade 76, 2300 Copenhagen S', 'Student Studio', 1, 1.0, 'property18.jpg', 720.00, 'Student Housing', 'available', 'pending'),
(1, 13, 'Copenhagen K Historic City Apartment', 'Charming apartment in the historic center with classic details, renovated kitchen, and walking distance to Strøget.', 'Kompagnistræde 21, 1208 Copenhagen K', 'Luxury Apartment', 2, 1.5, 'property19.jpg', 1750.00, 'Apartment', 'available', 'approved'),
(2, 13, 'Copenhagen K Premium City Room', 'Premium city room in a central apartment near Kongens Nytorv, metro, shopping, restaurants, and cultural attractions.', 'Store Kongensgade 46, 1264 Copenhagen K', 'Premium City Room', 1, 1.0, 'property20.jpg', 850.00, 'Room', 'available', 'rejected'),
(1, 6, 'Frederiksberg Rooftop Penthouse', 'Exclusive penthouse with private terrace, city views, elevator access, two bathrooms, and elegant furnished living areas.', 'Hostrups Have 11, 2000 Frederiksberg', 'Penthouse Apartment', 3, 2.0, 'property21.jpg', 2450.00, 'Apartment', 'available', 'approved'),
(2, 6, 'Frederiksberg Compact Single Room', 'Simple furnished single room near shopping, metro, and campus areas with shared kitchen and laundry access.', 'Godthåbsvej 42, 2000 Frederiksberg', 'Single Room', 1, 1.0, 'property22.jpg', 495.00, 'Room', 'available', 'approved'),
(1, 7, 'Vesterbro Designer Studio', 'Stylish designer studio with built-in storage, small dining area, fast internet, and easy access to Enghave Plads.', 'Sønder Boulevard 53, 1720 Copenhagen V', 'Designer Studio', 1, 1.0, 'property23.jpg', 1025.00, 'Studio', 'available', 'approved'),
(2, 7, 'Vesterbro Family Courtyard Apartment', 'Family-friendly apartment facing a quiet courtyard with two bedrooms, dishwasher, and room for a home office.', 'Absalonsgade 19, 1658 Copenhagen V', 'Family Apartment', 2, 1.0, 'property24.jpg', 1425.00, 'Family Housing', 'available', 'pending'),
(1, 8, 'Østerbro Embassy Quarter Apartment', 'Refined apartment in a calm Østerbro street with large windows, separate dining room, and premium furnishings.', 'Rosenvængets Allé 6, 2100 Copenhagen Ø', 'Luxury Apartment', 2, 1.5, 'property25.jpg', 1680.00, 'Apartment', 'unavailable', 'approved'),
(2, 8, 'Østerbro Bright Micro Studio', 'Efficient micro studio with kitchenette, workspace, storage solutions, and quick access to Nordhavn station.', 'Classensgade 72, 2100 Copenhagen Ø', 'Micro Studio', 1, 1.0, 'property26.jpg', 675.00, 'Studio', 'available', 'approved'),
(1, 1, 'Nørrebro Creative Loft Apartment', 'Characterful loft-style apartment near Stefansgade with exposed beams, open kitchen, and flexible work space.', 'Stefansgade 35, 2200 Copenhagen N', 'Loft Apartment', 2, 1.0, 'property27.jpg', 1295.00, 'Apartment', 'available', 'approved'),
(2, 1, 'Nørrebro Student Twin Room', 'Budget twin room suited for students with shared bathroom, shared kitchen, bicycle parking, and bus connections.', 'Mimersgade 46, 2200 Copenhagen N', 'Student Room', 1, 1.0, 'property28.jpg', 510.00, 'Student Housing', 'available', 'pending'),
(1, 9, 'Valby Garden Apartment', 'Ground-floor apartment with garden access, two bedrooms, bright kitchen, and a quiet residential setting.', 'Høffdingsvej 17, 2500 Valby', 'Garden Apartment', 2, 1.0, 'property29.jpg', 1125.00, 'Family Housing', 'available', 'approved'),
(2, 9, 'Valby Station Modern Studio', 'Modern studio close to Valby Station with furnished sleeping area, compact kitchen, and good commuter access.', 'Mosedalvej 9, 2500 Valby', 'Studio Apartment', 1, 1.0, 'property30.jpg', 760.00, 'Studio', 'unavailable', 'approved'),
(1, 10, 'Sydhavn Harbor Family Flat', 'Three-bedroom flat in a newer harbor district with balcony, elevator, playground nearby, and generous storage.', 'Sluseholmen 18, 2450 Copenhagen SV', 'Family Apartment', 3, 1.5, 'property31.jpg', 1510.00, 'Family Housing', 'available', 'approved'),
(2, 10, 'Sydhavn Affordable Studio Room', 'Affordable furnished studio room with kitchenette, shared laundry, and easy bike routes to central Copenhagen.', 'Bådehavnsgade 31, 2450 Copenhagen SV', 'Studio Room', 1, 1.0, 'property32.jpg', 620.00, 'Student Housing', 'available', 'rejected'),
(1, 11, 'Nordhavn Minimalist Studio', 'Minimalist furnished studio in Nordhavn with balcony, modern bathroom, and fast metro access to the city center.', 'Kattegatvej 41, 2150 Nordhavn', 'Studio Apartment', 1, 1.0, NULL, 1090.00, 'Studio', 'available', 'approved'),
(2, 12, 'Islands Brygge Two-Bedroom Apartment', 'Comfortable two-bedroom apartment near the waterfront with balcony, open living room, and excellent bike access.', 'Artillerivej 68, 2300 Copenhagen S', 'Apartment', 2, 1.0, NULL, 1340.00, 'Apartment', 'available', 'approved'),
(1, 13, 'Copenhagen K Boutique Serviced Apartment', 'Boutique serviced apartment with weekly cleaning option, central location, premium kitchen, and classic city charm.', 'Læderstræde 12, 1201 Copenhagen K', 'Serviced Apartment', 1, 1.0, 'property15.jpg', 1550.00, 'Apartment', 'unavailable', 'pending');

INSERT INTO bookings (user_id, property_id, booking_date, check_in_date, check_out_date, total_price, booking_status) VALUES
(5, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 2250.00, 'pending'),
(5, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 18 DAY), 2850.00, 'confirmed'),
(6, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 22 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 2250.00, 'cancelled'),
(5, 2, DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 32 DAY), 1900.00, 'rejected'),
(9, 5, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 35 DAY), DATE_ADD(CURDATE(), INTERVAL 38 DAY), 1560.00, 'confirmed'),
(5, 6, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_ADD(CURDATE(), INTERVAL 45 DAY), DATE_ADD(CURDATE(), INTERVAL 48 DAY), 2040.00, 'confirmed'),
(9, 7, DATE_SUB(CURDATE(), INTERVAL 70 DAY), DATE_ADD(CURDATE(), INTERVAL 55 DAY), DATE_ADD(CURDATE(), INTERVAL 57 DAY), 2200.00, 'confirmed'),
(5, 5, DATE_SUB(CURDATE(), INTERVAL 100 DAY), DATE_ADD(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 62 DAY), 1040.00, 'cancelled'),
(9, 6, DATE_SUB(CURDATE(), INTERVAL 130 DAY), DATE_ADD(CURDATE(), INTERVAL 65 DAY), DATE_ADD(CURDATE(), INTERVAL 67 DAY), 1360.00, 'rejected'),
(5, 7, DATE_SUB(CURDATE(), INTERVAL 160 DAY), DATE_ADD(CURDATE(), INTERVAL 75 DAY), DATE_ADD(CURDATE(), INTERVAL 78 DAY), 3300.00, 'confirmed');
