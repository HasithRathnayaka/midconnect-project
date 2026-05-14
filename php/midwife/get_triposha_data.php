<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

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

try {
    $month = date('Y-m-01');

    $inventoryStmt = $pdo->prepare("
        SELECT
            inventory_id,
            midwife_id,
            duty_area,
            inventory_month,
            packets_received,
            packets_left_previous,
            packets_distributed,
            pregnant_packets,
            lactating_packets,
            children_packets,
            notes,
            created_at,
            updated_at
        FROM triposha_inventory
        WHERE midwife_id = ?
        AND inventory_month = ?
        ORDER BY duty_area ASC
    ");
    $inventoryStmt->execute([$midwifeId, $month]);
    $inventory = $inventoryStmt->fetchAll(PDO::FETCH_ASSOC);

    $distributionStmt = $pdo->prepare("
        SELECT
            distribution_id,
            midwife_id,
            duty_area,
            distribution_date,
            beneficiary_name,
            address,
            packets,
            category,
            status,
            notes,
            created_at,
            updated_at
        FROM triposha_distributions
        WHERE midwife_id = ?
        ORDER BY distribution_date DESC, distribution_id DESC
    ");
    $distributionStmt->execute([$midwifeId]);
    $distributions = $distributionStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'inventory' => $inventory,
        'distributions' => $distributions
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}