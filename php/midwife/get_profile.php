<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

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

try {
    $stmt = $pdo->prepare("
        SELECT
            midwife_id,
            employee_id,
            full_name,
            email,
            phone,
            assigned_area,
            moh_office,
            hire_date,
            birth_date,
            address,
            experience_years,
            status,
            last_login
        FROM midwives
        WHERE midwife_id = :midwife_id
        LIMIT 1
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId
    ]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        echo json_encode([
            'success' => false,
            'message' => 'Profile not found.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'profile' => $profile
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}