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
$distributionDate = trim($_POST['distribution_date'] ?? '');
$beneficiaryName = trim($_POST['beneficiary_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$packets = trim($_POST['packets'] ?? '');
$category = trim($_POST['category'] ?? '');
$status = trim($_POST['status'] ?? 'completed');
$notes = trim($_POST['notes'] ?? '');

if (
    $dutyArea === '' ||
    $distributionDate === '' ||
    $beneficiaryName === '' ||
    $packets === '' ||
    $category === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.',
        'debug' => [
            'duty_area' => $dutyArea,
            'distribution_date' => $distributionDate,
            'beneficiary_name' => $beneficiaryName,
            'packets' => $packets,
            'category' => $category
        ]
    ]);
    exit;
}

$packetsInt = (int) $packets;

if ($packetsInt <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Packets must be greater than 0.'
    ]);
    exit;
}

$allowedCategories = ['pregnant', 'lactating', 'children'];
$allowedStatuses = ['completed', 'pending'];

if (!in_array($category, $allowedCategories, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid beneficiary category.'
    ]);
    exit;
}

if (!in_array($status, $allowedStatuses, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid distribution status.'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO triposha_distribution_records (
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
        ) VALUES (
            :midwife_id,
            :duty_area,
            :distribution_date,
            :beneficiary_name,
            :address,
            :packets,
            :category,
            :status,
            :notes,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':duty_area' => $dutyArea,
        ':distribution_date' => $distributionDate,
        ':beneficiary_name' => $beneficiaryName,
        ':address' => $address !== '' ? $address : null,
        ':packets' => $packetsInt,
        ':category' => $category,
        ':status' => $status,
        ':notes' => $notes !== '' ? $notes : null
    ]);

    $distributionId = $pdo->lastInsertId();
    $inventoryUpdated = false;

    if ($status === 'completed') {
        $inventoryMonth = date('Y-m-01', strtotime($distributionDate));

        $pregnantPackets = $category === 'pregnant' ? $packetsInt : 0;
        $lactatingPackets = $category === 'lactating' ? $packetsInt : 0;
        $childrenPackets = $category === 'children' ? $packetsInt : 0;

        $inventoryStmt = $pdo->prepare("
            INSERT INTO triposha_inventory (
                midwife_id,
                duty_area,
                inventory_month,
                packets_received,
                packets_left_previous,
                packets_distributed,
                pregnant_packets,
                lactating_packets,
                children_packets,
                created_at,
                updated_at
            ) VALUES (
                :midwife_id,
                :duty_area,
                :inventory_month,
                0,
                0,
                :packets_distributed,
                :pregnant_packets,
                :lactating_packets,
                :children_packets,
                NOW(),
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                packets_distributed = packets_distributed + VALUES(packets_distributed),
                pregnant_packets = pregnant_packets + VALUES(pregnant_packets),
                lactating_packets = lactating_packets + VALUES(lactating_packets),
                children_packets = children_packets + VALUES(children_packets),
                updated_at = NOW()
        ");

        $inventoryStmt->execute([
            ':midwife_id' => $midwifeId,
            ':duty_area' => $dutyArea,
            ':inventory_month' => $inventoryMonth,
            ':packets_distributed' => $packetsInt,
            ':pregnant_packets' => $pregnantPackets,
            ':lactating_packets' => $lactatingPackets,
            ':children_packets' => $childrenPackets
        ]);

        $inventoryUpdated = true;
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Triposha distribution recorded successfully.',
        'distribution_id' => $distributionId,
        'inventory_updated' => $inventoryUpdated
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