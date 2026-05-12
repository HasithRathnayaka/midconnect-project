<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
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

$activityType = trim($_POST['activity_type'] ?? '');
$activityDateTime = trim($_POST['activity_datetime'] ?? '');
$patientName = trim($_POST['patient_name'] ?? '');
$location = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');
$duration = trim($_POST['duration'] ?? '');
$followupRequired = trim($_POST['followup_required'] ?? 'no');
$priority = trim($_POST['priority'] ?? 'normal');

if (
    $activityType === '' ||
    $activityDateTime === '' ||
    $patientName === '' ||
    $location === '' ||
    $duration === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

$activityTypeMap = [
    'home_visit' => 'HOME_VISIT',
    'clinic_visit' => 'CLINIC_VISIT',
    'vaccination' => 'VACCINATION',
    'counseling' => 'COUNSELING',
    'health_education' => 'HEALTH_EDUCATION',
    'emergency_response' => 'EMERGENCY_RESPONSE'
];

$typeCode = $activityTypeMap[$activityType] ?? strtoupper($activityType);

try {
    $typeStmt = $pdo->prepare("
        SELECT type_id 
        FROM activity_types 
        WHERE type_code = ?
        LIMIT 1
    ");
    $typeStmt->execute([$typeCode]);
    $activityTypeRow = $typeStmt->fetch(PDO::FETCH_ASSOC);

    if (!$activityTypeRow) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid activity type.'
        ]);
        exit;
    }

    $activityTypeId = $activityTypeRow['type_id'];

    $dateTime = new DateTime($activityDateTime);
    $activityDate = $dateTime->format('Y-m-d');
    $startTime = $dateTime->format('H:i:s');

    $durationMinutes = (int) $duration;

    $endDateTime = clone $dateTime;
    $endDateTime->modify('+' . $durationMinutes . ' minutes');
    $endTime = $endDateTime->format('H:i:s');

    $followupValue = ($followupRequired === 'yes') ? 1 : 0;

    $stmt = $pdo->prepare("
        INSERT INTO activities (
            midwife_id,
            activity_type_id,
            patient_name,
            activity_date,
            start_time,
            end_time,
            duration_minutes,
            location,
            description,
            follow_up_required,
            priority_level,
            status,
            created_at,
            updated_at
        ) VALUES (
            :midwife_id,
            :activity_type_id,
            :patient_name,
            :activity_date,
            :start_time,
            :end_time,
            :duration_minutes,
            :location,
            :description,
            :follow_up_required,
            :priority_level,
            'completed',
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':activity_type_id' => $activityTypeId,
        ':patient_name' => $patientName,
        ':activity_date' => $activityDate,
        ':start_time' => $startTime,
        ':end_time' => $endTime,
        ':duration_minutes' => $durationMinutes,
        ':location' => $location,
        ':description' => $description,
        ':follow_up_required' => $followupValue,
        ':priority_level' => $priority
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Activity logged successfully.',
        'activity_id' => $pdo->lastInsertId()
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}