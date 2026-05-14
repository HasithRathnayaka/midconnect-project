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

$patientName = trim($_POST['patient_name'] ?? '');
$contactNumber = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$visitType = trim($_POST['visit_type'] ?? '');
$priority = trim($_POST['priority'] ?? 'normal');
$visitDate = trim($_POST['visit_date'] ?? '');
$startTime = trim($_POST['start_time'] ?? '');
$durationMinutes = (int)($_POST['duration_minutes'] ?? 45);
$dutyArea = trim($_POST['duty_area'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (
    $patientName === '' ||
    $address === '' ||
    $visitType === '' ||
    $visitDate === '' ||
    $startTime === '' ||
    $dutyArea === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO home_visits (
            midwife_id,
            patient_name,
            contact_number,
            address,
            visit_date,
            start_time,
            duration_minutes,
            duty_area,
            visit_type,
            priority,
            reason,
            status,
            notes,
            created_at,
            updated_at
        ) VALUES (
            :midwife_id,
            :patient_name,
            :contact_number,
            :address,
            :visit_date,
            :start_time,
            :duration_minutes,
            :duty_area,
            :visit_type,
            :priority,
            :reason,
            'scheduled',
            :notes,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':patient_name' => $patientName,
        ':contact_number' => $contactNumber !== '' ? $contactNumber : null,
        ':address' => $address,
        ':visit_date' => $visitDate,
        ':start_time' => $startTime,
        ':duration_minutes' => $durationMinutes,
        ':duty_area' => $dutyArea,
        ':visit_type' => $visitType,
        ':priority' => $priority,
        ':reason' => $reason !== '' ? $reason : null,
        ':notes' => $notes !== '' ? $notes : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Home visit scheduled successfully.',
        'visit_id' => $pdo->lastInsertId()
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}