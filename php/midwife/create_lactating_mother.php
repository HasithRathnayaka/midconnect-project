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
$babyAge = trim($_POST['baby_age'] ?? '');
$breastfeedingStatus = trim($_POST['breastfeeding_status'] ?? '');
$lastVisit = trim($_POST['last_visit'] ?? '');
$supportLevel = trim($_POST['support_level'] ?? '');
$contactNumber = trim($_POST['contact_number'] ?? '');
$healthStatus = trim($_POST['health_status'] ?? 'Healthy');
$address = trim($_POST['address'] ?? '');
$notes = trim($_POST['notes'] ?? '');

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
            baby_age,
            breastfeeding_status,
            last_visit,
            support_level,
            health_status,
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
            'lactating',
            :baby_age,
            :breastfeeding_status,
            :last_visit,
            :support_level,
            :health_status,
            :contact_number,
            :address,
            :notes,
            'active',
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':duty_area' => $dutyArea,
        ':mother_name' => $motherName,
        ':age' => $age !== '' ? (int)$age : null,
        ':baby_age' => $babyAge !== '' ? $babyAge : null,
        ':breastfeeding_status' => $breastfeedingStatus !== '' ? $breastfeedingStatus : null,
        ':last_visit' => $lastVisit !== '' ? $lastVisit : null,
        ':support_level' => $supportLevel !== '' ? $supportLevel : null,
        ':health_status' => $healthStatus !== '' ? $healthStatus : null,
        ':contact_number' => $contactNumber !== '' ? $contactNumber : null,
        ':address' => $address !== '' ? $address : null,
        ':notes' => $notes !== '' ? $notes : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Lactating mother record saved successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}