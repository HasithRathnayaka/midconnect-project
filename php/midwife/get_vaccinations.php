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
    $vaccinationStmt = $pdo->prepare("
        SELECT
            vr.vaccination_id,
            vr.patient_id,
            vr.midwife_id,
            vr.vaccine_id,
            vr.patient_name,
            vr.patient_age,
            vr.contact_number,
            vr.address,
            vr.duty_area,
            vr.vaccination_date,
            vr.vaccination_time,
            vr.location,
            vr.dose_number,
            vr.batch_number,
            vr.next_due_date,
            vr.status,
            vr.notes,
            vr.completed_at,
            vr.created_at,
            vr.updated_at,
            vi.vaccine_name,
            vi.vaccine_code,
            vi.category
        FROM vaccination_records vr
        INNER JOIN vaccine_inventory vi ON vr.vaccine_id = vi.vaccine_id
        WHERE vr.midwife_id = ?
        ORDER BY vr.vaccination_date ASC, vr.vaccination_time ASC
    ");

    $vaccinationStmt->execute([$midwifeId]);
    $vaccinations = $vaccinationStmt->fetchAll(PDO::FETCH_ASSOC);

    $inventoryStmt = $pdo->prepare("
        SELECT
            vaccine_id,
            vaccine_name,
            vaccine_code,
            category,
            stock_quantity,
            minimum_stock_level,
            batch_number,
            expiry_date,
            storage_temperature,
            supplier,
            status
        FROM vaccine_inventory
        ORDER BY vaccine_name ASC
    ");

    $inventoryStmt->execute();
    $inventory = $inventoryStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'vaccinations' => $vaccinations,
        'inventory' => $inventory
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}