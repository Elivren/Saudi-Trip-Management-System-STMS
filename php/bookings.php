<?php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        createBooking();
        break;
    case 'create_package_booking':
        createPackageBooking();
        break;
    case 'create_self_planned':
        createSelfPlannedBooking();
        break;
    case 'my_bookings':
        myBookings();
        break;
    case 'trip_bookings':
        tripBookings();
        break;
    case 'update_status':
        updateBookingStatus();
        break;
    case 'cancel':
        cancelBooking();
        break;
    case 'package_bookings':
        packageBookings();
        break;
    case 'guide_self_planned':
        guideSelfPlannedBookings();
        break;
    case 'assign_guide':
        assignGuide();
        break;
    case 'delete_booking':
        deleteBooking();
        break;
    case 'get_booking':
        getBooking();
        break;
    default:
        respond(['success' => false, 'message' => 'Invalid action'], 400);
}

function createBooking() {
    requireRole('tourist');
    $data = json_decode(file_get_contents('php://input'), true);

    $trip_id = (int)($data['trip_id'] ?? 0);
    $num_people = (int)($data['num_people'] ?? 1);
    $notes = sanitize($data['notes'] ?? '');

    if (!$trip_id) respond(['success' => false, 'message' => msg(msg('Trip ID required', 'معرف الرحلة مطلوب'), 'معرف الرحلة مطلوب')], 400);
    if ($num_people < 1) respond(['success' => false, 'message' => msg('At least 1 person required', 'يجب تحديد شخص واحد على الأقل')], 400);

    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM trips WHERE id = ? AND status = 'open'");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch();
    if (!$trip) respond(['success' => false, 'message' => msg('Trip not available', 'الرحلة غير متاحة')], 404);

    $stmt = $db->prepare("SELECT COALESCE(SUM(num_people),0) AS booked FROM bookings WHERE trip_id = ? AND status IN ('accepted','pending')");
    $stmt->execute([$trip_id]);
    $booked = $stmt->fetch()['booked'];

    if (($booked + $num_people) > $trip['max_tourists']) {
        respond(['success' => false, 'message' => msg('Not enough spots available. Only ' . ($trip['max_tourists'] - $booked) . ' spots left.', 'لا توجد أماكن كافية. المتبقي ' . ($trip['max_tourists'] - $booked) . ' فقط.')], 400);
    }

    $stmt = $db->prepare("SELECT id FROM bookings WHERE trip_id = ? AND tourist_id = ? AND status IN ('accepted','pending')");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        respond(['success' => false, 'message' => msg('You already have a booking for this trip', 'لديك حجز مسبق لهذه الرحلة')], 409);
    }

    $stmt = $db->prepare("INSERT INTO bookings (trip_id, tourist_id, booking_type, num_people, notes, total_price, status) VALUES (?, ?, 'trip', ?, ?, ?, 'accepted')");
    $totalPrice = (float)$trip['price'] * $num_people;
    $stmt->execute([$trip_id, $_SESSION['user_id'], $num_people, $notes, $totalPrice]);

    respond(['success' => true, 'message' => msg('Booking confirmed successfully! Your trip has been booked. Note: You can cancel up to 24 hours before the trip for a full refund. After that, no refund will be issued.', 'تم تأكيد حجزك بنجاح! تم حجز رحلتك. ملاحظة: يمكنك الإلغاء قبل 24 ساعة من الرحلة لاسترداد كامل المبلغ. بعد ذلك لا يمكن استرداد المبلغ.')]);
}

