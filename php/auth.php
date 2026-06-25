<?php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        login();
        break;
    case 'register':
        register();
        break;
    case 'logout':
        logout();
        break;
    case 'check':
        checkSession();
        break;
    default:
        respond(['success' => false, 'message' => 'Invalid action'], 400);
}

function login() {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = sanitize($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $role = sanitize($data['role'] ?? '');

    if (empty($email) || empty($password) || empty($role)) {
        respond(['success' => false, 'message' => msg('All fields are required', 'جميع الحقول مطلوبة')], 400);
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        respond(['success' => false, 'message' => msg('Invalid email or password', 'البريد الإلكتروني أو كلمة المرور غير صحيحة')], 401);
    }

    if ($user['status'] === 'inactive') {
        respond(['success' => false, 'message' => msg('Your account has been deactivated. Contact admin.', 'تم تعطيل حسابك. تواصل مع المشرف.')], 403);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];

    respond([
        'success' => true,
        'message' => msg('Login successful', 'تم تسجيل الدخول بنجاح'),
        'user' => [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'city' => $user['city'],
            'phone' => $user['phone'],
            'profile_picture' => $user['profile_picture']
        ]
    ]);
}

function register() {
    $data = json_decode(file_get_contents('php://input'), true);

    $full_name = sanitize($data['full_name'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $phone = sanitize($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';
    $role = sanitize($data['role'] ?? 'tourist');
    $city = sanitize($data['city'] ?? '');
    $region = sanitize($data['region'] ?? '');

    if (empty($full_name) || empty($email) || empty($password)) {
        respond(['success' => false, 'message' => msg('Name, email, and password are required', 'الاسم والبريد الإلكتروني وكلمة المرور مطلوبة')], 400);
    }

    // Name must contain letters only (English or Arabic), no numbers or symbols
    if (!preg_match('/^[\p{L}\s]+$/u', $full_name) || preg_match('/[\d\!\@\#\$\%\^\&\*\(\)\-\_\=\+\[\]\{\}\|\;\:\'\"\,\.\<\>\/\?\`\~]/u', $full_name)) {
        respond(['success' => false, 'message' => msg('Full name must contain letters only, no numbers or symbols', 'الاسم يجب أن يحتوي على حروف فقط، بدون أرقام أو رموز')], 400);
    }

    // Phone must contain numbers only, max 15 digits
    if (!empty($phone) && !preg_match('/^[\+]?[0-9]{7,15}$/', $phone)) {
        respond(['success' => false, 'message' => msg('Phone number must contain numbers only, max 15 digits', 'رقم الهاتف يجب أن يحتوي على أرقام فقط، بحد أقصى 15 رقم')], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(['success' => false, 'message' => msg('Invalid email format', 'صيغة البريد الإلكتروني غير صحيحة')], 400);
    }

    if (strlen($password) < 6) {
        respond(['success' => false, 'message' => msg('Password must be at least 6 characters', 'كلمة المرور يجب أن تكون 6 أحرف على الأقل')], 400);
    }

    if ($password !== $confirm_password) {
        respond(['success' => false, 'message' => msg('Passwords do not match', 'كلمتا المرور غير متطابقتين')], 400);
    }

    if (!in_array($role, ['tourist', 'guide'])) {
        respond(['success' => false, 'message' => msg('Invalid role', 'دور غير صحيح')], 400);
    }

    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        respond(['success' => false, 'message' => msg('Email already registered', 'البريد الإلكتروني مسجل مسبقاً')], 409);
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (full_name, email, phone, password, role, city, region) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $phone, $hashed, $role, $city, $region]);

    respond(['success' => true, 'message' => msg('Registration successful! You can now login.', 'تم التسجيل بنجاح! يمكنك تسجيل الدخول الآن.')]);
}

function logout() {
    session_destroy();
    respond(['success' => true, 'message' => msg('Logged out successfully', 'تم تسجيل الخروج بنجاح')]);
}

function checkSession() {
    if (isset($_SESSION['user_id'])) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, full_name, email, role, phone, city, region, profile_picture, status FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        respond(['success' => true, 'logged_in' => true, 'user' => $user]);
    } else {
        respond(['success' => true, 'logged_in' => false]);
    }
}
?>
