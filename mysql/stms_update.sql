-- =============================================
-- STMS - Tourism Booking System Update
-- Dynamic Pricing, Packages, Self-Planned Bookings
-- =============================================

USE stms_db;

-- ===== City Dynamic Pricing Table =====
CREATE TABLE IF NOT EXISTS city_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL UNIQUE,
    base_price_per_day DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    accommodation_per_night DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    breakfast_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    lunch_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    dinner_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    transportation_per_day DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===== Guide Packages Table (multi-city bundles) =====
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guide_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    duration_days INT NOT NULL DEFAULT 10,
    is_extendable TINYINT(1) NOT NULL DEFAULT 0,
    base_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_tourists INT NOT NULL DEFAULT 10,
    includes_transport TINYINT(1) NOT NULL DEFAULT 1,
    includes_accommodation TINYINT(1) NOT NULL DEFAULT 0,
    includes_meals VARCHAR(100) DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guide_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Package Cities (cities included in a package) =====
CREATE TABLE IF NOT EXISTS package_cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    days_in_city INT NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Package Itinerary (day-by-day schedule) =====
CREATE TABLE IF NOT EXISTS package_itinerary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    day_number INT NOT NULL,
    city_name VARCHAR(100),
    title VARCHAR(200) NOT NULL,
    description TEXT,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Package Images =====
CREATE TABLE IF NOT EXISTS package_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Tourist Bookings Update =====
-- Add new columns to bookings table for enhanced booking
ALTER TABLE bookings
    ADD COLUMN booking_type ENUM('trip', 'package', 'self_planned') NOT NULL DEFAULT 'trip' AFTER tourist_id,
    ADD COLUMN package_id INT DEFAULT NULL AFTER booking_type,
    ADD COLUMN include_accommodation TINYINT(1) NOT NULL DEFAULT 0 AFTER notes,
    ADD COLUMN include_breakfast TINYINT(1) NOT NULL DEFAULT 0 AFTER include_accommodation,
    ADD COLUMN include_lunch TINYINT(1) NOT NULL DEFAULT 0 AFTER include_breakfast,
    ADD COLUMN include_dinner TINYINT(1) NOT NULL DEFAULT 0 AFTER include_lunch,
    ADD COLUMN total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER include_dinner,
    ADD COLUMN duration_days INT DEFAULT NULL AFTER total_price,
    ADD COLUMN extension_days INT NOT NULL DEFAULT 0 AFTER duration_days,
    ADD COLUMN start_date DATE DEFAULT NULL AFTER extension_days,
    ADD COLUMN end_date DATE DEFAULT NULL AFTER start_date;

-- ===== Self-Planned Booking Cities =====
CREATE TABLE IF NOT EXISTS booking_cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    days_in_city INT NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Seed City Pricing Data =====
INSERT INTO city_pricing (city_name, base_price_per_day, accommodation_per_night, breakfast_price, lunch_price, dinner_price, transportation_per_day) VALUES
('Riyadh',  350.00, 450.00, 60.00, 95.00, 120.00, 150.00),
('Jeddah',  300.00, 400.00, 55.00, 85.00, 110.00, 130.00),
('Makkah',  320.00, 500.00, 55.00, 90.00, 115.00, 120.00),
('Madinah', 280.00, 420.00, 50.00, 80.00, 100.00, 110.00),
('Dammam',  250.00, 320.00, 45.00, 75.00, 95.00, 120.00),
('Abha',    220.00, 280.00, 40.00, 70.00, 85.00, 100.00),
('Tabuk',   200.00, 260.00, 40.00, 65.00, 80.00, 90.00),
('AlUla',   180.00, 240.00, 35.00, 60.00, 75.00, 80.00),
('Taif',    200.00, 270.00, 40.00, 65.00, 80.00, 90.00),
('Yanbu',   190.00, 250.00, 35.00, 60.00, 75.00, 85.00);

