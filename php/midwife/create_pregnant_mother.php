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

$dutyArea = trim($_POST['duty_area'] ?? '');

if ($dutyArea === '') {
    $dutyArea = trim($_SESSION['assigned_area'] ?? '');
}
$motherName = trim($_POST['mother_name'] ?? '');
$age = trim($_POST['age'] ?? '');
$weeksPregnant = trim($_POST['weeks_pregnant'] ?? '');
$riskLevel = trim($_POST['risk_level'] ?? 'Low Risk');
$lastVisit = trim($_POST['last_visit'] ?? '');
$nextAppointment = trim($_POST['next_appointment'] ?? '');
$contactNumber = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$status = trim($_POST['status'] ?? 'active');

if ($dutyArea === '' || $motherName === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO maternal_care_records (
            midwife_id,
            duty_area,
            mother_name,
            age,
            category,
            weeks_pregnant,
            last_visit,
            next_appointment,
            risk_level,
            contact_number,
            address,
            notes,
            status,
            created_at,
            updated_at
        ) VALUES (
            :midwife_id,
            :duty_area,
            :mother_name,
            :age,
            'pregnant',
            :weeks_pregnant,
            :last_visit,
            :next_appointment,
            :risk_level,
            :contact_number,
            :address,
            :notes,
            :status,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':duty_area' => $dutyArea,
        ':mother_name' => $motherName,
        ':age' => $age !== '' ? (int)$age : null,
        ':weeks_pregnant' => $weeksPregnant !== '' ? (int)$weeksPregnant : null,
        ':last_visit' => $lastVisit !== '' ? $lastVisit : null,
        ':next_appointment' => $nextAppointment !== '' ? $nextAppointment : null,
        ':risk_level' => $riskLevel,
        ':contact_number' => $contactNumber !== '' ? $contactNumber : null,
        ':address' => $address !== '' ? $address : null,
        ':notes' => $notes !== '' ? $notes : null,
        ':status' => $status !== '' ? $status : 'active'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Pregnant mother record saved successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}