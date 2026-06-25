<?php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        listPackages();
        break;
    case 'get':
        getPackage();
        break;
    case 'create':
        createPackage();
        break;
    case 'update':
        updatePackage();
        break;
    case 'delete':
        deletePackage();
        break;
    case 'delete_package':
        deletePackage();
        break;
    case 'all_packages':
        allPackages();
        break;
    case 'get_package':
        getPackage();
        break;
    case 'guide_packages':
        guidePackages();
        break;
    case 'city_pricing':
        getCityPricing();
        break;
    case 'calculate_price':
        calculatePrice();
        break;
    default:
        respond(['success' => false, 'message' => msg('Invalid action', 'إجراء غير صحيح')], 400);
}

function listPackages() {
    $db = getDB();
    $sql = "SELECT p.*, u.full_name AS guide_name, u.city AS guide_city
            FROM packages p
            JOIN users u ON p.guide_id = u.id
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC";
    $stmt = $db->query($sql);
    $packages = $stmt->fetchAll();

    foreach ($packages as &$pkg) {
        // Get cities
        $stmt2 = $db->prepare("SELECT city_name, days_in_city, sort_order FROM package_cities WHERE package_id = ? ORDER BY sort_order ASC");
        $stmt2->execute([$pkg['id']]);
        $pkg['cities'] = $stmt2->fetchAll();

        // Get itinerary
        $stmt3 = $db->prepare("SELECT day_number, city_name, title, description FROM package_itinerary WHERE package_id = ? ORDER BY day_number ASC");
        $stmt3->execute([$pkg['id']]);
        $pkg['itinerary'] = $stmt3->fetchAll();

        // Get images
        $stmt4 = $db->prepare("SELECT image_url, sort_order FROM package_images WHERE package_id = ? ORDER BY sort_order ASC");
        $stmt4->execute([$pkg['id']]);
        $pkg['images'] = $stmt4->fetchAll();

        // City names string
        $pkg['cities_label'] = implode(', ', array_column($pkg['cities'], 'city_name'));
    }

    respond(['success' => true, 'packages' => $packages]);
}

function getPackage() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) respond(['success' => false, 'message' => msg('Package ID required', 'معرف الباقة مطلوب')], 400);

    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, u.full_name AS guide_name, u.email AS guide_email, u.phone AS guide_phone, u.city AS guide_city
        FROM packages p JOIN users u ON p.guide_id = u.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $pkg = $stmt->fetch();

    if (!$pkg) respond(['success' => false, 'message' => msg('Package not found', 'الباقة غير موجودة')], 404);

    // Get cities
    $stmt2 = $db->prepare("SELECT city_name, days_in_city, sort_order FROM package_cities WHERE package_id = ? ORDER BY sort_order ASC");
    $stmt2->execute([$id]);
    $pkg['cities'] = $stmt2->fetchAll();

    // Get itinerary
    $stmt3 = $db->prepare("SELECT day_number, city_name, title, description FROM package_itinerary WHERE package_id = ? ORDER BY day_number ASC");
    $stmt3->execute([$id]);
    $pkg['itinerary'] = $stmt3->fetchAll();

    // Get images
    $stmt4 = $db->prepare("SELECT image_url, sort_order FROM package_images WHERE package_id = ? ORDER BY sort_order ASC");
    $stmt4->execute([$id]);
    $pkg['images'] = $stmt4->fetchAll();

    $pkg['cities_label'] = implode(', ', array_column($pkg['cities'], 'city_name'));

    // Get city pricing for add-on calculations
    $cityNames = array_column($pkg['cities'], 'city_name');
    if (!empty($cityNames)) {
        $placeholders = implode(',', array_fill(0, count($cityNames), '?'));
        $stmt5 = $db->prepare("SELECT * FROM city_pricing WHERE city_name IN ($placeholders)");
        $stmt5->execute($cityNames);
        $pkg['city_prices'] = $stmt5->fetchAll();
    } else {
        $pkg['city_prices'] = [];
    }

    // Bookings count
    $stmt6 = $db->prepare("SELECT COALESCE(SUM(num_people),0) AS booked FROM bookings WHERE package_id = ? AND status IN ('accepted','pending')");
    $stmt6->execute([$id]);
    $pkg['booked_count'] = (int)$stmt6->fetch()['booked'];

    respond(['success' => true, 'package' => $pkg]);
}

