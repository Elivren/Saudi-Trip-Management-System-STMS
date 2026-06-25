-- =============================================
-- STMS - Saudi Trip Management System Database
-- =============================================

CREATE DATABASE IF NOT EXISTS stms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stms_db;

-- ===== Users Table =====
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('tourist', 'guide', 'admin') NOT NULL DEFAULT 'tourist',
    city VARCHAR(100),
    region VARCHAR(100),
    profile_picture VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===== Trips Table =====
CREATE TABLE IF NOT EXISTS trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guide_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    location_city VARCHAR(100),
    location_region VARCHAR(100),
    cover_image VARCHAR(255),
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_tourists INT NOT NULL DEFAULT 10,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration VARCHAR(50),
    status ENUM('open', 'fully_booked', 'completed', 'cancelled') NOT NULL DEFAULT 'open',
    itinerary LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guide_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Bookings Table =====
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL DEFAULT 0,
    tourist_id INT NOT NULL,
    booking_type ENUM('trip', 'package', 'self_planned') NOT NULL DEFAULT 'trip',
    package_id INT DEFAULT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    num_people INT NOT NULL DEFAULT 1,
    status ENUM('pending', 'accepted', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT,
    include_accommodation TINYINT(1) NOT NULL DEFAULT 0,
    include_breakfast TINYINT(1) NOT NULL DEFAULT 0,
    include_lunch TINYINT(1) NOT NULL DEFAULT 0,
    include_dinner TINYINT(1) NOT NULL DEFAULT 0,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    duration_days INT DEFAULT NULL,
    extension_days INT NOT NULL DEFAULT 0,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tourist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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

-- ===== Guide Packages Table =====
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

-- ===== Package Cities Table =====
CREATE TABLE IF NOT EXISTS package_cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    days_in_city INT NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Package Itinerary Table =====
CREATE TABLE IF NOT EXISTS package_itinerary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    day_number INT NOT NULL,
    city_name VARCHAR(100),
    title VARCHAR(200) NOT NULL,
    description TEXT,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Package Images Table =====
CREATE TABLE IF NOT EXISTS package_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Booking Cities Table (for self-planned bookings) =====
CREATE TABLE IF NOT EXISTS booking_cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    days_in_city INT NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Reviews Table =====
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    tourist_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    FOREIGN KEY (tourist_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Feedback / Contact Messages =====
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(150),
    subject VARCHAR(200),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===== FAQs Table =====
CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- SEED DATA
-- =============================================

-- Admin user (password: admin123)
INSERT INTO users (full_name, email, phone, password, role, city, status) VALUES
('System Admin', 'admin@stms.sa', '+966500000000', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgY1OKFGq1V1UiAmKMEK8GW4xkG', 'admin', 'Riyadh', 'active');

-- Guide users (password: guide123)
INSERT INTO users (full_name, email, phone, password, role, city, region, status) VALUES
('Ahmed Al-Rashid', 'ahmed@stms.sa', '+966501111111', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgY1OKFGq1V1UiAmKMEK8GW4xkG', 'guide', 'Jeddah', 'Western', 'active'),
('Fatimah Al-Harbi', 'fatimah@stms.sa', '+966502222222', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgY1OKFGq1V1UiAmKMEK8GW4xkG', 'guide', 'Abha', 'Southern', 'active'),
('Omar Al-Dosari', 'omar@stms.sa', '+966503333333', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgY1OKFGq1V1UiAmKMEK8GW4xkG', 'guide', 'AlUla', 'Northern', 'active');

-- Tourist users (password: tourist123)
INSERT INTO users (full_name, email, phone, password, role, city, region, status) VALUES
('Sarah Johnson', 'sarah@email.com', '+966504444444', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgY1OKFGq1V1UiAmKMEK8GW4xkG', 'tourist', 'Riyadh', 'Central', 'active'),
('Mohammed Ali', 'mohammed@email.com', '+966505555555', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgY1OKFGq1V1UiAmKMEK8GW4xkG', 'tourist', 'Dammam', 'Eastern', 'active'),
('Emily Chen', 'emily@email.com', '+966506666666', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgY1OKFGq1V1UiAmKMEK8GW4xkG', 'tourist', 'Jeddah', 'Western', 'active');

-- Sample Trips
INSERT INTO trips (guide_id, title, description, location_city, location_region, cover_image, price, max_tourists, start_date, end_date, duration, status, itinerary) VALUES
(2, 'Desert Safari Adventure in Red Sand Dunes', 'Experience the breathtaking beauty of Saudi Arabia''s vast desert landscapes. Ride camels across golden dunes, enjoy traditional Bedouin hospitality, and witness a spectacular desert sunset that paints the sky in shades of amber and crimson.', 'Riyadh', 'Central', 'https://images.unsplash.com/photo-1451337516015-6b6e9a44a8a3?w=800', 450.00, 15, '2026-03-15', '2026-03-17', '3 Days', 'open',
'[{"day":1,"title":"Arrival & Desert Camp Setup","desc":"Meet at Riyadh meeting point, drive to desert camp, camel rides and dune bashing"},{"day":2,"title":"Full Desert Exploration","desc":"Sunrise photography, sandboarding, traditional lunch, stargazing at night"},{"day":3,"title":"Farewell & Return","desc":"Morning yoga in the desert, breakfast, return to Riyadh by noon"}]'),

(2, 'Historic Jeddah Al-Balad Walking Tour', 'Walk through the ancient streets of Al-Balad, Jeddah''s historic district and UNESCO World Heritage Site. Discover coral-stone architecture, vibrant souks, and centuries of trade history along the Red Sea coast.', 'Jeddah', 'Western', 'https://images.unsplash.com/photo-1578895101408-1a36b834405b?w=800', 180.00, 20, '2026-03-20', '2026-03-20', '1 Day', 'open',
'[{"day":1,"title":"Al-Balad Heritage Walk","desc":"Morning tour of Nassif House, Al-Shafi''i Mosque, Souk Al-Alawi, traditional lunch, visit Jeddah Corniche at sunset"}]'),

(3, 'Abha Mountain Retreat & Cloud Forest', 'Escape to the cool highlands of Abha, the jewel of Saudi Arabia''s south. Explore lush green mountains, hanging villages, and the mystical cloud forests of the Asir region, with temperatures that offer a refreshing break from the heat.', 'Abha', 'Southern', 'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800', 600.00, 12, '2026-04-01', '2026-04-04', '4 Days', 'open',
'[{"day":1,"title":"Arrival in Abha","desc":"Airport pickup, check-in at mountain lodge, evening city tour"},{"day":2,"title":"Hanging Village & Habala","desc":"Visit the famous Habala hanging village via cable car, local crafts market"},{"day":3,"title":"Cloud Forest Trek","desc":"Guided hike through Asir National Park, traditional Asiri lunch"},{"day":4,"title":"Al-Soudah Peak & Departure","desc":"Visit Al-Soudah, highest point in Saudi Arabia, farewell lunch, departure"}]'),

(4, 'AlUla Heritage & Hegra Discovery', 'Journey through time in AlUla, home to Saudi Arabia''s first UNESCO World Heritage Site — Hegra (Mada''in Saleh). Marvel at 2,000-year-old Nabataean tombs carved into sandstone mountains and explore the stunning desert canyons.', 'AlUla', 'Northern', 'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800', 850.00, 10, '2026-04-10', '2026-04-13', '4 Days', 'open',
'[{"day":1,"title":"Welcome to AlUla","desc":"Arrive at AlUla, check in at desert resort, sunset at Elephant Rock"},{"day":2,"title":"Hegra Archaeological Site","desc":"Full day exploring Hegra tombs, Jabal Ithlib, ancient inscriptions"},{"day":3,"title":"Old Town & Oasis","desc":"Walk through AlUla Old Town, date palm oasis, Dadan kingdom ruins"},{"day":4,"title":"Stargazing & Farewell","desc":"Morning at leisure, afternoon desert drive, evening stargazing experience, departure"}]'),

(3, 'NEOM Beach & Coral Reef Snorkeling', 'Discover the pristine waters of the Red Sea near the NEOM project area. Snorkel among vibrant coral reefs, relax on untouched beaches, and experience the future of Saudi Arabian tourism in this stunning coastal paradise.', 'Tabuk', 'Northern', 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800', 500.00, 8, '2026-05-01', '2026-05-03', '3 Days', 'open',
'[{"day":1,"title":"Coastal Arrival","desc":"Drive to NEOM coast area, beach setup, sunset swim"},{"day":2,"title":"Reef Snorkeling Day","desc":"Full day snorkeling at two reef sites, underwater photography, beach BBQ"},{"day":3,"title":"Beach Relaxation & Return","desc":"Morning kayaking, beach leisure, return journey"}]'),

(2, 'Riyadh City Lights & Culture Tour', 'Experience the modern pulse of Saudi Arabia in its capital city. From the iconic Kingdom Tower to the historic Diriyah district, this tour blends cutting-edge architecture with deep cultural heritage.', 'Riyadh', 'Central', 'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800', 220.00, 25, '2026-03-25', '2026-03-26', '2 Days', 'open',
'[{"day":1,"title":"Modern Riyadh","desc":"Kingdom Tower Sky Bridge, Boulevard City, National Museum, dinner at Tahlia Street"},{"day":2,"title":"Historic Diriyah","desc":"At-Turaif UNESCO site, Bujairi Terrace, traditional Saudi lunch, departure"}]');

-- Sample Bookings
INSERT INTO bookings (trip_id, tourist_id, booking_type, num_people, status, total_price) VALUES
(1, 5, 'trip', 2, 'accepted', 900.00),
(1, 6, 'trip', 1, 'pending', 450.00),
(3, 5, 'trip', 3, 'accepted', 1800.00),
(4, 7, 'trip', 2, 'pending', 1700.00),
(2, 7, 'trip', 1, 'accepted', 180.00);

-- City Dynamic Pricing Seed Data
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

-- Guide Mohammed (password: guide123)
INSERT INTO users (full_name, email, phone, password, role, city, region, status) VALUES
('Mohammed Al-Qahtani', 'mohammed@stms.sa', '+966507777777', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgY1OKFGq1V1UiAmKMEK8GW4xkG', 'guide', 'Riyadh', 'Central', 'active');

-- Sample Package 1: Saudi Grand Tour by Mohammed (guide_id = 8)
INSERT INTO packages (guide_id, title, description, cover_image, duration_days, is_extendable, base_price, max_tourists, includes_transport) VALUES
(8, 'Saudi Grand Tour: Jeddah, Riyadh & AlUla',
'Experience the best of Saudi Arabia in this comprehensive 10-day package. Start in the historic port city of Jeddah, explore the vibrant capital Riyadh, and discover the ancient wonders of AlUla. This fixed-duration package includes transportation between all cities and expert guidance throughout your journey.',
'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800',
10, 0, 4500.00, 12, 1);

INSERT INTO package_cities (package_id, city_name, days_in_city, sort_order) VALUES
(1, 'Jeddah', 3, 1),
(1, 'Riyadh', 4, 2),
(1, 'AlUla', 3, 3);

INSERT INTO package_itinerary (package_id, day_number, city_name, title, description) VALUES
(1, 1, 'Jeddah', 'Welcome to Jeddah', 'Airport pickup, check-in at hotel, evening walk along Jeddah Corniche'),
(1, 2, 'Jeddah', 'Historic Al-Balad Tour', 'Full day exploring UNESCO World Heritage Al-Balad district, Nassif House, traditional souks'),
(1, 3, 'Jeddah', 'Red Sea & Departure', 'Morning at Red Sea waterfront, King Fahd Fountain, afternoon flight to Riyadh'),
(1, 4, 'Riyadh', 'Arrival in the Capital', 'Arrive in Riyadh, check-in, evening visit to Kingdom Tower Sky Bridge'),
(1, 5, 'Riyadh', 'Modern Riyadh', 'National Museum, Boulevard City, Tahlia Street dining experience'),
(1, 6, 'Riyadh', 'Historic Diriyah', 'Full day at At-Turaif UNESCO site, Bujairi Terrace, traditional Saudi lunch'),
(1, 7, 'Riyadh', 'Edge of the World & Transfer', 'Morning trip to Edge of the World viewpoint, afternoon flight to AlUla'),
(1, 8, 'AlUla', 'Welcome to AlUla', 'Arrive in AlUla, desert resort check-in, sunset at Elephant Rock'),
(1, 9, 'AlUla', 'Hegra Archaeological Site', 'Full day exploring Hegra tombs, Jabal Ithlib, ancient Nabataean inscriptions'),
(1, 10, 'AlUla', 'Old Town & Farewell', 'AlUla Old Town walk, date palm oasis, farewell dinner under the stars, departure');

INSERT INTO package_images (package_id, image_url, sort_order) VALUES
(1, 'https://images.unsplash.com/photo-1578895101408-1a36b834405b?w=800', 1),
(1, 'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800', 2),
(1, 'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800', 3);

-- Sample Package 2: Jeddah & Abha Explorer by Ahmed (guide_id = 2)
INSERT INTO packages (guide_id, title, description, cover_image, duration_days, is_extendable, base_price, max_tourists, includes_transport) VALUES
(2, 'Jeddah & Abha Explorer',
'A beautiful 7-day journey from the coastal charm of Jeddah to the cool mountain heights of Abha. Experience the contrast of Red Sea beaches and cloud forests in one amazing trip.',
'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800',
7, 0, 3200.00, 15, 1);

INSERT INTO package_cities (package_id, city_name, days_in_city, sort_order) VALUES
(2, 'Jeddah', 3, 1),
(2, 'Abha', 4, 2);

INSERT INTO package_itinerary (package_id, day_number, city_name, title, description) VALUES
(2, 1, 'Jeddah', 'Arrival & Corniche', 'Airport pickup, hotel check-in, evening at Jeddah Corniche'),
(2, 2, 'Jeddah', 'Al-Balad Heritage Walk', 'Explore the historic Al-Balad district, lunch at traditional restaurant'),
(2, 3, 'Jeddah', 'Red Sea Day & Travel', 'Morning Red Sea activities, afternoon flight to Abha'),
(2, 4, 'Abha', 'Welcome to Abha', 'Arrive in Abha, mountain lodge check-in, evening city tour'),
(2, 5, 'Abha', 'Habala Hanging Village', 'Cable car to the famous hanging village, local crafts market'),
(2, 6, 'Abha', 'Cloud Forest Trek', 'Guided hike through Asir National Park, traditional Asiri lunch'),
(2, 7, 'Abha', 'Al-Soudah & Farewell', 'Visit highest point in Saudi Arabia, farewell lunch, departure');

INSERT INTO package_images (package_id, image_url, sort_order) VALUES
(2, 'https://images.unsplash.com/photo-1578895101408-1a36b834405b?w=800', 1),
(2, 'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800', 2);

-- Sample Reviews
INSERT INTO reviews (trip_id, tourist_id, rating, comment) VALUES
(1, 5, 5, 'Absolutely magical experience! The desert sunset was unlike anything I have ever seen. Ahmed was an incredible guide who knew everything about the history and ecology of the area.'),
(2, 7, 4, 'A wonderful walking tour through history. The coral architecture in Al-Balad is truly unique. Would have loved more time at the souks though.'),
(3, 5, 5, 'Abha is a hidden gem! The cloud forests and hanging villages were breathtaking. Fatimah was so knowledgeable and made us feel welcome everywhere we went.');

-- Sample FAQs
INSERT INTO faqs (question, answer, sort_order) VALUES
('How do I book a trip?', 'Simply browse available trips, select one you like, choose your preferred date and number of people, and click Book Trip. You will receive a confirmation once the guide accepts your booking.', 1),
('Can I cancel a booking?', 'Yes, you can cancel a booking up to 48 hours before the trip start date for a full refund. Cancellations within 48 hours may be subject to a cancellation fee.', 2),
('How do I become a tour guide?', 'Register as a Guide on our platform, complete your profile, and start creating trips. Our admin team will review and approve your account within 24 hours.', 3),
('Is it safe to travel in Saudi Arabia?', 'Saudi Arabia is one of the safest countries in the world for tourists. Our guides are certified and experienced professionals who ensure your safety throughout every trip.', 4),
('What payment methods are accepted?', 'We accept major credit cards, Apple Pay, mada debit cards, and bank transfers. All payments are processed securely through our platform.', 5);
