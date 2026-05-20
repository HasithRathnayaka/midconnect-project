<?php
session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.',
        'admin' => null
    ]);
    exit;
}

$adminId = $_SESSION['admin_id'] ?? null;

if (!$adminId) {
    echo json_encode([
        'success' => false,
        'message' => 'Admin session not found.',
        'admin' => null
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT
            admin_id,
            username,
            full_name,
            email,
            phone,
            moh_office,
            position,
            created_at,
            updated_at,
            last_login,
            is_active
        FROM moh_admins
        WHERE admin_id = :admin_id
        LIMIT 1
    ");

    $stmt->execute([
        ':admin_id' => $adminId
    ]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode([
            'success' => false,
            'message' => 'Admin profile not found.',
            'admin' => null
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'admin' => $admin
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Admin profile loading error: ' . $e->getMessage(),
        'admin' => null
    ]);
    exit;
}