-- ===== Sample Package: Guide Mohammed's 10-Day Package =====
-- First, insert a guide named Mohammed (password: guide123)
INSERT INTO users (full_name, email, phone, password, role, city, region, status) VALUES
('Mohammed Al-Qahtani', 'mohammed@stms.sa', '+966507777777', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgY1OKFGq1V1UiAmKMEK8GW4xkG', 'guide', 'Riyadh', 'Central', 'active');

-- Get Mohammed's ID (will be 8 based on existing seed data: 1 admin + 3 guides + 3 tourists + 1 new = 8)
SET @mohammed_id = LAST_INSERT_ID();

-- Create Mohammed's 10-day package
INSERT INTO packages (guide_id, title, description, cover_image, duration_days, is_extendable, base_price, max_tourists, includes_transport) VALUES
(@mohammed_id, 'Saudi Grand Tour: Jeddah, Riyadh & AlUla', 
'Experience the best of Saudi Arabia in this comprehensive 10-day package. Start in the historic port city of Jeddah, explore the vibrant capital Riyadh, and discover the ancient wonders of AlUla. This fixed-duration package includes transportation between all cities and expert guidance throughout your journey.',
'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800',
10, 0, 4500.00, 12, 1);

SET @pkg_id = LAST_INSERT_ID();

-- Package cities
INSERT INTO package_cities (package_id, city_name, days_in_city, sort_order) VALUES
(@pkg_id, 'Jeddah', 3, 1),
(@pkg_id, 'Riyadh', 4, 2),
(@pkg_id, 'AlUla', 3, 3);

-- Package itinerary
INSERT INTO package_itinerary (package_id, day_number, city_name, title, description) VALUES
(@pkg_id, 1, 'Jeddah', 'Welcome to Jeddah', 'Airport pickup, check-in at hotel, evening walk along Jeddah Corniche'),
(@pkg_id, 2, 'Jeddah', 'Historic Al-Balad Tour', 'Full day exploring UNESCO World Heritage Al-Balad district, Nassif House, traditional souks'),
(@pkg_id, 3, 'Jeddah', 'Red Sea & Departure', 'Morning at Red Sea waterfront, King Fahd Fountain, afternoon flight to Riyadh'),
(@pkg_id, 4, 'Riyadh', 'Arrival in the Capital', 'Arrive in Riyadh, check-in, evening visit to Kingdom Tower Sky Bridge'),
(@pkg_id, 5, 'Riyadh', 'Modern Riyadh', 'National Museum, Boulevard City, Tahlia Street dining experience'),
(@pkg_id, 6, 'Riyadh', 'Historic Diriyah', 'Full day at At-Turaif UNESCO site, Bujairi Terrace, traditional Saudi lunch'),
(@pkg_id, 7, 'Riyadh', 'Edge of the World & Transfer', 'Morning trip to Edge of the World viewpoint, afternoon flight to AlUla'),
(@pkg_id, 8, 'AlUla', 'Welcome to AlUla', 'Arrive in AlUla, desert resort check-in, sunset at Elephant Rock'),
(@pkg_id, 9, 'AlUla', 'Hegra Archaeological Site', 'Full day exploring Hegra tombs, Jabal Ithlib, ancient Nabataean inscriptions'),
(@pkg_id, 10, 'AlUla', 'Old Town & Farewell', 'AlUla Old Town walk, date palm oasis, farewell dinner under the stars, departure');

-- Package images
INSERT INTO package_images (package_id, image_url, sort_order) VALUES
(@pkg_id, 'https://images.unsplash.com/photo-1578895101408-1a36b834405b?w=800', 1),
(@pkg_id, 'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800', 2),
(@pkg_id, 'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800', 3);

-- Second sample package by Ahmed
INSERT INTO packages (guide_id, title, description, cover_image, duration_days, is_extendable, base_price, max_tourists, includes_transport) VALUES
(2, 'Jeddah & Abha Explorer', 
'A beautiful 7-day journey from the coastal charm of Jeddah to the cool mountain heights of Abha. Experience the contrast of Red Sea beaches and cloud forests in one amazing trip.',
'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800',
7, 0, 3200.00, 15, 1);

SET @pkg2_id = LAST_INSERT_ID();

INSERT INTO package_cities (package_id, city_name, days_in_city, sort_order) VALUES
(@pkg2_id, 'Jeddah', 3, 1),
(@pkg2_id, 'Abha', 4, 2);

INSERT INTO package_itinerary (package_id, day_number, city_name, title, description) VALUES
(@pkg2_id, 1, 'Jeddah', 'Arrival & Corniche', 'Airport pickup, hotel check-in, evening at Jeddah Corniche'),
(@pkg2_id, 2, 'Jeddah', 'Al-Balad Heritage Walk', 'Explore the historic Al-Balad district, lunch at traditional restaurant'),
(@pkg2_id, 3, 'Jeddah', 'Red Sea Day & Travel', 'Morning Red Sea activities, afternoon flight to Abha'),
(@pkg2_id, 4, 'Abha', 'Welcome to Abha', 'Arrive in Abha, mountain lodge check-in, evening city tour'),
(@pkg2_id, 5, 'Abha', 'Habala Hanging Village', 'Cable car to the famous hanging village, local crafts market'),
(@pkg2_id, 6, 'Abha', 'Cloud Forest Trek', 'Guided hike through Asir National Park, traditional Asiri lunch'),
(@pkg2_id, 7, 'Abha', 'Al-Soudah & Farewell', 'Visit highest point in Saudi Arabia, farewell lunch, departure');

INSERT INTO package_images (package_id, image_url, sort_order) VALUES
(@pkg2_id, 'https://images.unsplash.com/photo-1578895101408-1a36b834405b?w=800', 1),
(@pkg2_id, 'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800', 2);
