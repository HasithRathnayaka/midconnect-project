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

$dutyArea = trim($_POST['duty_area'] ?? '');
$motherName = trim($_POST['mother_name'] ?? '');
$age = trim($_POST['age'] ?? '');
$category = trim($_POST['category'] ?? '');
$weeksPregnant = trim($_POST['weeks_pregnant'] ?? '');
$babyAge = trim($_POST['baby_age'] ?? '');
$breastfeedingStatus = trim($_POST['breastfeeding_status'] ?? '');
$deliveryDate = trim($_POST['delivery_date'] ?? '');
$deliveryType = trim($_POST['delivery_type'] ?? '');
$recoveryStatus = trim($_POST['recovery_status'] ?? '');
$lastVisit = trim($_POST['last_visit'] ?? '');
$nextAppointment = trim($_POST['next_appointment'] ?? '');
$riskLevel = trim($_POST['risk_level'] ?? '');
$supportLevel = trim($_POST['support_level'] ?? '');
$healthStatus = trim($_POST['health_status'] ?? '');
$contactNumber = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($dutyArea === '' || $motherName === '' || $category === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

$allowedCategories = ['pregnant', 'lactating', 'postnatal'];

if (!in_array($category, $allowedCategories, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid mother category.'
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
            baby_age,
            breastfeeding_status,
            delivery_date,
            delivery_type,
            recovery_status,
            last_visit,
            next_appointment,
            risk_level,
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
            :category,
            :weeks_pregnant,
            :baby_age,
            :breastfeeding_status,
            :delivery_date,
            :delivery_type,
            :recovery_status,
            :last_visit,
            :next_appointment,
            :risk_level,
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
        ':category' => $category,
        ':weeks_pregnant' => $weeksPregnant !== '' ? (int)$weeksPregnant : null,
        ':baby_age' => $babyAge !== '' ? $babyAge : null,
        ':breastfeeding_status' => $breastfeedingStatus !== '' ? $breastfeedingStatus : null,
        ':delivery_date' => $deliveryDate !== '' ? $deliveryDate : null,
        ':delivery_type' => $deliveryType !== '' ? $deliveryType : null,
        ':recovery_status' => $recoveryStatus !== '' ? $recoveryStatus : null,
        ':last_visit' => $lastVisit !== '' ? $lastVisit : null,
        ':next_appointment' => $nextAppointment !== '' ? $nextAppointment : null,
        ':risk_level' => $riskLevel !== '' ? $riskLevel : null,
        ':support_level' => $supportLevel !== '' ? $supportLevel : null,
        ':health_status' => $healthStatus !== '' ? $healthStatus : null,
        ':contact_number' => $contactNumber !== '' ? $contactNumber : null,
        ':address' => $address !== '' ? $address : null,
        ':notes' => $notes !== '' ? $notes : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Maternal care record saved successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}