function createPackageBooking() {
    requireRole('tourist');
    $data = json_decode(file_get_contents('php://input'), true);

    $package_id = (int)($data['package_id'] ?? 0);
    $num_people = (int)($data['num_people'] ?? 1);
    $notes = sanitize($data['notes'] ?? '');
    $includeAccommodation = (int)($data['include_accommodation'] ?? 0);
    $includeBreakfast = (int)($data['include_breakfast'] ?? 0);
    $includeLunch = (int)($data['include_lunch'] ?? 0);
    $includeDinner = (int)($data['include_dinner'] ?? 0);
    $startDate = $data['start_date'] ?? null;
    $totalPrice = (float)($data['total_price'] ?? 0);

    if (!$package_id) respond(['success' => false, 'message' => msg('Package ID required', 'معرف الباقة مطلوب')], 400);
    if ($num_people < 1) respond(['success' => false, 'message' => msg('At least 1 person required', 'يجب تحديد شخص واحد على الأقل')], 400);

    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM packages WHERE id = ? AND status = 'active'");
    $stmt->execute([$package_id]);
    $pkg = $stmt->fetch();
    if (!$pkg) respond(['success' => false, 'message' => msg('Package not available', 'الباقة غير متاحة')], 404);

    // Check capacity
    $stmt = $db->prepare("SELECT COALESCE(SUM(num_people),0) AS booked FROM bookings WHERE package_id = ? AND status IN ('accepted','pending')");
    $stmt->execute([$package_id]);
    $booked = (int)$stmt->fetch()['booked'];

    if (($booked + $num_people) > $pkg['max_tourists']) {
        respond(['success' => false, 'message' => msg('Not enough spots available. Only ' . ($pkg['max_tourists'] - $booked) . ' spots left.', 'لا توجد أماكن كافية. المتبقي ' . ($pkg['max_tourists'] - $booked) . ' فقط.')], 400);
    }

    // Prevent duplicate booking
    $stmt = $db->prepare("SELECT id FROM bookings WHERE package_id = ? AND tourist_id = ? AND status IN ('accepted','pending')");
    $stmt->execute([$package_id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        respond(['success' => false, 'message' => msg('You already have a booking for this package', 'لديك حجز مسبق لهذه الباقة')], 409);
    }

    $endDate = null;
    if ($startDate) {
        $endDate = date('Y-m-d', strtotime($startDate . ' + ' . ($pkg['duration_days'] - 1) . ' days'));
    }

    $stmt = $db->prepare("INSERT INTO bookings (trip_id, tourist_id, booking_type, package_id, num_people, notes, include_accommodation, include_breakfast, include_lunch, include_dinner, total_price, duration_days, extension_days, start_date, end_date, status)
        VALUES (0, ?, 'package', ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, 'accepted')");
    $stmt->execute([
        $_SESSION['user_id'], $package_id, $num_people, $notes,
        $includeAccommodation, $includeBreakfast, $includeLunch, $includeDinner,
        $totalPrice, $pkg['duration_days'], $startDate, $endDate
    ]);
    $bookingId = $db->lastInsertId();

    respond(['success' => true, 'message' => msg('Package booking confirmed successfully! Note: You can cancel up to 24 hours before the trip for a full refund. After that, no refund will be issued.', 'تم تأكيد حجز الباقة بنجاح! ملاحظة: يمكنك الإلغاء قبل 24 ساعة من الرحلة لاسترداد كامل المبلغ. بعد ذلك لا يمكن استرداد المبلغ.'), 'booking_id' => $bookingId]);
}

function createSelfPlannedBooking() {
    requireRole('tourist');
    $data = json_decode(file_get_contents('php://input'), true);

    $cities = $data['cities'] ?? [];
    $num_people = (int)($data['num_people'] ?? 1);
    $notes = sanitize($data['notes'] ?? '');
    $includeAccommodation = (int)($data['include_accommodation'] ?? 0);
    $includeBreakfast = (int)($data['include_breakfast'] ?? 0);
    $includeLunch = (int)($data['include_lunch'] ?? 0);
    $includeDinner = (int)($data['include_dinner'] ?? 0);
    $extensionDays = (int)($data['extension_days'] ?? 0);
    $startDate = $data['start_date'] ?? null;
    $totalPrice = (float)($data['total_price'] ?? 0);

    if (empty($cities)) respond(['success' => false, 'message' => msg('At least one city is required', 'يجب اختيار مدينة واحدة على الأقل')], 400);
    if ($num_people < 1) respond(['success' => false, 'message' => msg('At least 1 person required', 'يجب تحديد شخص واحد على الأقل')], 400);

    // Calculate total days
    $totalDays = 0;
    foreach ($cities as $c) {
        $totalDays += (int)($c['days_in_city'] ?? 1);
    }
    $totalDays += $extensionDays;

    if ($totalDays > 10 && $extensionDays === 0) {
        respond(['success' => false, 'message' => msg('Maximum trip duration is 10 days. Use the extension option for longer trips.', 'الحد الأقصى لمدة الرحلة 10 أيام. استخدم خيار التمديد للرحلات الأطول.')], 400);
    }

    $endDate = null;
    if ($startDate) {
        $endDate = date('Y-m-d', strtotime($startDate . ' + ' . ($totalDays - 1) . ' days'));
    }

    $db = getDB();
    $db->beginTransaction();

    try {
        $stmt = $db->prepare("INSERT INTO bookings (trip_id, tourist_id, booking_type, num_people, notes, include_accommodation, include_breakfast, include_lunch, include_dinner, total_price, duration_days, extension_days, start_date, end_date, status)
            VALUES (0, ?, 'self_planned', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'accepted')");
        $stmt->execute([
            $_SESSION['user_id'], $num_people, $notes,
            $includeAccommodation, $includeBreakfast, $includeLunch, $includeDinner,
            $totalPrice, $totalDays, $extensionDays, $startDate, $endDate
        ]);
        $bookingId = $db->lastInsertId();

        // Insert booking cities
        $stmtCity = $db->prepare("INSERT INTO booking_cities (booking_id, city_name, days_in_city, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($cities as $i => $city) {
            $stmtCity->execute([$bookingId, sanitize($city['city_name']), (int)$city['days_in_city'], $i + 1]);
        }

        $db->commit();
        respond(['success' => true, 'message' => msg('Self-planned booking confirmed! Your itinerary has been saved. Note: You can cancel up to 24 hours before the trip for a full refund. After that, no refund will be issued.', 'تم تأكيد حجزك المخصص! تم حفظ مساركم. ملاحظة: يمكنك الإلغاء قبل 24 ساعة من الرحلة لاسترداد كامل المبلغ. بعد ذلك لا يمكن استرداد المبلغ.'), 'booking_id' => $bookingId]);
    } catch (Exception $e) {
        $db->rollBack();
        respond(['success' => false, 'message' => 'Failed to create booking: ' . $e->getMessage()], 500);
    }
}

function myBookings() {
    requireRole('tourist');
    $db = getDB();

    // Get all bookings (trip, package, self-planned)
    $stmt = $db->prepare("SELECT b.*,
        CASE
            WHEN b.booking_type = 'trip' THEN t.title
            WHEN b.booking_type = 'package' THEN p.title
            ELSE 'Self-Planned Trip'
        END AS trip_title,
        CASE
            WHEN b.booking_type = 'trip' THEN t.location_city
            WHEN b.booking_type = 'package' THEN (SELECT GROUP_CONCAT(pc.city_name ORDER BY pc.sort_order SEPARATOR ', ') FROM package_cities pc WHERE pc.package_id = b.package_id)
            ELSE (SELECT GROUP_CONCAT(bc.city_name ORDER BY bc.sort_order SEPARATOR ', ') FROM booking_cities bc WHERE bc.booking_id = b.id)
        END AS location_city,
        CASE
            WHEN b.booking_type = 'trip' THEN t.start_date
            ELSE b.start_date
        END AS display_start_date,
        CASE
            WHEN b.booking_type = 'trip' THEN t.end_date
            ELSE b.end_date
        END AS display_end_date,
        CASE
            WHEN b.booking_type = 'trip' THEN t.cover_image
            WHEN b.booking_type = 'package' THEN p.cover_image
            ELSE NULL
        END AS cover_image,
        CASE
            WHEN b.booking_type = 'trip' THEN t.price
            ELSE b.total_price
        END AS price,
        CASE
            WHEN b.booking_type = 'trip' THEN ug.full_name
            WHEN b.booking_type = 'package' THEN up.full_name
            ELSE NULL
        END AS guide_name
        FROM bookings b
        LEFT JOIN trips t ON b.trip_id = t.id AND b.booking_type = 'trip'
        LEFT JOIN packages p ON b.package_id = p.id AND b.booking_type = 'package'
        LEFT JOIN users ug ON t.guide_id = ug.id
        LEFT JOIN users up ON p.guide_id = up.id
        WHERE b.tourist_id = ?
        ORDER BY b.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    respond(['success' => true, 'bookings' => $stmt->fetchAll()]);
}

function tripBookings() {
    requireRole(['guide', 'admin']);
    $trip_id = (int)($_GET['trip_id'] ?? 0);
    if (!$trip_id) respond(['success' => false, 'message' => msg(msg('Trip ID required', 'معرف الرحلة مطلوب'), 'معرف الرحلة مطلوب')], 400);

    $db = getDB();
    // Admin can view all, guide can only view their own trips
    if ($_SESSION['role'] === 'guide') {
        $stmt = $db->prepare("SELECT id FROM trips WHERE id = ? AND guide_id = ?");
        $stmt->execute([$trip_id, $_SESSION['user_id']]);
        if (!$stmt->fetch()) respond(['success' => false, 'message' => msg('Unauthorized', 'غير مصرح')], 403);
    }

    $stmt = $db->prepare("SELECT b.*, u.full_name AS tourist_name, u.email AS tourist_email, u.phone AS tourist_phone,
        t.location_city, t.location_region
        FROM bookings b 
        JOIN users u ON b.tourist_id = u.id
        JOIN trips t ON b.trip_id = t.id
        WHERE b.trip_id = ? AND b.booking_type = 'trip' ORDER BY b.created_at DESC");
    $stmt->execute([$trip_id]);
    respond(['success' => true, 'bookings' => $stmt->fetchAll()]);
}

function updateBookingStatus() {
    requireLogin();
    $data = json_decode(file_get_contents('php://input'), true);
    $booking_id = (int)($data['booking_id'] ?? 0);
    $status = sanitize($data['status'] ?? '');

    if (!$booking_id || !in_array($status, ['accepted', 'rejected'])) {
        respond(['success' => false, 'message' => msg('Invalid parameters', 'معطيات غير صحيحة')], 400);
    }

    $db = getDB();

    $stmt = $db->prepare("SELECT b.booking_type, b.trip_id, b.package_id FROM bookings b WHERE b.id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    if (!$booking) respond(['success' => false, 'message' => msg('Booking not found', 'الحجز غير موجود')], 404);

    // Admin can update any booking, guide can only update their own
    if ($_SESSION['role'] === 'admin') {
        $authorized = true;
    } else {
        $authorized = false;
        if ($booking['booking_type'] === 'trip' && $booking['trip_id']) {
            $stmt = $db->prepare("SELECT id FROM trips WHERE id = ? AND guide_id = ?");
            $stmt->execute([$booking['trip_id'], $_SESSION['user_id']]);
            $authorized = (bool)$stmt->fetch();
        } elseif ($booking['booking_type'] === 'package' && $booking['package_id']) {
            $stmt = $db->prepare("SELECT id FROM packages WHERE id = ? AND guide_id = ?");
            $stmt->execute([$booking['package_id'], $_SESSION['user_id']]);
            $authorized = (bool)$stmt->fetch();
        }
    }

    if (!$authorized) respond(['success' => false, 'message' => msg('Unauthorized', 'غير مصرح')], 403);

    $stmt = $db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $booking_id]);

    respond(['success' => true, 'message' => msg("Booking {$status} successfully", 'تم ' . ($status === 'accepted' ? 'قبول' : 'رفض') . ' الحجز بنجاح')]);
}

function cancelBooking() {
    requireRole('tourist');
    $data = json_decode(file_get_contents('php://input'), true);
    $booking_id = (int)($data['booking_id'] ?? 0);

    $db = getDB();
    $stmt = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND tourist_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);

    // Check if within 24 hours of trip start
    $stmt2 = $db->prepare("SELECT b.start_date, t.start_date AS trip_start FROM bookings b LEFT JOIN trips t ON b.trip_id = t.id WHERE b.id = ?");
    $stmt2->execute([$booking_id]);
    $bk = $stmt2->fetch();
    $startDate = $bk['start_date'] ?: $bk['trip_start'];
    $refundMsg = '';
    if ($startDate) {
        $hoursUntil = (strtotime($startDate) - time()) / 3600;
        if ($hoursUntil < 24) {
            $refundMsg = msg(' No refund will be issued as cancellation is within 24 hours of the trip.', ' لن يتم استرداد المبلغ لأن الإلغاء تم خلال 24 ساعة من موعد الرحلة.');
        } else {
            $refundMsg = msg(' Full refund will be processed.', ' سيتم استرداد المبلغ كاملاً.');
        }
    }
    respond(['success' => true, 'message' => msg('Booking cancelled.', 'تم إلغاء الحجز.') . $refundMsg]);
}

function packageBookings() {
    requireRole('guide');
    $db = getDB();

    // Get all bookings for packages owned by this guide
    $stmt = $db->prepare("SELECT b.*, u.full_name AS tourist_name, u.email AS tourist_email, u.phone AS tourist_phone, p.title AS package_title
        FROM bookings b
        JOIN users u ON b.tourist_id = u.id
        JOIN packages p ON b.package_id = p.id
        WHERE b.booking_type = 'package' AND p.guide_id = ?
        ORDER BY b.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll();

    // Attach package cities for each booking
    $stmtCities = $db->prepare("SELECT city_name, days_in_city, sort_order FROM package_cities WHERE package_id = ? ORDER BY sort_order ASC");
    foreach ($bookings as &$b) {
        $stmtCities->execute([$b['package_id']]);
        $b['cities'] = $stmtCities->fetchAll();
    }

    respond(['success' => true, 'bookings' => $bookings]);
}

function guideSelfPlannedBookings() {
    requireRole('guide');
    $db = getDB();

    // Ensure guide_id column exists (in case DB hasn't been updated yet)
    $cols = $db->query("SHOW COLUMNS FROM bookings LIKE 'guide_id'")->fetchAll();
    if (empty($cols)) {
        respond(['success' => true, 'bookings' => []]);
        return;
    }

    // Get all self-planned bookings assigned to this guide
    $stmt = $db->prepare("
        SELECT
            b.*,
            u.full_name  AS tourist_name,
            u.email      AS tourist_email,
            u.phone      AS tourist_phone,
            (
                SELECT GROUP_CONCAT(bc.city_name ORDER BY bc.sort_order SEPARATOR ', ')
                FROM booking_cities bc
                WHERE bc.booking_id = b.id
            ) AS cities_label
        FROM bookings b
        JOIN users u ON b.tourist_id = u.id
        WHERE b.booking_type = 'self_planned'
          AND b.guide_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll();

    respond(['success' => true, 'bookings' => $bookings]);
}

function assignGuide() {
    requireRole('admin');
    $data = json_decode(file_get_contents('php://input'), true);
    $booking_id = (int)($data['booking_id'] ?? 0);
    $guide_id   = (int)($data['guide_id']   ?? 0);

    if (!$booking_id || !$guide_id) {
        respond(['success' => false, 'message' => msg('Booking ID and Guide ID are required', 'معرف الحجز والمرشد مطلوبان')], 400);
    }

    $db = getDB();

    // Verify booking exists
    $stmt = $db->prepare("SELECT id, booking_type, trip_id FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    if (!$booking) {
        respond(['success' => false, 'message' => msg('Booking not found', 'الحجز غير موجود')], 404);
    }

    // Verify guide exists and is active
    $stmt = $db->prepare("SELECT id, full_name FROM users WHERE id = ? AND role = 'guide' AND status = 'active'");
    $stmt->execute([$guide_id]);
    $guide = $stmt->fetch();
    if (!$guide) {
        respond(['success' => false, 'message' => msg('Guide not found or inactive', 'المرشد غير موجود أو غير نشط')], 404);
    }

    // Check if guide_id column exists in bookings table
    $cols = $db->query("SHOW COLUMNS FROM bookings LIKE 'guide_id'")->fetchAll();
    if (empty($cols)) {
        // Add the column if it doesn't exist
        $db->exec("ALTER TABLE bookings ADD COLUMN guide_id INT NULL DEFAULT NULL");
    }

    // Assign guide directly on booking row
    $stmt = $db->prepare("UPDATE bookings SET guide_id = ? WHERE id = ?");
    $stmt->execute([$guide_id, $booking_id]);

    respond(['success' => true, 'message' => msg('Guide ' . $guide['full_name'] . ' assigned successfully', 'تم تعيين المرشد ' . $guide['full_name'] . ' بنجاح')]);
}

function deleteBooking() {
    requireRole('admin');
    $data = json_decode(file_get_contents('php://input'), true);
    $booking_id = (int)($data['booking_id'] ?? 0);

    if (!$booking_id) {
        respond(['success' => false, 'message' => msg('Booking ID required', 'معرف الحجز مطلوب')], 400);
    }

    $db = getDB();
    $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);

    respond(['success' => true, 'message' => msg('Booking deleted successfully', 'تم حذف الحجز بنجاح')]);
}

function getBooking() {
    requireRole(['admin', 'guide', 'tourist']);
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) respond(['success' => false, 'message' => 'Booking ID required'], 400);

    $db = getDB();
    $stmt = $db->prepare("SELECT b.*,
        u.full_name AS tourist_name, u.email AS tourist_email, u.phone AS tourist_phone,
        gu.full_name AS guide_name, gu.email AS guide_email
        FROM bookings b
        JOIN users u ON b.tourist_id = u.id
        LEFT JOIN users gu ON b.guide_id = gu.id
        WHERE b.id = ?");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();

    if (!$booking) respond(['success' => false, 'message' => 'Booking not found'], 404);

    // Get cities
    $stmt2 = $db->prepare("SELECT city_name, days_in_city, sort_order FROM booking_cities WHERE booking_id = ? ORDER BY sort_order ASC");
    $stmt2->execute([$id]);
    $booking['cities'] = $stmt2->fetchAll();

    respond(['success' => true, 'booking' => $booking]);
}
?>
