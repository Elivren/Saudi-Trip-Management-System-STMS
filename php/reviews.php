<?php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        createReview();
        break;
    case 'my_reviews':
        myReviews();
        break;
    case 'guide_reviews':
        guideReviews();
        break;
    case 'trip_reviews':
        tripReviews();
        break;
    case 'delete':
        deleteReview();
        break;
    default:
        respond(['success' => false, 'message' => msg('Invalid action', 'إجراء غير صحيح')], 400);
}

function createReview() {
    requireRole('tourist');
    $data = json_decode(file_get_contents('php://input'), true);

    $trip_id = (int)($data['trip_id'] ?? 0);
    $rating = (int)($data['rating'] ?? 0);
    $comment = sanitize($data['comment'] ?? '');

    if (!$trip_id || $rating < 1 || $rating > 5) {
        respond(['success' => false, 'message' => msg('Valid trip ID and rating (1-5) required', 'معرف الرحلة والتقييم (1-5) مطلوبان')], 400);
    }

    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM bookings WHERE trip_id = ? AND tourist_id = ? AND status = 'accepted'");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        respond(['success' => false, 'message' => msg('You can only review trips you have booked', 'يمكنك تقييم الرحلات التي حجزتها فقط')], 403);
    }

    $stmt = $db->prepare("SELECT id FROM reviews WHERE trip_id = ? AND tourist_id = ?");
    $stmt->execute([$trip_id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        respond(['success' => false, 'message' => msg('You have already reviewed this trip', 'لقد قيّمت هذه الرحلة مسبقاً')], 409);
    }

    $stmt = $db->prepare("INSERT INTO reviews (trip_id, tourist_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$trip_id, $_SESSION['user_id'], $rating, $comment]);

    respond(['success' => true, 'message' => msg('Review submitted successfully', 'تم إرسال التقييم بنجاح')]);
}

function myReviews() {
    requireRole('tourist');
    $db = getDB();
    $stmt = $db->prepare("SELECT r.*, t.title AS trip_title, t.location_city FROM reviews r JOIN trips t ON r.trip_id = t.id WHERE r.tourist_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    respond(['success' => true, 'reviews' => $stmt->fetchAll()]);
}

function guideReviews() {
    requireRole('guide');
    $db = getDB();
    $stmt = $db->prepare("SELECT r.*, t.title AS trip_title, u.full_name AS reviewer_name FROM reviews r JOIN trips t ON r.trip_id = t.id JOIN users u ON r.tourist_id = u.id WHERE t.guide_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    respond(['success' => true, 'reviews' => $stmt->fetchAll()]);
}

function tripReviews() {
    $trip_id = (int)($_GET['trip_id'] ?? 0);
    if (!$trip_id) respond(['success' => false, 'message' => msg('Trip ID required', 'معرف الرحلة مطلوب')], 400);

    $db = getDB();
    $stmt = $db->prepare("SELECT r.*, u.full_name AS reviewer_name FROM reviews r JOIN users u ON r.tourist_id = u.id WHERE r.trip_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$trip_id]);
    respond(['success' => true, 'reviews' => $stmt->fetchAll()]);
}

function deleteReview() {
    requireRole('admin');
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);

    $db = getDB();
    $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$id]);
    respond(['success' => true, 'message' => msg('Review deleted', 'تم حذف التقييم')]);
}
?>
