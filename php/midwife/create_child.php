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

$childName = trim($_POST['child_name'] ?? '');
$motherName = trim($_POST['mother_name'] ?? '');
$ageLabel = trim($_POST['age_label'] ?? '');
$school = trim($_POST['school'] ?? '');
$lastCheckup = trim($_POST['last_checkup'] ?? '');
$healthStatus = trim($_POST['health_status'] ?? 'Healthy');
$notes = trim($_POST['notes'] ?? '');

if ($dutyArea === '' || $childName === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO child_care_records (
            midwife_id,
            duty_area,
            child_name,
            mother_name,
            child_category,
            age_label,
            school,
            last_checkup,
            health_status,
            notes,
            status,
            created_at,
            updated_at
        ) VALUES (
            :midwife_id,
            :duty_area,
            :child_name,
            :mother_name,
            'childs',
            :age_label,
            :school,
            :last_checkup,
            :health_status,
            :notes,
            'active',
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':duty_area' => $dutyArea,
        ':child_name' => $childName,
        ':mother_name' => $motherName !== '' ? $motherName : null,
        ':age_label' => $ageLabel !== '' ? $ageLabel : null,
        ':school' => $school !== '' ? $school : null,
        ':last_checkup' => $lastCheckup !== '' ? $lastCheckup : null,
        ':health_status' => $healthStatus !== '' ? $healthStatus : null,
        ':notes' => $notes !== '' ? $notes : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Child record saved successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}