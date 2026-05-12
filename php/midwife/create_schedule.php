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

if (!$midwifeId) {
    echo json_encode([
        'success' => false,
        'message' => 'Midwife session not found.'
    ]);
    exit;
}

$scheduledDate = trim($_POST['scheduled_date'] ?? '');
$startTime = trim($_POST['start_time'] ?? '');
$activityTypeCode = trim($_POST['activity_type'] ?? '');
$duration = (int)($_POST['duration'] ?? 60);
$description = trim($_POST['description'] ?? '');
$patientName = trim($_POST['patient_name'] ?? '');
$location = trim($_POST['location'] ?? '');
$priorityLevel = trim($_POST['priority_level'] ?? 'normal');
$dutyArea = trim($_POST['duty_area'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (
    $scheduledDate === '' ||
    $startTime === '' ||
    $activityTypeCode === '' ||
    $description === '' ||
    $location === '' ||
    $dutyArea === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

try {
    $typeStmt = $pdo->prepare("
        SELECT type_id
        FROM activity_types
        WHERE type_code = ?
        AND is_active = 1
        LIMIT 1
    ");
    $typeStmt->execute([$activityTypeCode]);
    $activityType = $typeStmt->fetch(PDO::FETCH_ASSOC);

    if (!$activityType) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid activity type.'
        ]);
        exit;
    }

    $activityTypeId = $activityType['type_id'];

    $startDateTime = new DateTime($scheduledDate . ' ' . $startTime);
    $endDateTime = clone $startDateTime;
    $endDateTime->modify('+' . $duration . ' minutes');

    $estimatedEndTime = $endDateTime->format('H:i:s');

    $finalNotes = $notes;
    if ($dutyArea !== '') {
        $finalNotes = trim($finalNotes . "\nDuty Area: " . $dutyArea);
    }

    $stmt = $pdo->prepare("
        INSERT INTO schedules (
            midwife_id,
            activity_type_id,
            scheduled_date,
            start_time,
            estimated_end_time,
            patient_name,
            location,
            description,
            priority_level,
            status,
            notes,
            created_at,
            updated_at
        ) VALUES (
            :midwife_id,
            :activity_type_id,
            :scheduled_date,
            :start_time,
            :estimated_end_time,
            :patient_name,
            :location,
            :description,
            :priority_level,
            'scheduled',
            :notes,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':activity_type_id' => $activityTypeId,
        ':scheduled_date' => $scheduledDate,
        ':start_time' => $startTime,
        ':estimated_end_time' => $estimatedEndTime,
        ':patient_name' => $patientName !== '' ? $patientName : null,
        ':location' => $location,
        ':description' => $description,
        ':priority_level' => $priorityLevel,
        ':notes' => $finalNotes !== '' ? $finalNotes : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Schedule item added successfully.',
        'schedule_id' => $pdo->lastInsertId()
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}