function createPackage() {
    requireRole('guide');
    $data = json_decode(file_get_contents('php://input'), true);

    $required = ['title', 'description', 'duration_days', 'base_price', 'cities'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            respond(['success' => false, 'message' => msg("Field '{$field}' is required", "الحقل '{$field}' مطلوب")], 400);
        }
    }

    $duration = (int)$data['duration_days'];
    if ($duration < 1 || $duration > 10) {
        respond(['success' => false, 'message' => msg('Duration must be between 1 and 10 days', 'المدة يجب أن تكون بين 1 و 10 أيام')], 400);
    }

    // Validate total city days match duration
    $totalDays = 0;
    foreach ($data['cities'] as $city) {
        $totalDays += (int)($city['days_in_city'] ?? 1);
    }
    if ($totalDays !== $duration) {
        respond(['success' => false, 'message' => msg("Total days in cities ({$totalDays}) must equal package duration ({$duration})", "مجموع أيام المدن ({$totalDays}) يجب أن يساوي مدة الباقة ({$duration})")], 400);
    }

    $db = getDB();

    // Ensure start_date column exists
    $cols = $db->query("SHOW COLUMNS FROM packages LIKE 'start_date'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE packages ADD COLUMN start_date DATE NULL DEFAULT NULL");
    }

    $db->beginTransaction();

    try {
        $stmt = $db->prepare("INSERT INTO packages (guide_id, title, description, cover_image, duration_days, is_extendable, base_price, max_tourists, includes_transport, start_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            sanitize($data['title']),
            sanitize($data['description']),
            sanitize($data['cover_image'] ?? ''),
            $duration,
            0,
            (float)$data['base_price'],
            (int)($data['max_tourists'] ?? 10),
            !empty($data['start_date']) ? $data['start_date'] : null
        ]);
        $pkgId = $db->lastInsertId();

        // Insert cities
        $stmtCity = $db->prepare("INSERT INTO package_cities (package_id, city_name, days_in_city, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($data['cities'] as $i => $city) {
            $stmtCity->execute([$pkgId, sanitize($city['city_name']), (int)$city['days_in_city'], $i + 1]);
        }

        // Insert itinerary
        if (!empty($data['itinerary'])) {
            $stmtItin = $db->prepare("INSERT INTO package_itinerary (package_id, day_number, city_name, title, description) VALUES (?, ?, ?, ?, ?)");
            foreach ($data['itinerary'] as $item) {
                if (!empty($item['title'])) {
                    $stmtItin->execute([
                        $pkgId,
                        (int)$item['day_number'],
                        sanitize($item['city_name'] ?? ''),
                        sanitize($item['title']),
                        sanitize($item['description'] ?? '')
                    ]);
                }
            }
        }

        // Insert images
        if (!empty($data['images'])) {
            $stmtImg = $db->prepare("INSERT INTO package_images (package_id, image_url, sort_order) VALUES (?, ?, ?)");
            foreach ($data['images'] as $i => $img) {
                if (!empty($img)) {
                    $stmtImg->execute([$pkgId, sanitize($img), $i + 1]);
                }
            }
        }

        $db->commit();
        respond(['success' => true, 'message' => msg('Package created successfully', 'تم إنشاء الباقة بنجاح'), 'id' => $pkgId]);
    } catch (Exception $e) {
        $db->rollBack();
        respond(['success' => false, 'message' => msg('Failed to create package: ' . $e->getMessage(), 'فشل إنشاء الباقة: ' . $e->getMessage())], 500);
    }
}

