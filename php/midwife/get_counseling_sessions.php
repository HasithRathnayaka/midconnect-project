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
            counseling_id,
            midwife_id,
            session_datetime,
            duration_mins,
            client_ref,
            focus,
            location_type,
            notes,
            followup,
            followup_date,
            referral_details,
            status,
            created_at,
            updated_at
        FROM counseling_sessions
        WHERE midwife_id = ?
        ORDER BY session_datetime DESC
    ");

    $stmt->execute([$midwifeId]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'sessions' => $sessions
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}