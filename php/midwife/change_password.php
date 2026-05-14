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

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'midwife') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
    ]);
    exit;
}

$midwifeId = $_SESSION['midwife_id'] ?? null;

if (!$midwifeId) {
    echo json_encode([
        'success' => false,
        'message' => 'Midwife session not found.'
    ]);
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

if (strlen($newPassword) < 8) {
    echo json_encode([
        'success' => false,
        'message' => 'New password must be at least 8 characters long.'
    ]);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode([
        'success' => false,
        'message' => 'New password and confirm password do not match.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT password_hash
        FROM midwives
        WHERE midwife_id = :midwife_id
        LIMIT 1
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId
    ]);

    $midwife = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$midwife) {
        echo json_encode([
            'success' => false,
            'message' => 'Midwife account not found.'
        ]);
        exit;
    }

    if (!password_verify($currentPassword, $midwife['password_hash'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect.'
        ]);
        exit;
    }

    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateStmt = $pdo->prepare("
        UPDATE midwives
        SET 
            password_hash = :password_hash,
            updated_at = NOW()
        WHERE midwife_id = :midwife_id
    ");

    $updateStmt->execute([
        ':password_hash' => $newPasswordHash,
        ':midwife_id' => $midwifeId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Password changed successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}