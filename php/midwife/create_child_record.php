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
$childName = trim($_POST['child_name'] ?? '');
$motherName = trim($_POST['mother_name'] ?? '');
$childCategory = trim($_POST['child_category'] ?? '');
$dateOfBirth = trim($_POST['date_of_birth'] ?? '');
$ageLabel = trim($_POST['age_label'] ?? '');
$birthWeight = trim($_POST['birth_weight'] ?? '');
$currentWeight = trim($_POST['current_weight'] ?? '');
$heightCm = trim($_POST['height_cm'] ?? '');
$school = trim($_POST['school'] ?? '');
$lastCheckup = trim($_POST['last_checkup'] ?? '');
$developmentStatus = trim($_POST['development_status'] ?? '');
$healthStatus = trim($_POST['health_status'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($dutyArea === '' || $childName === '' || $childCategory === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

$allowedCategories = ['newborns', 'young', 'childs'];

if (!in_array($childCategory, $allowedCategories, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid child category.'
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
            date_of_birth,
            age_label,
            birth_weight,
            current_weight,
            height_cm,
            school,
            last_checkup,
            development_status,
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
            :child_category,
            :date_of_birth,
            :age_label,
            :birth_weight,
            :current_weight,
            :height_cm,
            :school,
            :last_checkup,
            :development_status,
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
        ':child_category' => $childCategory,
        ':date_of_birth' => $dateOfBirth !== '' ? $dateOfBirth : null,
        ':age_label' => $ageLabel !== '' ? $ageLabel : null,
        ':birth_weight' => $birthWeight !== '' ? $birthWeight : null,
        ':current_weight' => $currentWeight !== '' ? $currentWeight : null,
        ':height_cm' => $heightCm !== '' ? $heightCm : null,
        ':school' => $school !== '' ? $school : null,
        ':last_checkup' => $lastCheckup !== '' ? $lastCheckup : null,
        ':development_status' => $developmentStatus !== '' ? $developmentStatus : null,
        ':health_status' => $healthStatus !== '' ? $healthStatus : null,
        ':notes' => $notes !== '' ? $notes : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Child care record saved successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}