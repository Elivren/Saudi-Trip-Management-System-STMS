<?php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        listTrips();
        break;
    case 'get':
        getTrip();
        break;
    case 'create':
        createTrip();
        break;
    case 'update':
        updateTrip();
        break;
    case 'delete':
        deleteTrip();
        break;
    case 'guide_trips':
        guideTrips();
        break;
    default:
        respond(['success' => false, 'message' => msg('Invalid action', 'إجراء غير صحيح')], 400);
}

function listTrips() {
    $db = getDB();
    $where = ["t.status != 'cancelled'"];
    $params = [];

    if (!empty($_GET['location'])) {
        $where[] = "(t.location_city LIKE ? OR t.location_region LIKE ?)";
        $params[] = '%' . $_GET['location'] . '%';
        $params[] = '%' . $_GET['location'] . '%';
    }
    if (!empty($_GET['date'])) {
        $where[] = "t.start_date >= ?";
        $params[] = $_GET['date'];
    }
    if (!empty($_GET['price_min'])) {
        $where[] = "t.price >= ?";
        $params[] = $_GET['price_min'];
    }
    if (!empty($_GET['price_max'])) {
        $where[] = "t.price <= ?";
        $params[] = $_GET['price_max'];
    }
    if (!empty($_GET['search'])) {
        $where[] = "(t.title LIKE ? OR t.description LIKE ?)";
        $params[] = '%' . $_GET['search'] . '%';
        $params[] = '%' . $_GET['search'] . '%';
    }

    $whereClause = implode(' AND ', $where);

    $sql = "SELECT t.*, u.full_name AS guide_name, u.city AS guide_city,
            (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.trip_id = t.id) AS avg_rating,
            (SELECT COUNT(*) FROM reviews r WHERE r.trip_id = t.id) AS review_count,
            (SELECT COALESCE(SUM(b.num_people),0) FROM bookings b WHERE b.trip_id = t.id AND b.status IN ('accepted','pending')) AS booked_count
            FROM trips t
            JOIN users u ON t.guide_id = u.id
            WHERE {$whereClause}
            ORDER BY t.start_date ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $trips = $stmt->fetchAll();

    foreach ($trips as &$trip) {
        $trip['itinerary'] = json_decode($trip['itinerary'], true);
    }

    respond(['success' => true, 'trips' => $trips]);
}

function getTrip() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) respond(['success' => false, 'message' => msg(msg('Trip ID required', 'معرف الرحلة مطلوب'), 'معرف الرحلة مطلوب')], 400);

    $db = getDB();
    $stmt = $db->prepare("SELECT t.*, u.full_name AS guide_name, u.email AS guide_email, u.phone AS guide_phone, u.city AS guide_city,
        (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.trip_id = t.id) AS avg_rating,
        (SELECT COUNT(*) FROM reviews r WHERE r.trip_id = t.id) AS review_count,
        (SELECT COALESCE(SUM(b.num_people),0) FROM bookings b WHERE b.trip_id = t.id AND b.status IN ('accepted','pending')) AS booked_count
        FROM trips t JOIN users u ON t.guide_id = u.id WHERE t.id = ?");
    $stmt->execute([$id]);
    $trip = $stmt->fetch();

    if (!$trip) respond(['success' => false, 'message' => msg('Trip not found', 'الرحلة غير موجودة')], 404);

    $trip['itinerary'] = json_decode($trip['itinerary'], true);

    $stmt2 = $db->prepare("SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON r.tourist_id = u.id WHERE r.trip_id = ? ORDER BY r.created_at DESC");
    $stmt2->execute([$id]);
    $trip['reviews'] = $stmt2->fetchAll();

    respond(['success' => true, 'trip' => $trip]);
}