function updatePackage() {
    requireRole('guide');
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    if (!$id) respond(['success' => false, 'message' => msg('Package ID required', 'معرف الباقة مطلوب')], 400);

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM packages WHERE id = ? AND guide_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) respond(['success' => false, 'message' => msg('Package not found or unauthorized', 'الباقة غير موجودة أو غير مصرح')], 403);

    $duration = (int)($data['duration_days'] ?? 10);
    if ($duration < 1 || $duration > 10) {
        respond(['success' => false, 'message' => msg('Duration must be between 1 and 10 days', 'المدة يجب أن تكون بين 1 و 10 أيام')], 400);
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("UPDATE packages SET title=?, description=?, cover_image=?, duration_days=?, base_price=?, max_tourists=?, status=?, start_date=? WHERE id=?");
        $stmt->execute([
            sanitize($data['title']),
            sanitize($data['description']),
            sanitize($data['cover_image'] ?? ''),
            $duration,
            (float)$data['base_price'],
            (int)($data['max_tourists'] ?? 10),
            sanitize($data['status'] ?? 'active'),
            !empty($data['start_date']) ? $data['start_date'] : null,
            $id
        ]);

        // Replace cities
        $db->prepare("DELETE FROM package_cities WHERE package_id = ?")->execute([$id]);
        if (!empty($data['cities'])) {
            $stmtCity = $db->prepare("INSERT INTO package_cities (package_id, city_name, days_in_city, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($data['cities'] as $i => $city) {
                $stmtCity->execute([$id, sanitize($city['city_name']), (int)$city['days_in_city'], $i + 1]);
            }
        }

        // Replace itinerary
        $db->prepare("DELETE FROM package_itinerary WHERE package_id = ?")->execute([$id]);
        if (!empty($data['itinerary'])) {
            $stmtItin = $db->prepare("INSERT INTO package_itinerary (package_id, day_number, city_name, title, description) VALUES (?, ?, ?, ?, ?)");
            foreach ($data['itinerary'] as $item) {
                if (!empty($item['title'])) {
                    $stmtItin->execute([$id, (int)$item['day_number'], sanitize($item['city_name'] ?? ''), sanitize($item['title']), sanitize($item['description'] ?? '')]);
                }
            }
        }

        // Replace images
        $db->prepare("DELETE FROM package_images WHERE package_id = ?")->execute([$id]);
        if (!empty($data['images'])) {
            $stmtImg = $db->prepare("INSERT INTO package_images (package_id, image_url, sort_order) VALUES (?, ?, ?)");
            foreach ($data['images'] as $i => $img) {
                if (!empty($img)) {
                    $stmtImg->execute([$id, sanitize($img), $i + 1]);
                }
            }
        }

        $db->commit();
        respond(['success' => true, 'message' => msg('Package updated successfully', 'تم تحديث الباقة بنجاح')]);
    } catch (Exception $e) {
        $db->rollBack();
        respond(['success' => false, 'message' => msg('Failed to update package', 'فشل تحديث الباقة')], 500);
    }
}

function deletePackage() {
    requireRole(['guide', 'admin']);
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    if (!$id) respond(['success' => false, 'message' => msg('Package ID required', 'معرف الباقة مطلوب')], 400);

    $db = getDB();
    if ($_SESSION['role'] === 'guide') {
        $stmt = $db->prepare("DELETE FROM packages WHERE id = ? AND guide_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
    } else {
        $stmt = $db->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$id]);
    }

    respond(['success' => true, 'message' => msg('Package deleted successfully', 'تم حذف الباقة بنجاح')]);
}

