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

$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

if ($fullName === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Full name is required.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE midwives
        SET 
            full_name = :full_name,
            phone = :phone,
            address = :address,
            updated_at = NOW()
        WHERE midwife_id = :midwife_id
    ");

    $stmt->execute([
        ':full_name' => $fullName,
        ':phone' => $phone !== '' ? $phone : null,
        ':address' => $address !== '' ? $address : null,
        ':midwife_id' => $midwifeId
    ]);

    $_SESSION['full_name'] = $fullName;

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}