<?php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'dashboard':
        dashboard();
        break;
    case 'users':
        listUsers();
        break;
    case 'update_user_status':
        updateUserStatus();
        break;
    case 'delete_user':
        deleteUser();
        break;
    case 'all_trips':
        allTrips();
        break;
    case 'all_bookings':
        allBookings();
        break;
    case 'all_reviews':
        allReviews();
        break;
    case 'faqs':
        listFaqs();
        break;
    case 'save_faq':
        saveFaq();
        break;
    case 'delete_faq':
        deleteFaq();
        break;
    case 'feedback':
        listFeedback();
        break;
    default:
        respond(['success' => false, 'message' => msg('Invalid action', 'إجراء غير صحيح')], 400);
}

function dashboard() {
    requireRole('admin');
    $db = getDB();

    $stats = [];
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
    $stats['total_users'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM trips");
    $stats['total_trips'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
    $stats['pending_bookings'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM feedback WHERE is_read = 0");
    $stats['new_feedback'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'tourist'");
    $stats['total_tourists'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'guide'");
    $stats['total_guides'] = $stmt->fetch()['total'];

    respond(['success' => true, 'stats' => $stats]);
}

function listUsers() {
    requireRole('admin');
    $db = getDB();
    $role = $_GET['role'] ?? '';
    $where = "WHERE role != 'admin'";
    $params = [];

    if ($role && in_array($role, ['tourist', 'guide'])) {
        $where .= " AND role = ?";
        $params[] = $role;
    }

    $stmt = $db->prepare("SELECT id, full_name, email, phone, role, city, region, status, created_at FROM users {$where} ORDER BY created_at DESC");
    $stmt->execute($params);
    respond(['success' => true, 'users' => $stmt->fetchAll()]);
}

function updateUserStatus() {
    requireRole('admin');
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = (int)($data['user_id'] ?? 0);
    $status = sanitize($data['status'] ?? '');

    if (!$user_id || !in_array($status, ['active', 'inactive'])) {
        respond(['success' => false, 'message' => msg('Invalid parameters', 'معطيات غير صحيحة')], 400);
    }

    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
    $stmt->execute([$status, $user_id]);
    respond(['success' => true, 'message' => msg("User status updated to {$status}", 'تم تحديث حالة المستخدم إلى ' . ($status === 'active' ? 'نشط' : 'معطل'))]);
}

function deleteUser() {
    requireRole('admin');
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = (int)($data['user_id'] ?? 0);

    $db = getDB();
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$user_id]);
    respond(['success' => true, 'message' => msg('User deleted', 'تم حذف المستخدم')]);
}

function allTrips() {
    requireRole('admin');
    $db = getDB();
    $stmt = $db->query("SELECT t.*, u.full_name AS guide_name,
        (SELECT COALESCE(SUM(b.num_people),0) FROM bookings b WHERE b.trip_id = t.id AND b.status IN ('accepted','pending')) AS booked_count
        FROM trips t JOIN users u ON t.guide_id = u.id ORDER BY t.created_at DESC");
    respond(['success' => true, 'trips' => $stmt->fetchAll()]);
}

function allBookings() {
    requireRole('admin');
    $db = getDB();

    // Ensure guide_id column exists
    $cols = $db->query("SHOW COLUMNS FROM bookings LIKE 'guide_id'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE bookings ADD COLUMN guide_id INT NULL DEFAULT NULL");
    }

    $stmt = $db->query("SELECT b.*,
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
        u.full_name AS tourist_name,
        CASE
            WHEN b.booking_type = 'trip' THEN gt.full_name
            WHEN b.booking_type = 'package' THEN gp.full_name
            WHEN b.guide_id IS NOT NULL THEN gs.full_name
            ELSE NULL
        END AS guide_name
        FROM bookings b
        JOIN users u ON b.tourist_id = u.id
        LEFT JOIN trips t ON b.trip_id = t.id AND b.booking_type = 'trip'
        LEFT JOIN users gt ON t.guide_id = gt.id
        LEFT JOIN packages p ON b.package_id = p.id AND b.booking_type = 'package'
        LEFT JOIN users gp ON p.guide_id = gp.id
        LEFT JOIN users gs ON b.guide_id = gs.id
        ORDER BY b.created_at DESC");

    respond(['success' => true, 'bookings' => $stmt->fetchAll()]);
}

function allReviews() {
    requireRole('admin');
    $db = getDB();
    $stmt = $db->query("SELECT r.*, t.title AS trip_title, u.full_name AS reviewer_name
        FROM reviews r JOIN trips t ON r.trip_id = t.id JOIN users u ON r.tourist_id = u.id ORDER BY r.created_at DESC");
    respond(['success' => true, 'reviews' => $stmt->fetchAll()]);
}

function listFaqs() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM faqs ORDER BY sort_order ASC");
    respond(['success' => true, 'faqs' => $stmt->fetchAll()]);
}

function saveFaq() {
    requireRole('admin');
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    $question = sanitize($data['question'] ?? '');
    $answer = sanitize($data['answer'] ?? '');

    if (empty($question) || empty($answer)) {
        respond(['success' => false, 'message' => msg('Question and answer required', 'السؤال والإجابة مطلوبان')], 400);
    }

    $db = getDB();
    if ($id) {
        $stmt = $db->prepare("UPDATE faqs SET question = ?, answer = ? WHERE id = ?");
        $stmt->execute([$question, $answer, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
        $stmt->execute([$question, $answer]);
    }
    respond(['success' => true, 'message' => msg('FAQ saved', 'تم حفظ السؤال')]);
}

function deleteFaq() {
    requireRole('admin');
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);

    $db = getDB();
    $stmt = $db->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
    respond(['success' => true, 'message' => msg('FAQ deleted', 'تم حذف السؤال')]);
}

function listFeedback() {
    requireRole('admin');
    $db = getDB();
    $stmt = $db->query("SELECT * FROM feedback ORDER BY created_at DESC");
    respond(['success' => true, 'feedback' => $stmt->fetchAll()]);
}
?>