function allPackages() {
    requireRole('admin');
    $db = getDB();
    $stmt = $db->query("SELECT p.*, u.full_name AS guide_name
        FROM packages p
        JOIN users u ON p.guide_id = u.id
        ORDER BY p.created_at DESC");
    $packages = $stmt->fetchAll();

    foreach ($packages as &$pkg) {
        $stmt2 = $db->prepare("SELECT city_name, days_in_city, sort_order FROM package_cities WHERE package_id = ? ORDER BY sort_order ASC");
        $stmt2->execute([$pkg['id']]);
        $pkg['cities'] = $stmt2->fetchAll();
    }

    respond(['success' => true, 'packages' => $packages]);
}

function guidePackages() {
    requireRole('guide');
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*,
        (SELECT COALESCE(SUM(b.num_people),0) FROM bookings b WHERE b.package_id = p.id AND b.status IN ('accepted','pending')) AS booked_count
        FROM packages p WHERE p.guide_id = ? ORDER BY p.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $packages = $stmt->fetchAll();

    foreach ($packages as &$pkg) {
        $stmt2 = $db->prepare("SELECT city_name, days_in_city, sort_order FROM package_cities WHERE package_id = ? ORDER BY sort_order ASC");
        $stmt2->execute([$pkg['id']]);
        $pkg['cities'] = $stmt2->fetchAll();
        $pkg['cities_label'] = implode(', ', array_column($pkg['cities'], 'city_name'));

        $stmt3 = $db->prepare("SELECT day_number, city_name, title, description FROM package_itinerary WHERE package_id = ? ORDER BY day_number ASC");
        $stmt3->execute([$pkg['id']]);
        $pkg['itinerary'] = $stmt3->fetchAll();

        $stmt4 = $db->prepare("SELECT image_url, sort_order FROM package_images WHERE package_id = ? ORDER BY sort_order ASC");
        $stmt4->execute([$pkg['id']]);
        $pkg['images'] = $stmt4->fetchAll();
    }

    respond(['success' => true, 'packages' => $packages]);
}

function getCityPricing() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM city_pricing ORDER BY city_name ASC");
    respond(['success' => true, 'pricing' => $stmt->fetchAll()]);
}

