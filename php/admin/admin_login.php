<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$mohOffice = trim($_POST['moh_office'] ?? '');
$secureSession = isset($_POST['secure_session']);

if ($username === '' || $password === '' || $mohOffice === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Username, password, and MOH office are required.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT
            admin_id,
            username,
            password_hash,
            full_name,
            email,
            phone,
            moh_office,
            position,
            is_active,
            login_attempts,
            locked_until
        FROM moh_admins
        WHERE username = :username
        LIMIT 1
    ");

    $stmt->execute([
        ':username' => $username
    ]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username, password, or MOH office.'
        ]);
        exit;
    }

    if ((int)$admin['is_active'] !== 1) {
        echo json_encode([
            'success' => false,
            'message' => 'This admin account is inactive.'
        ]);
        exit;
    }

    if (!empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
        echo json_encode([
            'success' => false,
            'message' => 'This account is temporarily locked. Please try again later.'
        ]);
        exit;
    }

    if ($admin['moh_office'] !== $mohOffice) {
        increaseAdminLoginAttempt($pdo, (int)$admin['admin_id']);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid username, password, or MOH office.'
        ]);
        exit;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        increaseAdminLoginAttempt($pdo, (int)$admin['admin_id']);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid username, password, or MOH office.'
        ]);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_type'] = 'admin';
    $_SESSION['admin_id'] = (int)$admin['admin_id'];
    $_SESSION['username'] = $admin['username'];
    $_SESSION['full_name'] = $admin['full_name'];
    $_SESSION['email'] = $admin['email'];
    $_SESSION['phone'] = $admin['phone'];
    $_SESSION['moh_office'] = $admin['moh_office'];
    $_SESSION['position'] = $admin['position'];
    $_SESSION['secure_session'] = $secureSession ? 1 : 0;
    $_SESSION['login_time'] = date('Y-m-d H:i:s');

    $updateStmt = $pdo->prepare("
        UPDATE moh_admins
        SET
            last_login = NOW(),
            login_attempts = 0,
            locked_until = NULL
        WHERE admin_id = :admin_id
    ");

    $updateStmt->execute([
        ':admin_id' => $admin['admin_id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful.',
        'redirect' => 'admin/dashboard.php',
        'admin' => [
            'admin_id' => (int)$admin['admin_id'],
            'username' => $admin['username'],
            'full_name' => $admin['full_name'],
            'email' => $admin['email'],
            'phone' => $admin['phone'],
            'moh_office' => $admin['moh_office'],
            'position' => $admin['position'],
            'role' => 'admin'
        ]
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}

function increaseAdminLoginAttempt(PDO $pdo, int $adminId): void
{
    $stmt = $pdo->prepare("
        UPDATE moh_admins
        SET
            login_attempts = login_attempts + 1,
            locked_until = CASE
                WHEN login_attempts + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                ELSE locked_until
            END
        WHERE admin_id = :admin_id
    ");

    $stmt->execute([
        ':admin_id' => $adminId
    ]);
}