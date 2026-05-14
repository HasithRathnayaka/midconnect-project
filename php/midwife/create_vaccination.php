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
$patientAge = trim($_POST['patient_age'] ?? '');
$contactNumber = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$dutyArea = trim($_POST['duty_area'] ?? '');
$vaccineCategory = trim($_POST['vaccine_category'] ?? '');
$vaccineCode = trim($_POST['vaccine_code'] ?? '');
$vaccinationDate = trim($_POST['vaccination_date'] ?? '');
$vaccinationTime = trim($_POST['vaccination_time'] ?? '');
$location = trim($_POST['location'] ?? '');
$doseNumber = trim($_POST['dose_number'] ?? '');
$nextDueDate = trim($_POST['next_due_date'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (
    $patientName === '' ||
    $dutyArea === '' ||
    $vaccineCategory === '' ||
    $vaccineCode === '' ||
    $vaccinationDate === '' ||
    $vaccinationTime === '' ||
    $location === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $vaccineStmt = $pdo->prepare("
        SELECT vaccine_id, batch_number, stock_quantity
        FROM vaccine_inventory
        WHERE vaccine_code = ?
        AND category = ?
        LIMIT 1
    ");

    $vaccineStmt->execute([$vaccineCode, $vaccineCategory]);
    $vaccine = $vaccineStmt->fetch(PDO::FETCH_ASSOC);

    if (!$vaccine) {
        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'Invalid vaccine selected for this category.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO vaccination_records (
            patient_id,
            midwife_id,
            vaccine_id,
            patient_name,
            patient_age,
            contact_number,
            address,
            duty_area,
            vaccination_date,
            vaccination_time,
            location,
            dose_number,
            batch_number,
            next_due_date,
            status,
            notes,
            created_at,
            updated_at
        ) VALUES (
            :patient_id,
            :midwife_id,
            :vaccine_id,
            :patient_name,
            :patient_age,
            :contact_number,
            :address,
            :duty_area,
            :vaccination_date,
            :vaccination_time,
            :location,
            :dose_number,
            :batch_number,
            :next_due_date,
            :status,
            :notes,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':patient_id' => null,
        ':midwife_id' => $midwifeId,
        ':vaccine_id' => (int) $vaccine['vaccine_id'],
        ':patient_name' => $patientName,
        ':patient_age' => $patientAge !== '' ? (int) $patientAge : null,
        ':contact_number' => $contactNumber !== '' ? $contactNumber : null,
        ':address' => $address !== '' ? $address : null,
        ':duty_area' => $dutyArea,
        ':vaccination_date' => $vaccinationDate,
        ':vaccination_time' => $vaccinationTime,
        ':location' => $location,
        ':dose_number' => $doseNumber !== '' ? $doseNumber : null,
        ':batch_number' => $vaccine['batch_number'],
        ':next_due_date' => $nextDueDate !== '' ? $nextDueDate : null,
        ':status' => 'scheduled',
        ':notes' => $notes !== '' ? $notes : null
    ]);

    $vaccinationId = $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Vaccination scheduled successfully.',
        'vaccination_id' => $vaccinationId
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}