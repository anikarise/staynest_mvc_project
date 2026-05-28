USE staynest_db;

ALTER TABLE properties
    ADD COLUMN IF NOT EXISTS address VARCHAR(180) NULL AFTER description,
    ADD COLUMN IF NOT EXISTS property_type VARCHAR(80) NOT NULL DEFAULT 'Apartment' AFTER address,
    ADD COLUMN IF NOT EXISTS bedrooms TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER property_type,
    ADD COLUMN IF NOT EXISTS bathrooms DECIMAL(3,1) NOT NULL DEFAULT 1.0 AFTER bedrooms;

INSERT INTO locations (city, area, country, postal_code)
SELECT 'Frederiksberg', 'Frederiksberg C', 'Denmark', '2000'
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE city = 'Frederiksberg' AND area = 'Frederiksberg C');

INSERT INTO locations (city, area, country, postal_code)
SELECT 'Copenhagen', 'Vesterbro', 'Denmark', '1700'
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE city = 'Copenhagen' AND area = 'Vesterbro');

INSERT INTO locations (city, area, country, postal_code)
SELECT 'Copenhagen', 'Osterbro', 'Denmark', '2100'
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE city = 'Copenhagen' AND area = 'Osterbro');

INSERT INTO locations (city, area, country, postal_code)
SELECT 'Copenhagen', 'Valby', 'Denmark', '2500'
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE city = 'Copenhagen' AND area = 'Valby');

INSERT INTO locations (city, area, country, postal_code)
SELECT 'Copenhagen', 'Sydhavn', 'Denmark', '2450'
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE city = 'Copenhagen' AND area = 'Sydhavn');

INSERT INTO locations (city, area, country, postal_code)
SELECT 'Copenhagen', 'Nordhavn', 'Denmark', '2150'
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE city = 'Copenhagen' AND area = 'Nordhavn');

INSERT INTO locations (city, area, country, postal_code)
SELECT 'Copenhagen', 'Islands Brygge', 'Denmark', '2300'
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE city = 'Copenhagen' AND area = 'Islands Brygge');

INSERT INTO locations (city, area, country, postal_code)
SELECT 'Copenhagen', 'Copenhagen K', 'Denmark', '1050'
WHERE NOT EXISTS (SELECT 1 FROM locations WHERE city = 'Copenhagen' AND area = 'Copenhagen K');

