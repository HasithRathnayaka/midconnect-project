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
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$birthDate = trim($_POST['birth_date'] ?? '');
$address = trim($_POST['address'] ?? '');

if ($fullName === '' || $email === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Full name and email are required.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ]);
    exit;
}

try {
    $checkStmt = $pdo->prepare("
        SELECT midwife_id
        FROM midwives
        WHERE email = :email
        AND midwife_id != :midwife_id
        LIMIT 1
    ");

    $checkStmt->execute([
        ':email' => $email,
        ':midwife_id' => $midwifeId
    ]);

    if ($checkStmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'This email is already used by another midwife.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE midwives
        SET 
            full_name = :full_name,
            email = :email,
            phone = :phone,
            birth_date = :birth_date,
            address = :address,
            updated_at = NOW()
        WHERE midwife_id = :midwife_id
    ");

    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':phone' => $phone !== '' ? $phone : null,
        ':birth_date' => $birthDate !== '' ? $birthDate : null,
        ':address' => $address !== '' ? $address : null,
        ':midwife_id' => $midwifeId
    ]);

    $_SESSION['full_name'] = $fullName;
    $_SESSION['email'] = $email;

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