<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'midwife') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$midwifeId = $_SESSION['midwife_id'] ?? null;

if (!$midwifeId) {
    echo json_encode([
        'success' => false,
        'message' => 'Midwife session not found'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            midwife_id,
            patient_name,
            contact_number,
            address,
            visit_date,
            start_time,
            end_time,
            duration_minutes,
            duty_area,
            visit_type,
            priority,
            reason,
            status,
            notes,
            completed_at,
            created_at,
            updated_at
        FROM home_visits
        WHERE midwife_id = ?
        ORDER BY visit_date ASC, start_time ASC
    ");

    $stmt->execute([$midwifeId]);
    $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'visits' => $visits
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}