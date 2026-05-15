<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
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
            profile_image,
            last_login,
            created_at,
            updated_at
        FROM midwives
        ORDER BY created_at DESC, midwife_id DESC
    ");

    $stmt->execute();
    $midwives = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'midwives' => $midwives
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}