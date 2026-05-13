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
            health_education_id,
            midwife_id,
            session_date,
            venue,
            topic,
            audience,
            attendees,
            duration_mins,
            materials,
            outcomes,
            status,
            created_at,
            updated_at
        FROM health_education_sessions
        WHERE midwife_id = ?
        ORDER BY session_date DESC, created_at DESC
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