INSERT INTO properties (host_id, location_id, title, description, address, property_type, bedrooms, bathrooms, image, price, category, availability, status)
SELECT *
FROM (
    SELECT 1 AS host_id, (SELECT location_id FROM locations WHERE city = 'Frederiksberg' AND area = 'Frederiksberg C' LIMIT 1) AS location_id, 'Frederiksberg Classic Luxury Apartment' AS title, 'Elegant furnished apartment near Frederiksberg Have with bright living space, premium kitchen, workspace, and quick metro access.' AS description, 'Gammel Kongevej 121, 2000 Frederiksberg' AS address, 'Luxury Apartment' AS property_type, 3 AS bedrooms, 2.0 AS bathrooms, 'property1.jpg' AS image, 1850.00 AS price, 'Apartment' AS category, 'available' AS availability, 'approved' AS status
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Frederiksberg' AND area = 'Frederiksberg C' LIMIT 1), 'Frederiksberg Student Studio', 'Compact student-friendly studio close to CBS, metro, grocery stores, and quiet green streets.', 'Falkoner Alle 34, 2000 Frederiksberg', 'Studio Apartment', 1, 1.0, 'property2.jpg', 690.00, 'Student Housing', 'available', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Amager' LIMIT 1), 'Amager Beach Modern Studio', 'Modern studio apartment with balcony, simple Scandinavian furniture, and easy access to Amager Strand and the metro.', 'Amager Strandvej 112, 2300 Copenhagen S', 'Studio Apartment', 1, 1.0, 'property3.jpg', 875.00, 'Studio', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Amager' LIMIT 1), 'Amager Family Apartment Near Metro', 'Practical family apartment with two bedrooms, open kitchen, courtyard access, and a short walk to Lergravsparken metro.', 'Holmbladsgade 78, 2300 Copenhagen S', 'Family Apartment', 2, 1.0, 'property4.jpg', 1250.00, 'Family Housing', 'available', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Vesterbro' LIMIT 1), 'Vesterbro Premium City Room', 'Premium private city room in a shared apartment near cafes, restaurants, Kodbyen, and Copenhagen Central Station.', 'Istedgade 61, 1650 Copenhagen V', 'Premium City Room', 1, 1.0, 'property5.jpg', 725.00, 'Room', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Vesterbro' LIMIT 1), 'Vesterbro Renovated Two-Bed Apartment', 'Renovated two-bedroom apartment with high ceilings, dining area, modern bathroom, and lively neighborhood access.', 'Vesterbrogade 95, 1620 Copenhagen V', 'Apartment', 2, 1.0, 'property6.jpg', 1380.00, 'Apartment', 'unavailable', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Osterbro' LIMIT 1), 'Osterbro Parkside Family Apartment', 'Spacious apartment close to Faelledparken with three bedrooms, large living room, and family-friendly surroundings.', 'Osterbrogade 142, 2100 Copenhagen O', 'Family Apartment', 3, 1.5, 'property7.jpg', 1590.00, 'Family Housing', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Osterbro' LIMIT 1), 'Osterbro Quiet Student Room', 'Affordable furnished room in a calm Osterbro apartment with shared kitchen, desk, and bicycle storage.', 'Nordre Frihavnsgade 54, 2100 Copenhagen O', 'Student Room', 1, 1.0, 'property8.jpg', 560.00, 'Student Housing', 'available', 'pending'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area LIKE '%rrebro' LIMIT 1), 'Norrebro Urban Studio Loft', 'Bright urban studio loft near Assistens Cemetery with smart storage, fast internet, and easy access to cafes and bus lines.', 'Jaegersborggade 27, 2200 Copenhagen N', 'Studio Apartment', 1, 1.0, 'property9.jpg', 820.00, 'Studio', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area LIKE '%rrebro' LIMIT 1), 'Norrebro Affordable Shared Apartment', 'Affordable shared apartment option with private bedroom, shared kitchen, laundry access, and public transport nearby.', 'Rantzausgade 18, 2200 Copenhagen N', 'Shared Apartment', 1, 1.0, 'property10.jpg', 540.00, 'Room', 'available', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Valby' LIMIT 1), 'Valby Modern Family Home', 'Comfortable family apartment in Valby with two bedrooms, balcony, modern appliances, and quick S-train connections.', 'Valby Langgade 88, 2500 Valby', 'Family Apartment', 2, 1.0, 'property11.jpg', 1180.00, 'Family Housing', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Valby' LIMIT 1), 'Valby Budget Student Apartment', 'Budget-friendly student apartment near Valby Station with compact kitchen, workspace, and simple furnishings.', 'Toftegaards Alle 23, 2500 Valby', 'Student Apartment', 1, 1.0, 'property12.jpg', 640.00, 'Student Housing', 'unavailable', 'pending'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Sydhavn' LIMIT 1), 'Sydhavn Waterfront Studio', 'Fresh waterfront studio with harbor views, balcony, elevator access, and direct public transport to central Copenhagen.', 'Teglholmsgade 45, 2450 Copenhagen SV', 'Studio Apartment', 1, 1.0, 'property13.jpg', 980.00, 'Studio', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Sydhavn' LIMIT 1), 'Sydhavn New Family Apartment', 'Newer family apartment with two bedrooms, open-plan kitchen, playground nearby, and harbor swimming options.', 'Alliancevej 14, 2450 Copenhagen SV', 'Family Apartment', 2, 1.5, 'property14.jpg', 1325.00, 'Family Housing', 'available', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Nordhavn' LIMIT 1), 'Nordhavn Luxury Harbor Apartment', 'High-end harbor apartment with panoramic windows, designer furniture, two bathrooms, and access to Nordhavn dining.', 'Goteborg Plads 9, 2150 Nordhavn', 'Luxury Apartment', 3, 2.0, 'property15.jpg', 2250.00, 'Apartment', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Nordhavn' LIMIT 1), 'Nordhavn Premium Business Room', 'Premium furnished room for short stays with desk, shared designer kitchen, and quick metro connection.', 'Arhusgade 126, 2150 Nordhavn', 'Premium City Room', 1, 1.0, 'property16.jpg', 790.00, 'Room', 'unavailable', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Islands Brygge' LIMIT 1), 'Islands Brygge Canal Apartment', 'Modern canal-side apartment with balcony, bright living room, one bedroom, and walking distance to the harbor bath.', 'Islands Brygge 32, 2300 Copenhagen S', 'Apartment', 1, 1.0, 'property17.jpg', 1195.00, 'Apartment', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Islands Brygge' LIMIT 1), 'Islands Brygge Student Studio', 'Well-planned student studio close to IT University and University of Copenhagen South Campus.', 'Njalsgade 76, 2300 Copenhagen S', 'Student Studio', 1, 1.0, 'property18.jpg', 720.00, 'Student Housing', 'available', 'pending'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Copenhagen K' LIMIT 1), 'Copenhagen K Historic City Apartment', 'Charming apartment in the historic center with classic details, renovated kitchen, and walking distance to Stroget.', 'Kompagnistraede 21, 1208 Copenhagen K', 'Luxury Apartment', 2, 1.5, 'property19.jpg', 1750.00, 'Apartment', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Copenhagen K' LIMIT 1), 'Copenhagen K Premium City Room', 'Premium city room in a central apartment near Kongens Nytorv, metro, shopping, restaurants, and cultural attractions.', 'Store Kongensgade 46, 1264 Copenhagen K', 'Premium City Room', 1, 1.0, 'property20.jpg', 850.00, 'Room', 'available', 'rejected'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Frederiksberg' AND area = 'Frederiksberg C' LIMIT 1), 'Frederiksberg Rooftop Penthouse', 'Exclusive penthouse with private terrace, city views, elevator access, two bathrooms, and elegant furnished living areas.', 'Hostrups Have 11, 2000 Frederiksberg', 'Penthouse Apartment', 3, 2.0, 'property21.jpg', 2450.00, 'Apartment', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Frederiksberg' AND area = 'Frederiksberg C' LIMIT 1), 'Frederiksberg Compact Single Room', 'Simple furnished single room near shopping, metro, and campus areas with shared kitchen and laundry access.', 'Godthabsvej 42, 2000 Frederiksberg', 'Single Room', 1, 1.0, 'property22.jpg', 495.00, 'Room', 'available', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Vesterbro' LIMIT 1), 'Vesterbro Designer Studio', 'Stylish designer studio with built-in storage, small dining area, fast internet, and easy access to Enghave Plads.', 'Sonder Boulevard 53, 1720 Copenhagen V', 'Designer Studio', 1, 1.0, 'property23.jpg', 1025.00, 'Studio', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Vesterbro' LIMIT 1), 'Vesterbro Family Courtyard Apartment', 'Family-friendly apartment facing a quiet courtyard with two bedrooms, dishwasher, and room for a home office.', 'Absalonsgade 19, 1658 Copenhagen V', 'Family Apartment', 2, 1.0, 'property24.jpg', 1425.00, 'Family Housing', 'available', 'pending'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Osterbro' LIMIT 1), 'Osterbro Embassy Quarter Apartment', 'Refined apartment in a calm Osterbro street with large windows, separate dining room, and premium furnishings.', 'Rosenvaengets Alle 6, 2100 Copenhagen O', 'Luxury Apartment', 2, 1.5, 'property25.jpg', 1680.00, 'Apartment', 'unavailable', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Osterbro' LIMIT 1), 'Osterbro Bright Micro Studio', 'Efficient micro studio with kitchenette, workspace, storage solutions, and quick access to Nordhavn station.', 'Classensgade 72, 2100 Copenhagen O', 'Micro Studio', 1, 1.0, 'property26.jpg', 675.00, 'Studio', 'available', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area LIKE '%rrebro' LIMIT 1), 'Norrebro Creative Loft Apartment', 'Characterful loft-style apartment near Stefansgade with exposed beams, open kitchen, and flexible work space.', 'Stefansgade 35, 2200 Copenhagen N', 'Loft Apartment', 2, 1.0, 'property27.jpg', 1295.00, 'Apartment', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area LIKE '%rrebro' LIMIT 1), 'Norrebro Student Twin Room', 'Budget twin room suited for students with shared bathroom, shared kitchen, bicycle parking, and bus connections.', 'Mimersgade 46, 2200 Copenhagen N', 'Student Room', 1, 1.0, 'property28.jpg', 510.00, 'Student Housing', 'available', 'pending'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Valby' LIMIT 1), 'Valby Garden Apartment', 'Ground-floor apartment with garden access, two bedrooms, bright kitchen, and a quiet residential setting.', 'Hoffdingsvej 17, 2500 Valby', 'Garden Apartment', 2, 1.0, 'property29.jpg', 1125.00, 'Family Housing', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Valby' LIMIT 1), 'Valby Station Modern Studio', 'Modern studio close to Valby Station with furnished sleeping area, compact kitchen, and good commuter access.', 'Mosedalvej 9, 2500 Valby', 'Studio Apartment', 1, 1.0, 'property30.jpg', 760.00, 'Studio', 'unavailable', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Sydhavn' LIMIT 1), 'Sydhavn Harbor Family Flat', 'Three-bedroom flat in a newer harbor district with balcony, elevator, playground nearby, and generous storage.', 'Sluseholmen 18, 2450 Copenhagen SV', 'Family Apartment', 3, 1.5, 'property31.jpg', 1510.00, 'Family Housing', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Sydhavn' LIMIT 1), 'Sydhavn Affordable Studio Room', 'Affordable furnished studio room with kitchenette, shared laundry, and easy bike routes to central Copenhagen.', 'Badehavnsgade 31, 2450 Copenhagen SV', 'Studio Room', 1, 1.0, 'property32.jpg', 620.00, 'Student Housing', 'available', 'rejected'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Nordhavn' LIMIT 1), 'Nordhavn Minimalist Studio', 'Minimalist furnished studio in Nordhavn with balcony, modern bathroom, and fast metro access to the city center.', 'Kattegatvej 41, 2150 Nordhavn', 'Studio Apartment', 1, 1.0, 'property33.jpg', 1090.00, 'Studio', 'available', 'approved'
    UNION ALL SELECT 2, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Islands Brygge' LIMIT 1), 'Islands Brygge Two-Bedroom Apartment', 'Comfortable two-bedroom apartment near the waterfront with balcony, open living room, and excellent bike access.', 'Artillerivej 68, 2300 Copenhagen S', 'Apartment', 2, 1.0, 'property34.jpg', 1340.00, 'Apartment', 'available', 'approved'
    UNION ALL SELECT 1, (SELECT location_id FROM locations WHERE city = 'Copenhagen' AND area = 'Copenhagen K' LIMIT 1), 'Copenhagen K Boutique Serviced Apartment', 'Boutique serviced apartment with weekly cleaning option, central location, premium kitchen, and classic city charm.', 'Laederstraede 12, 1201 Copenhagen K', 'Serviced Apartment', 1, 1.0, 'property15.jpg', 1550.00, 'Apartment', 'unavailable', 'pending'
) AS new_properties
WHERE NOT EXISTS (
    SELECT 1 FROM properties p WHERE p.title = new_properties.title
);
