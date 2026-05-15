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

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
    ]);
    exit;
}

$midwifeId = trim($_POST['midwife_id'] ?? '');
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$assignedArea = trim($_POST['assigned_area'] ?? '');
$mohOffice = trim($_POST['moh_office'] ?? '');
$status = trim($_POST['status'] ?? 'active');
$experienceYears = trim($_POST['experience_years'] ?? '0');
$address = trim($_POST['address'] ?? '');

if ($midwifeId === '' || $fullName === '' || $email === '' || $assignedArea === '' || $mohOffice === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
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
            assigned_area = :assigned_area,
            moh_office = :moh_office,
            status = :status,
            experience_years = :experience_years,
            address = :address,
            updated_at = NOW()
        WHERE midwife_id = :midwife_id
    ");

    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':phone' => $phone !== '' ? $phone : null,
        ':assigned_area' => $assignedArea,
        ':moh_office' => $mohOffice,
        ':status' => $status,
        ':experience_years' => $experienceYears !== '' ? (int)$experienceYears : 0,
        ':address' => $address !== '' ? $address : null,
        ':midwife_id' => $midwifeId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Midwife updated successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}