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
            s.schedule_id,
            s.scheduled_date,
            s.start_time,
            s.estimated_end_time,
            s.patient_name,
            s.location,
            s.description,
            s.priority_level,
            s.status,
            s.notes,
            at.type_name,
            at.type_code
        FROM schedules s
        INNER JOIN activity_types at ON s.activity_type_id = at.type_id
        WHERE s.midwife_id = ?
        ORDER BY s.scheduled_date ASC, s.start_time ASC
    ");

    $stmt->execute([$midwifeId]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($schedules as &$schedule) {
        $schedule['duty_area'] = 'General';

        if (!empty($schedule['notes'])) {
            if (preg_match('/Duty Area:\s*(.+)/i', $schedule['notes'], $matches)) {
                $schedule['duty_area'] = trim($matches[1]);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'schedules' => $schedules
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}