function calculatePrice() {
    $data = json_decode(file_get_contents('php://input'), true);
    $cities = $data['cities'] ?? [];
    $includeAccommodation = (bool)($data['include_accommodation'] ?? false);
    $includeBreakfast = (bool)($data['include_breakfast'] ?? false);
    $includeLunch = (bool)($data['include_lunch'] ?? false);
    $includeDinner = (bool)($data['include_dinner'] ?? false);
    $numPeople = max(1, (int)($data['num_people'] ?? 1));
    $extensionDays = max(0, (int)($data['extension_days'] ?? 0));
    $bookingType = $data['booking_type'] ?? 'self_planned';
    $packageId = (int)($data['package_id'] ?? 0);

    $db = getDB();

    $totalPrice = 0;
    $breakdown = [];

    if ($bookingType === 'package' && $packageId) {
        // Package-based pricing
        $stmt = $db->prepare("SELECT base_price, duration_days FROM packages WHERE id = ?");
        $stmt->execute([$packageId]);
        $pkg = $stmt->fetch();
        if (!$pkg) respond(['success' => false, 'message' => msg('Package not found', 'الباقة غير موجودة')], 404);

        $totalPrice = (float)$pkg['base_price'];
        $breakdown[] = ['label' => msg('Package base price', 'السعر الأساسي للباقة'), 'amount' => $totalPrice];

        // Get package cities for add-on pricing
        $stmt2 = $db->prepare("SELECT pc.city_name, pc.days_in_city, cp.accommodation_per_night, cp.breakfast_price, cp.lunch_price, cp.dinner_price
            FROM package_cities pc LEFT JOIN city_pricing cp ON pc.city_name = cp.city_name WHERE pc.package_id = ? ORDER BY pc.sort_order");
        $stmt2->execute([$packageId]);
        $pkgCities = $stmt2->fetchAll();

        foreach ($pkgCities as $c) {
            $days = (int)$c['days_in_city'];
            $nights = max(0, $days - 1);
            if ($includeAccommodation && $c['accommodation_per_night']) {
                $acc = $nights * (float)$c['accommodation_per_night'];
                $totalPrice += $acc;
                $breakdown[] = ['label' => msg("Accommodation in {$c['city_name']} ({$nights} nights)", "الإقامة في {$c['city_name']} ({$nights} ليالي)"), 'amount' => $acc];
            }
            if ($includeBreakfast && $c['breakfast_price']) {
                $meal = $days * (float)$c['breakfast_price'];
                $totalPrice += $meal;
                $breakdown[] = ['label' => msg("Breakfast in {$c['city_name']} ({$days} days)", "إفطار في {$c['city_name']} ({$days} أيام)"), 'amount' => $meal];
            }
            if ($includeLunch && $c['lunch_price']) {
                $meal = $days * (float)$c['lunch_price'];
                $totalPrice += $meal;
                $breakdown[] = ['label' => msg("Lunch in {$c['city_name']} ({$days} days)", "غداء في {$c['city_name']} ({$days} أيام)"), 'amount' => $meal];
            }
            if ($includeDinner && $c['dinner_price']) {
                $meal = $days * (float)$c['dinner_price'];
                $totalPrice += $meal;
                $breakdown[] = ['label' => msg("Dinner in {$c['city_name']} ({$days} days)", "عشاء في {$c['city_name']} ({$days} أيام)"), 'amount' => $meal];
            }
        }
    } else {
        // Self-planned pricing
        if (empty($cities)) {
            respond(['success' => true, 'total_price' => 0, 'breakdown' => [], 'per_person' => 0]);
        }

        foreach ($cities as $cityData) {
            $cityName = $cityData['city_name'] ?? '';
            $days = (int)($cityData['days_in_city'] ?? 1);
            $nights = max(0, $days - 1);

            $stmt = $db->prepare("SELECT * FROM city_pricing WHERE city_name = ?");
            $stmt->execute([$cityName]);
            $pricing = $stmt->fetch();

            if (!$pricing) continue;

            // Base price per day
            $base = $days * (float)$pricing['base_price_per_day'];
            $totalPrice += $base;
            $breakdown[] = ['label' => msg("Base price in {$cityName} ({$days} days)", "السعر الأساسي في {$cityName} ({$days} أيام)"), 'amount' => $base];

            // Transportation included
            $transport = $days * (float)$pricing['transportation_per_day'];
            $totalPrice += $transport;
            $breakdown[] = ['label' => msg("Transportation in {$cityName} ({$days} days)", "المواصلات في {$cityName} ({$days} أيام)"), 'amount' => $transport];

            // Optional: Accommodation
            if ($includeAccommodation && $nights > 0) {
                $acc = $nights * (float)$pricing['accommodation_per_night'];
                $totalPrice += $acc;
                $breakdown[] = ['label' => msg("Accommodation in {$cityName} ({$nights} nights)", "الإقامة في {$cityName} ({$nights} ليالي)"), 'amount' => $acc];
            }

            // Optional: Meals
            if ($includeBreakfast) {
                $meal = $days * (float)$pricing['breakfast_price'];
                $totalPrice += $meal;
                $breakdown[] = ['label' => msg("Breakfast in {$cityName} ({$days} days)", "إفطار في {$cityName} ({$days} أيام)"), 'amount' => $meal];
            }
            if ($includeLunch) {
                $meal = $days * (float)$pricing['lunch_price'];
                $totalPrice += $meal;
                $breakdown[] = ['label' => msg("Lunch in {$cityName} ({$days} days)", "غداء في {$cityName} ({$days} أيام)"), 'amount' => $meal];
            }
            if ($includeDinner) {
                $meal = $days * (float)$pricing['dinner_price'];
                $totalPrice += $meal;
                $breakdown[] = ['label' => msg("Dinner in {$cityName} ({$days} days)", "عشاء في {$cityName} ({$days} أيام)"), 'amount' => $meal];
            }
        }
    }

    $perPerson = $totalPrice;
    $totalPrice = $totalPrice * $numPeople;

    respond([
        'success' => true,
        'per_person' => round($perPerson, 2),
        'total_price' => round($totalPrice, 2),
        'num_people' => $numPeople,
        'breakdown' => $breakdown
    ]);
}
?>
