<?php
// Session configuration - must be before session_start()
ini_set('session.cookie_path', '/02stmsarabic/02stms/');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 86400); // 24 hours

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS headers for credentials support
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if ($origin) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

define('DB_HOST', 'localhost');
define('DB_NAME', 'stms_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        respond(['success' => false, 'message' => 'Unauthorized'], 401);
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$roles)) {
        respond(['success' => false, 'message' => 'Forbidden'], 403);
    }
}

function sanitize($val) {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function getLang() {
    static $lang = null;
    if ($lang !== null) return $lang;
    $input = json_decode(file_get_contents('php://input'), true);
    $lang = $input['lang'] ?? $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
    return in_array($lang, ['ar', 'en']) ? $lang : 'en';
}

function msg($en, $ar) {
    return getLang() === 'ar' ? $ar : $en;
}
?>
