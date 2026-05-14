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
    $motherStmt = $pdo->prepare("
        SELECT
            mother_id,
            midwife_id,
            duty_area,
            mother_name,
            age,
            category,
            weeks_pregnant,
            baby_age,
            breastfeeding_status,
            delivery_date,
            delivery_type,
            recovery_status,
            last_visit,
            next_appointment,
            risk_level,
            support_level,
            health_status,
            contact_number,
            address,
            notes,
            status,
            created_at,
            updated_at
        FROM maternal_care_records
        WHERE midwife_id = ?
        ORDER BY created_at DESC
    ");
    $motherStmt->execute([$midwifeId]);
    $mothers = $motherStmt->fetchAll(PDO::FETCH_ASSOC);

    $childStmt = $pdo->prepare("
        SELECT
            child_id,
            midwife_id,
            duty_area,
            child_name,
            mother_name,
            child_category,
            date_of_birth,
            age_label,
            birth_weight,
            current_weight,
            height_cm,
            school,
            last_checkup,
            development_status,
            health_status,
            notes,
            status,
            created_at,
            updated_at
        FROM child_care_records
        WHERE midwife_id = ?
        ORDER BY created_at DESC
    ");
    $childStmt->execute([$midwifeId]);
    $children = $childStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'mothers' => $mothers,
        'children' => $children
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}