function createTrip() {
    requireRole('guide');
    $data = json_decode(file_get_contents('php://input'), true);

    $required = ['title', 'description', 'location_city', 'price', 'max_tourists', 'start_date', 'end_date'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            respond(['success' => false, 'message' => "Field '{$field}' is required"], 400);
        }
    }

    $db = getDB();

    // Auto-calculate duration from start/end dates
    $start = new DateTime($data['start_date']);
    $end = new DateTime($data['end_date']);
    $diffDays = $start->diff($end)->days + 1;
    $autoDuration = $diffDays === 1 ? '1 Day' : $diffDays . ' Days';

    $stmt = $db->prepare("INSERT INTO trips (guide_id, title, description, location_city, location_region, cover_image, price, max_tourists, start_date, end_date, duration, itinerary)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        sanitize($data['title']),
        sanitize($data['description']),
        sanitize($data['location_city']),
        sanitize($data['location_region'] ?? ''),
        sanitize($data['cover_image'] ?? ''),
        (float)$data['price'],
        (int)$data['max_tourists'],
        $data['start_date'],
        $data['end_date'],
        $autoDuration,
        json_encode($data['itinerary'] ?? [])
    ]);

    respond(['success' => true, 'message' => msg('Trip created successfully', 'تم إنشاء الرحلة بنجاح'), 'id' => $db->lastInsertId()]);
}

function updateTrip() {
    requireRole('guide');
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    if (!$id) respond(['success' => false, 'message' => msg(msg('Trip ID required', 'معرف الرحلة مطلوب'), 'معرف الرحلة مطلوب')], 400);

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM trips WHERE id = ? AND guide_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) respond(['success' => false, 'message' => msg('Trip not found or unauthorized', 'الرحلة غير موجودة أو غير مصرح')], 403);

    // Auto-calculate duration from dates
    $start = new DateTime($data['start_date']);
    $end = new DateTime($data['end_date']);
    $diffDays = $start->diff($end)->days + 1;
    $autoDuration = $diffDays === 1 ? '1 Day' : $diffDays . ' Days';

    $stmt = $db->prepare("UPDATE trips SET title=?, description=?, location_city=?, location_region=?, cover_image=?, price=?, max_tourists=?, start_date=?, end_date=?, duration=?, status=?, itinerary=? WHERE id=?");
    $stmt->execute([
        sanitize($data['title']),
        sanitize($data['description']),
        sanitize($data['location_city']),
        sanitize($data['location_region'] ?? ''),
        sanitize($data['cover_image'] ?? ''),
        (float)$data['price'],
        (int)$data['max_tourists'],
        $data['start_date'],
        $data['end_date'],
        $autoDuration,
        sanitize($data['status'] ?? 'open'),
        json_encode($data['itinerary'] ?? []),
        $id
    ]);

    respond(['success' => true, 'message' => msg('Trip updated successfully', 'تم تحديث الرحلة بنجاح')]);
}

function deleteTrip() {
    requireRole(['guide', 'admin']);
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    if (!$id) respond(['success' => false, 'message' => msg(msg('Trip ID required', 'معرف الرحلة مطلوب'), 'معرف الرحلة مطلوب')], 400);

    $db = getDB();

    if ($_SESSION['role'] === 'guide') {
        $stmt = $db->prepare("DELETE FROM trips WHERE id = ? AND guide_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
    } else {
        $stmt = $db->prepare("DELETE FROM trips WHERE id = ?");
        $stmt->execute([$id]);
    }

    respond(['success' => true, 'message' => msg('Trip deleted successfully', 'تم حذف الرحلة بنجاح')]);
}

function guideTrips() {
    requireRole('guide');
    $db = getDB();
    $stmt = $db->prepare("SELECT t.*,
        (SELECT COALESCE(SUM(b.num_people),0) FROM bookings b WHERE b.trip_id = t.id AND b.status IN ('accepted','pending')) AS booked_count,
        (SELECT COUNT(*) FROM bookings b WHERE b.trip_id = t.id) AS total_bookings
        FROM trips t WHERE t.guide_id = ? ORDER BY t.start_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $trips = $stmt->fetchAll();

    foreach ($trips as &$trip) {
        $trip['itinerary'] = json_decode($trip['itinerary'], true);
    }

    respond(['success' => true, 'trips' => $trips]);
}
?>
