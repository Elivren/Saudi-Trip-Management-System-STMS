<?php
/**
 * STMS Database Installer
 * Visit http://localhost/02stms/install.php in your browser to set up the database.
 * Make sure Apache and MySQL are running in XAMPP Control Panel first.
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'stms_db';

$messages = [];
$errors = [];

try {
    // Step 1: Connect without database
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $messages[] = "Connected to MySQL server successfully.";

    // Step 2: Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = "Database '$dbname' created (or already exists).";

    // Step 3: Use database
    $pdo->exec("USE `$dbname`");

    // Step 4: Drop old tables (in correct order due to foreign keys)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS booking_cities");
    $pdo->exec("DROP TABLE IF EXISTS package_images");
    $pdo->exec("DROP TABLE IF EXISTS package_itinerary");
    $pdo->exec("DROP TABLE IF EXISTS package_cities");
    $pdo->exec("DROP TABLE IF EXISTS packages");
    $pdo->exec("DROP TABLE IF EXISTS city_pricing");
    $pdo->exec("DROP TABLE IF EXISTS reviews");
    $pdo->exec("DROP TABLE IF EXISTS bookings");
    $pdo->exec("DROP TABLE IF EXISTS trips");
    $pdo->exec("DROP TABLE IF EXISTS feedback");
    $pdo->exec("DROP TABLE IF EXISTS faqs");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $messages[] = "Old tables dropped (fresh install).";

    // Step 5: Create tables
    $pdo->exec("CREATE TABLE users (
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
    ) ENGINE=InnoDB");
    $messages[] = "Table 'users' created.";

    $pdo->exec("CREATE TABLE trips (
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
    ) ENGINE=InnoDB");
    $messages[] = "Table 'trips' created.";

    $pdo->exec("CREATE TABLE bookings (
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
    ) ENGINE=InnoDB");
    $messages[] = "Table 'bookings' created.";

    // City Dynamic Pricing
    $pdo->exec("CREATE TABLE city_pricing (
        id INT AUTO_INCREMENT PRIMARY KEY,
        city_name VARCHAR(100) NOT NULL UNIQUE,
        base_price_per_day DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        accommodation_per_night DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        breakfast_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        lunch_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        dinner_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        transportation_per_day DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $messages[] = "Table 'city_pricing' created.";

    // Guide Packages
    $pdo->exec("CREATE TABLE packages (
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
    ) ENGINE=InnoDB");
    $messages[] = "Table 'packages' created.";

    // Package Cities
    $pdo->exec("CREATE TABLE package_cities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        package_id INT NOT NULL,
        city_name VARCHAR(100) NOT NULL,
        days_in_city INT NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 0,
        FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $messages[] = "Table 'package_cities' created.";

    // Package Itinerary
    $pdo->exec("CREATE TABLE package_itinerary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        package_id INT NOT NULL,
        day_number INT NOT NULL,
        city_name VARCHAR(100),
        title VARCHAR(200) NOT NULL,
        description TEXT,
        FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $messages[] = "Table 'package_itinerary' created.";

    // Package Images
    $pdo->exec("CREATE TABLE package_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        package_id INT NOT NULL,
        image_url VARCHAR(500) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $messages[] = "Table 'package_images' created.";

    // Booking Cities (for self-planned bookings)
    $pdo->exec("CREATE TABLE booking_cities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        city_name VARCHAR(100) NOT NULL,
        days_in_city INT NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 0,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $messages[] = "Table 'booking_cities' created.";

    $pdo->exec("CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        trip_id INT NOT NULL,
        tourist_id INT NOT NULL,
        rating INT NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
        FOREIGN KEY (tourist_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    $messages[] = "Table 'reviews' created.";

    $pdo->exec("CREATE TABLE feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(150),
        subject VARCHAR(200),
        message TEXT,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $messages[] = "Table 'feedback' created.";

    $pdo->exec("CREATE TABLE faqs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question VARCHAR(500) NOT NULL,
        answer TEXT NOT NULL,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    $messages[] = "Table 'faqs' created.";

    // Step 6: Seed data
    {
        // Generate proper bcrypt hashes
        $adminHash   = password_hash('admin123', PASSWORD_DEFAULT);
        $guideHash   = password_hash('guide123', PASSWORD_DEFAULT);
        $touristHash = password_hash('tourist123', PASSWORD_DEFAULT);

        // Admin
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, city, status) VALUES (?, ?, ?, ?, 'admin', 'Riyadh', 'active')");
        $stmt->execute(['System Admin', 'admin@stms.sa', '+966500000000', $adminHash]);

        // Guides
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, city, region, status) VALUES (?, ?, ?, ?, 'guide', ?, ?, 'active')");
        $stmt->execute(['Ahmed Al-Rashid', 'ahmed@stms.sa', '+966501111111', $guideHash, 'Jeddah', 'Western']);
        $stmt->execute(['Fatimah Al-Harbi', 'fatimah@stms.sa', '+966502222222', $guideHash, 'Abha', 'Southern']);
        $stmt->execute(['Omar Al-Dosari', 'omar@stms.sa', '+966503333333', $guideHash, 'AlUla', 'Northern']);

        // Tourists
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, city, region, status) VALUES (?, ?, ?, ?, 'tourist', ?, ?, 'active')");
        $stmt->execute(['Sarah Johnson', 'sarah@email.com', '+966504444444', $touristHash, 'Riyadh', 'Central']);
        $stmt->execute(['Mohammed Ali', 'mohammed@email.com', '+966505555555', $touristHash, 'Dammam', 'Eastern']);
        $stmt->execute(['Emily Chen', 'emily@email.com', '+966506666666', $touristHash, 'Jeddah', 'Western']);

        $messages[] = "Seed users created (admin, 3 guides, 3 tourists).";

        // Trips (guide_id 2 = Ahmed, 3 = Fatimah, 4 = Omar)
        $trips = [
            [2, 'Desert Safari Adventure in Red Sand Dunes', 'Experience the breathtaking beauty of Saudi Arabia\'s vast desert landscapes. Ride camels across golden dunes, enjoy traditional Bedouin hospitality, and witness a spectacular desert sunset that paints the sky in shades of amber and crimson.', 'Riyadh', 'Central', 'https://images.unsplash.com/photo-1451337516015-6b6e9a44a8a3?w=800', 450.00, 15, '2026-03-15', '2026-03-17', '3 Days', 'open', '[{"day":1,"title":"Arrival & Desert Camp Setup","desc":"Meet at Riyadh meeting point, drive to desert camp, camel rides and dune bashing"},{"day":2,"title":"Full Desert Exploration","desc":"Sunrise photography, sandboarding, traditional lunch, stargazing at night"},{"day":3,"title":"Farewell & Return","desc":"Morning yoga in the desert, breakfast, return to Riyadh by noon"}]'],
            [2, 'Historic Jeddah Al-Balad Walking Tour', 'Walk through the ancient streets of Al-Balad, Jeddah\'s historic district and UNESCO World Heritage Site. Discover coral-stone architecture, vibrant souks, and centuries of trade history along the Red Sea coast.', 'Jeddah', 'Western', 'https://images.unsplash.com/photo-1578895101408-1a36b834405b?w=800', 180.00, 20, '2026-03-20', '2026-03-20', '1 Day', 'open', '[{"day":1,"title":"Al-Balad Heritage Walk","desc":"Morning tour of Nassif House, Al-Shafii Mosque, Souk Al-Alawi, traditional lunch, visit Jeddah Corniche at sunset"}]'],
            [3, 'Abha Mountain Retreat & Cloud Forest', 'Escape to the cool highlands of Abha, the jewel of Saudi Arabia\'s south. Explore lush green mountains, hanging villages, and the mystical cloud forests of the Asir region.', 'Abha', 'Southern', 'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800', 600.00, 12, '2026-04-01', '2026-04-04', '4 Days', 'open', '[{"day":1,"title":"Arrival in Abha","desc":"Airport pickup, check-in at mountain lodge, evening city tour"},{"day":2,"title":"Hanging Village & Habala","desc":"Visit the famous Habala hanging village via cable car, local crafts market"},{"day":3,"title":"Cloud Forest Trek","desc":"Guided hike through Asir National Park, traditional Asiri lunch"},{"day":4,"title":"Al-Soudah Peak & Departure","desc":"Visit Al-Soudah, highest point in Saudi Arabia, farewell lunch, departure"}]'],
            [4, 'AlUla Heritage & Hegra Discovery', 'Journey through time in AlUla, home to Saudi Arabia\'s first UNESCO World Heritage Site. Marvel at 2,000-year-old Nabataean tombs carved into sandstone mountains.', 'AlUla', 'Northern', 'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800', 850.00, 10, '2026-04-10', '2026-04-13', '4 Days', 'open', '[{"day":1,"title":"Welcome to AlUla","desc":"Arrive at AlUla, check in at desert resort, sunset at Elephant Rock"},{"day":2,"title":"Hegra Archaeological Site","desc":"Full day exploring Hegra tombs, Jabal Ithlib, ancient inscriptions"},{"day":3,"title":"Old Town & Oasis","desc":"Walk through AlUla Old Town, date palm oasis, Dadan kingdom ruins"},{"day":4,"title":"Stargazing & Farewell","desc":"Morning at leisure, afternoon desert drive, evening stargazing experience, departure"}]'],
            [3, 'NEOM Beach & Coral Reef Snorkeling', 'Discover the pristine waters of the Red Sea near the NEOM project area. Snorkel among vibrant coral reefs, relax on untouched beaches.', 'Tabuk', 'Northern', 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800', 500.00, 8, '2026-05-01', '2026-05-03', '3 Days', 'open', '[{"day":1,"title":"Coastal Arrival","desc":"Drive to NEOM coast area, beach setup, sunset swim"},{"day":2,"title":"Reef Snorkeling Day","desc":"Full day snorkeling at two reef sites, underwater photography, beach BBQ"},{"day":3,"title":"Beach Relaxation & Return","desc":"Morning kayaking, beach leisure, return journey"}]'],
            [2, 'Riyadh City Lights & Culture Tour', 'Experience the modern pulse of Saudi Arabia in its capital city. From the iconic Kingdom Tower to the historic Diriyah district.', 'Riyadh', 'Central', 'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800', 220.00, 25, '2026-03-25', '2026-03-26', '2 Days', 'open', '[{"day":1,"title":"Modern Riyadh","desc":"Kingdom Tower Sky Bridge, Boulevard City, National Museum, dinner at Tahlia Street"},{"day":2,"title":"Historic Diriyah","desc":"At-Turaif UNESCO site, Bujairi Terrace, traditional Saudi lunch, departure"}]']
        ];

        $stmt = $pdo->prepare("INSERT INTO trips (guide_id, title, description, location_city, location_region, cover_image, price, max_tourists, start_date, end_date, duration, status, itinerary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($trips as $t) {
            $stmt->execute($t);
        }
        $messages[] = "6 sample trips created.";

        // Bookings (trip_id, tourist_id, num_people, status)
        $bookings = [
            [1, 5, 2, 'accepted'],
            [1, 6, 1, 'pending'],
            [3, 5, 3, 'accepted'],
            [4, 7, 2, 'pending'],
            [2, 7, 1, 'accepted']
        ];
        $stmt = $pdo->prepare("INSERT INTO bookings (trip_id, tourist_id, num_people, status) VALUES (?, ?, ?, ?)");
        foreach ($bookings as $b) {
            $stmt->execute($b);
        }
        $messages[] = "5 sample bookings created.";

        // Reviews
        $reviews = [
            [1, 5, 5, 'Absolutely magical experience! The desert sunset was unlike anything I have ever seen. Ahmed was an incredible guide.'],
            [2, 7, 4, 'A wonderful walking tour through history. The coral architecture in Al-Balad is truly unique.'],
            [3, 5, 5, 'Abha is a hidden gem! The cloud forests and hanging villages were breathtaking. Fatimah was so knowledgeable.']
        ];
        $stmt = $pdo->prepare("INSERT INTO reviews (trip_id, tourist_id, rating, comment) VALUES (?, ?, ?, ?)");
        foreach ($reviews as $r) {
            $stmt->execute($r);
        }
        $messages[] = "3 sample reviews created.";

        // FAQs
        $faqs = [
            ['How do I book a trip?', 'Simply browse available trips, select one you like, choose your preferred date and number of people, and click Book Trip. You will receive a confirmation once the guide accepts your booking.', 1],
            ['Can I cancel a booking?', 'Yes, you can cancel a booking up to 48 hours before the trip start date for a full refund. Cancellations within 48 hours may be subject to a cancellation fee.', 2],
            ['How do I become a tour guide?', 'Register as a Guide on our platform, complete your profile, and start creating trips. Our admin team will review and approve your account within 24 hours.', 3],
            ['Is it safe to travel in Saudi Arabia?', 'Saudi Arabia is one of the safest countries in the world for tourists. Our guides are certified and experienced professionals who ensure your safety throughout every trip.', 4],
            ['What payment methods are accepted?', 'We accept major credit cards, Apple Pay, mada debit cards, and bank transfers. All payments are processed securely through our platform.', 5]
        ];
        $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, sort_order) VALUES (?, ?, ?)");
        foreach ($faqs as $f) {
            $stmt->execute($f);
        }
        $messages[] = "5 FAQs created.";

        // City Dynamic Pricing seed data
        $cityPricing = [
            ['Riyadh',  350.00, 450.00, 60.00, 95.00, 120.00, 150.00],
            ['Jeddah',  300.00, 400.00, 55.00, 85.00, 110.00, 130.00],
            ['Makkah',  320.00, 500.00, 55.00, 90.00, 115.00, 120.00],
            ['Madinah', 280.00, 420.00, 50.00, 80.00, 100.00, 110.00],
            ['Dammam',  250.00, 320.00, 45.00, 75.00, 95.00, 120.00],
            ['Abha',    220.00, 280.00, 40.00, 70.00, 85.00, 100.00],
            ['Tabuk',   200.00, 260.00, 40.00, 65.00, 80.00, 90.00],
            ['AlUla',   180.00, 240.00, 35.00, 60.00, 75.00, 80.00],
            ['Taif',    200.00, 270.00, 40.00, 65.00, 80.00, 90.00],
            ['Yanbu',   190.00, 250.00, 35.00, 60.00, 75.00, 85.00]
        ];
        $stmt = $pdo->prepare("INSERT INTO city_pricing (city_name, base_price_per_day, accommodation_per_night, breakfast_price, lunch_price, dinner_price, transportation_per_day) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($cityPricing as $cp) {
            $stmt->execute($cp);
        }
        $messages[] = "10 city pricing entries created.";

        // Guide Mohammed for packages
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, city, region, status) VALUES (?, ?, ?, ?, 'guide', ?, ?, 'active')");
        $stmt->execute(['Mohammed Al-Qahtani', 'mohammed@stms.sa', '+966507777777', $guideHash, 'Riyadh', 'Central']);
        $mohammedId = $pdo->lastInsertId();

        // Package 1: Saudi Grand Tour
        $stmt = $pdo->prepare("INSERT INTO packages (guide_id, title, description, cover_image, duration_days, is_extendable, base_price, max_tourists, includes_transport) VALUES (?, ?, ?, ?, ?, 0, ?, ?, 1)");
        $stmt->execute([$mohammedId, 'Saudi Grand Tour: Jeddah, Riyadh & AlUla',
            'Experience the best of Saudi Arabia in this comprehensive 10-day package. Start in the historic port city of Jeddah, explore the vibrant capital Riyadh, and discover the ancient wonders of AlUla. This fixed-duration package includes transportation between all cities and expert guidance throughout your journey.',
            'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800', 10, 4500.00, 12]);
        $pkg1Id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO package_cities (package_id, city_name, days_in_city, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->execute([$pkg1Id, 'Jeddah', 3, 1]);
        $stmt->execute([$pkg1Id, 'Riyadh', 4, 2]);
        $stmt->execute([$pkg1Id, 'AlUla', 3, 3]);

        $itin1 = [
            [1, 'Jeddah', 'Welcome to Jeddah', 'Airport pickup, check-in at hotel, evening walk along Jeddah Corniche'],
            [2, 'Jeddah', 'Historic Al-Balad Tour', 'Full day exploring UNESCO World Heritage Al-Balad district, Nassif House, traditional souks'],
            [3, 'Jeddah', 'Red Sea & Departure', 'Morning at Red Sea waterfront, King Fahd Fountain, afternoon flight to Riyadh'],
            [4, 'Riyadh', 'Arrival in the Capital', 'Arrive in Riyadh, check-in, evening visit to Kingdom Tower Sky Bridge'],
            [5, 'Riyadh', 'Modern Riyadh', 'National Museum, Boulevard City, Tahlia Street dining experience'],
            [6, 'Riyadh', 'Historic Diriyah', 'Full day at At-Turaif UNESCO site, Bujairi Terrace, traditional Saudi lunch'],
            [7, 'Riyadh', 'Edge of the World & Transfer', 'Morning trip to Edge of the World viewpoint, afternoon flight to AlUla'],
            [8, 'AlUla', 'Welcome to AlUla', 'Arrive in AlUla, desert resort check-in, sunset at Elephant Rock'],
            [9, 'AlUla', 'Hegra Archaeological Site', 'Full day exploring Hegra tombs, Jabal Ithlib, ancient Nabataean inscriptions'],
            [10, 'AlUla', 'Old Town & Farewell', 'AlUla Old Town walk, date palm oasis, farewell dinner under the stars, departure']
        ];
        $stmt = $pdo->prepare("INSERT INTO package_itinerary (package_id, day_number, city_name, title, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($itin1 as $it) {
            $stmt->execute([$pkg1Id, $it[0], $it[1], $it[2], $it[3]]);
        }

        $stmt = $pdo->prepare("INSERT INTO package_images (package_id, image_url, sort_order) VALUES (?, ?, ?)");
        $stmt->execute([$pkg1Id, 'https://images.unsplash.com/photo-1578895101408-1a36b834405b?w=800', 1]);
        $stmt->execute([$pkg1Id, 'https://images.unsplash.com/photo-1586724237569-f3d0c1dee8c6?w=800', 2]);
        $stmt->execute([$pkg1Id, 'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800', 3]);

        // Package 2: Jeddah & Abha Explorer (by Ahmed, guide_id=2)
        $stmt = $pdo->prepare("INSERT INTO packages (guide_id, title, description, cover_image, duration_days, is_extendable, base_price, max_tourists, includes_transport) VALUES (?, ?, ?, ?, ?, 0, ?, ?, 1)");
        $stmt->execute([2, 'Jeddah & Abha Explorer',
            'A beautiful 7-day journey from the coastal charm of Jeddah to the cool mountain heights of Abha. Experience the contrast of Red Sea beaches and cloud forests in one amazing trip.',
            'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800', 7, 3200.00, 15]);
        $pkg2Id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO package_cities (package_id, city_name, days_in_city, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->execute([$pkg2Id, 'Jeddah', 3, 1]);
        $stmt->execute([$pkg2Id, 'Abha', 4, 2]);

        $itin2 = [
            [1, 'Jeddah', 'Arrival & Corniche', 'Airport pickup, hotel check-in, evening at Jeddah Corniche'],
            [2, 'Jeddah', 'Al-Balad Heritage Walk', 'Explore the historic Al-Balad district, lunch at traditional restaurant'],
            [3, 'Jeddah', 'Red Sea Day & Travel', 'Morning Red Sea activities, afternoon flight to Abha'],
            [4, 'Abha', 'Welcome to Abha', 'Arrive in Abha, mountain lodge check-in, evening city tour'],
            [5, 'Abha', 'Habala Hanging Village', 'Cable car to the famous hanging village, local crafts market'],
            [6, 'Abha', 'Cloud Forest Trek', 'Guided hike through Asir National Park, traditional Asiri lunch'],
            [7, 'Abha', 'Al-Soudah & Farewell', 'Visit highest point in Saudi Arabia, farewell lunch, departure']
        ];
        $stmt = $pdo->prepare("INSERT INTO package_itinerary (package_id, day_number, city_name, title, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($itin2 as $it) {
            $stmt->execute([$pkg2Id, $it[0], $it[1], $it[2], $it[3]]);
        }

        $stmt = $pdo->prepare("INSERT INTO package_images (package_id, image_url, sort_order) VALUES (?, ?, ?)");
        $stmt->execute([$pkg2Id, 'https://images.unsplash.com/photo-1578895101408-1a36b834405b?w=800', 1]);
        $stmt->execute([$pkg2Id, 'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=800', 2]);

        $messages[] = "4th guide (Mohammed) + 2 sample packages with itineraries created.";

    }

    $success = true;

} catch (PDOException $e) {
    $errors[] = "Database Error: " . $e->getMessage();
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STMS - Database Installer</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--gray-50);">
    <div class="card" style="max-width:600px; width:90%; margin:20px;">
        <div class="card-header" style="background:<?php echo $success ? 'var(--primary)' : 'var(--danger)'; ?>; color:white;">
            <h3>
                <i class="fas fa-<?php echo $success ? 'check-circle' : 'times-circle'; ?>"></i>
                STMS Database <?php echo $success ? 'Installed Successfully' : 'Installation Failed'; ?>
            </h3>
        </div>
        <div class="card-body">
            <?php if (!empty($messages)): ?>
                <div style="margin-bottom:20px;">
                    <?php foreach ($messages as $msg): ?>
                        <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid var(--gray-100);">
                            <i class="fas fa-check" style="color:var(--success); font-size:0.8rem;"></i>
                            <span style="font-size:0.9rem; color:var(--text);"><?php echo $msg; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div style="background:rgba(231,76,60,0.1); border:1px solid rgba(231,76,60,0.3); border-radius:10px; padding:16px; margin-bottom:20px;">
                    <?php foreach ($errors as $err): ?>
                        <p style="color:var(--danger); font-size:0.9rem;">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $err; ?>
                        </p>
                    <?php endforeach; ?>
                    <p style="color:var(--text-light); font-size:0.85rem; margin-top:10px;">
                        <strong>Make sure:</strong><br>
                        1. XAMPP Control Panel is open<br>
                        2. Apache is running (green)<br>
                        3. MySQL is running (green)<br>
                        Then refresh this page.
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background:rgba(39,174,96,0.1); border:1px solid rgba(39,174,96,0.3); border-radius:10px; padding:16px; margin-bottom:20px;">
                    <h4 style="color:var(--success); margin-bottom:10px;">Login Credentials</h4>
                    <table style="width:100%; font-size:0.85rem;">
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:6px 0;"><strong>Admin:</strong></td>
                            <td>admin@stms.sa</td>
                            <td>admin123</td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:6px 0;"><strong>Guide:</strong></td>
                            <td>ahmed@stms.sa</td>
                            <td>guide123</td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:6px 0;"><strong>Guide:</strong></td>
                            <td>fatimah@stms.sa</td>
                            <td>guide123</td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:6px 0;"><strong>Guide:</strong></td>
                            <td>mohammed@stms.sa</td>
                            <td>guide123</td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:6px 0;"><strong>Tourist:</strong></td>
                            <td>sarah@email.com</td>
                            <td>tourist123</td>
                        </tr>
                        <tr>
                            <td style="padding:6px 0;"><strong>Tourist:</strong></td>
                            <td>mohammed@email.com</td>
                            <td>tourist123</td>
                        </tr>
                    </table>
                </div>
                <a href="index.html" class="btn btn-primary btn-block btn-lg" style="justify-content:center;">
                    <i class="fas fa-home"></i> Go to STMS Home
                </a>
            <?php else: ?>
                <button onclick="location.reload()" class="btn btn-primary btn-block btn-lg" style="justify-content:center;">
                    <i class="fas fa-redo"></i> Retry Installation
                </button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
