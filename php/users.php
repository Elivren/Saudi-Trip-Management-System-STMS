<?php
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'profile':
        getProfile();
        break;
    case 'update_profile':
        updateProfile();
        break;
    case 'change_password':
        changePassword();
        break;
    case 'contact':
        submitContact();
        break;
    default:
        respond(['success' => false, 'message' => msg('Invalid action', 'إجراء غير صحيح')], 400);
}

function getProfile() {
    requireLogin();
    $db = getDB();
    $stmt = $db->prepare("SELECT id, full_name, email, phone, role, city, region, profile_picture, status, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    respond(['success' => true, 'user' => $user]);
}

function updateProfile() {
    requireLogin();
    $data = json_decode(file_get_contents('php://input'), true);

    $full_name = sanitize($data['full_name'] ?? '');
    $phone = sanitize($data['phone'] ?? '');
    $city = sanitize($data['city'] ?? '');
    $region = sanitize($data['region'] ?? '');

    if (empty($full_name)) {
        respond(['success' => false, 'message' => msg('Full name is required', 'الاسم الكامل مطلوب')], 400);
    }

    // Name must contain letters only (English or Arabic), no numbers or symbols
    if (!preg_match('/^[\p{L}\s]+$/u', $full_name) || preg_match('/[\d\!\@\#\$\%\^\&\*\(\)\-\_\=\+\[\]\{\}\|\;\:\'\"\,\.\<\>\/\?\`\~]/u', $full_name)) {
        respond(['success' => false, 'message' => msg('Full name must contain letters only, no numbers or symbols', 'الاسم يجب أن يحتوي على حروف فقط، بدون أرقام أو رموز')], 400);
    }

    // Phone must contain numbers only, max 15 digits
    if (!empty($phone) && !preg_match('/^[\+]?[0-9]{7,15}$/', $phone)) {
        respond(['success' => false, 'message' => msg('Phone number must contain numbers only, max 15 digits', 'رقم الهاتف يجب أن يحتوي على أرقام فقط، بحد أقصى 15 رقم')], 400);
    }

    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, city = ?, region = ? WHERE id = ?");
    $stmt->execute([$full_name, $phone, $city, $region, $_SESSION['user_id']]);

    $_SESSION['full_name'] = $full_name;

    respond(['success' => true, 'message' => msg('Profile updated successfully', 'تم تحديث الملف الشخصي بنجاح')]);
}

function changePassword() {
    requireLogin();
    $data = json_decode(file_get_contents('php://input'), true);

    $current = $data['current_password'] ?? '';
    $new_pass = $data['new_password'] ?? '';
    $confirm = $data['confirm_password'] ?? '';

    if (empty($current) || empty($new_pass)) {
        respond(['success' => false, 'message' => msg('All password fields are required', 'جميع حقول كلمة المرور مطلوبة')], 400);
    }

    if (strlen($new_pass) < 6) {
        respond(['success' => false, 'message' => msg('New password must be at least 6 characters', 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل')], 400);
    }

    if ($new_pass !== $confirm) {
        respond(['success' => false, 'message' => msg('New passwords do not match', 'كلمتا المرور الجديدتان غير متطابقتين')], 400);
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($current, $user['password'])) {
        respond(['success' => false, 'message' => msg('Current password is incorrect', 'كلمة المرور الحالية غير صحيحة')], 400);
    }

    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $_SESSION['user_id']]);

    respond(['success' => true, 'message' => msg('Password changed successfully', 'تم تغيير كلمة المرور بنجاح')]);
}

function submitContact() {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = sanitize($data['name'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $subject = sanitize($data['subject'] ?? '');
    $message = sanitize($data['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        respond(['success' => false, 'message' => msg('Name, email, and message are required', 'الاسم والبريد الإلكتروني والرسالة مطلوبة')], 400);
    }

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO feedback (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);

    respond(['success' => true, 'message' => msg('Your message has been sent successfully!', 'تم إرسال رسالتك بنجاح!')]);
}
?>
