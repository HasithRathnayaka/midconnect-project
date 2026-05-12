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
        'message' => 'Unauthorized. Please login again.'
    ]);
    exit;
}

$midwifeId = $_SESSION['midwife_id'] ?? null;
$scheduleId = $_POST['schedule_id'] ?? null;

if (!$midwifeId || !$scheduleId) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit;
}

try {
    $checkStmt = $pdo->prepare("
        SELECT schedule_id, status
        FROM schedules
        WHERE schedule_id = ?
        AND midwife_id = ?
        LIMIT 1
    ");
    $checkStmt->execute([$scheduleId, $midwifeId]);
    $schedule = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        echo json_encode([
            'success' => false,
            'message' => 'Schedule item not found.'
        ]);
        exit;
    }

    if ($schedule['status'] === 'completed') {
        echo json_encode([
            'success' => false,
            'message' => 'Schedule item is already completed.'
        ]);
        exit;
    }

    $updateStmt = $pdo->prepare("
        UPDATE schedules
        SET status = 'completed',
            updated_at = NOW()
        WHERE schedule_id = ?
        AND midwife_id = ?
    ");
    $updateStmt->execute([$scheduleId, $midwifeId]);

    echo json_encode([
        'success' => true,
        'message' => 'Schedule item completed successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}