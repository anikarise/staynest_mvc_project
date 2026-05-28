USE staynest_db;

UPDATE properties
SET image = CASE title
    WHEN 'Frederiksberg Classic Luxury Apartment' THEN 'property1.jpg'
    WHEN 'Frederiksberg Student Studio' THEN 'property2.jpg'
    WHEN 'Amager Beach Modern Studio' THEN 'property3.jpg'
    WHEN 'Amager Family Apartment Near Metro' THEN 'property4.jpg'
    WHEN 'Vesterbro Premium City Room' THEN 'property5.jpg'
    WHEN 'Vesterbro Renovated Two-Bed Apartment' THEN 'property6.jpg'
    WHEN 'Osterbro Parkside Family Apartment' THEN 'property7.jpg'
    WHEN 'Osterbro Quiet Student Room' THEN 'property8.jpg'
    WHEN 'Norrebro Urban Studio Loft' THEN 'property9.jpg'
    WHEN 'Norrebro Affordable Shared Apartment' THEN 'property10.jpg'
    WHEN 'Valby Modern Family Home' THEN 'property11.jpg'
    WHEN 'Valby Budget Student Apartment' THEN 'property12.jpg'
    WHEN 'Sydhavn Waterfront Studio' THEN 'property13.jpg'
    WHEN 'Sydhavn New Family Apartment' THEN 'property14.jpg'
    WHEN 'Nordhavn Luxury Harbor Apartment' THEN 'property15.jpg'
    WHEN 'Nordhavn Premium Business Room' THEN 'property16.jpg'
    WHEN 'Islands Brygge Canal Apartment' THEN 'property17.jpg'
    WHEN 'Islands Brygge Student Studio' THEN 'property18.jpg'
    WHEN 'Copenhagen K Historic City Apartment' THEN 'property19.jpg'
    WHEN 'Copenhagen K Premium City Room' THEN 'property20.jpg'
    WHEN 'Frederiksberg Rooftop Penthouse' THEN 'property21.jpg'
    WHEN 'Frederiksberg Compact Single Room' THEN 'property22.jpg'
    WHEN 'Vesterbro Designer Studio' THEN 'property23.jpg'
    WHEN 'Vesterbro Family Courtyard Apartment' THEN 'property24.jpg'
    WHEN 'Osterbro Embassy Quarter Apartment' THEN 'property25.jpg'
    WHEN 'Osterbro Bright Micro Studio' THEN 'property26.jpg'
    WHEN 'Norrebro Creative Loft Apartment' THEN 'property27.jpg'
    WHEN 'Norrebro Student Twin Room' THEN 'property28.jpg'
    WHEN 'Valby Garden Apartment' THEN 'property29.jpg'
    WHEN 'Valby Station Modern Studio' THEN 'property30.jpg'
    WHEN 'Sydhavn Harbor Family Flat' THEN 'property31.jpg'
    WHEN 'Sydhavn Affordable Studio Room' THEN 'property32.jpg'
    ELSE image
END
WHERE title IN (
    'Frederiksberg Classic Luxury Apartment',
    'Frederiksberg Student Studio',
    'Amager Beach Modern Studio',
    'Amager Family Apartment Near Metro',
    'Vesterbro Premium City Room',
    'Vesterbro Renovated Two-Bed Apartment',
    'Osterbro Parkside Family Apartment',
    'Osterbro Quiet Student Room',
    'Norrebro Urban Studio Loft',
    'Norrebro Affordable Shared Apartment',
    'Valby Modern Family Home',
    'Valby Budget Student Apartment',
    'Sydhavn Waterfront Studio',
    'Sydhavn New Family Apartment',
    'Nordhavn Luxury Harbor Apartment',
    'Nordhavn Premium Business Room',
    'Islands Brygge Canal Apartment',
    'Islands Brygge Student Studio',
    'Copenhagen K Historic City Apartment',
    'Copenhagen K Premium City Room',
    'Frederiksberg Rooftop Penthouse',
    'Frederiksberg Compact Single Room',
    'Vesterbro Designer Studio',
    'Vesterbro Family Courtyard Apartment',
    'Osterbro Embassy Quarter Apartment',
    'Osterbro Bright Micro Studio',
    'Norrebro Creative Loft Apartment',
    'Norrebro Student Twin Room',
    'Valby Garden Apartment',
    'Valby Station Modern Studio',
    'Sydhavn Harbor Family Flat',
    'Sydhavn Affordable Studio Room'
);

UPDATE properties
SET image = NULL
WHERE image IN ('property33.jpg', 'property34.jpg', 'property35.jpg');
