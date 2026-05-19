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

$adminMohOffice = trim($_SESSION['moh_office'] ?? '');

$mohOfficeToDutyArea = [
    'MOH Uduthuththiripitiya' => 'Uduthuththiripitiya',
    'MOH Kahabilihena'       => 'Kahabilihena',
    'MOH Opathella'          => 'Opathella',
    'MOH Ambalangoda'        => 'Ambalangoda',
    'MOH Colombo 01'         => 'Colombo Central',
    'MOH Galle'              => 'Galle'
];

$dutyArea = $mohOfficeToDutyArea[$adminMohOffice] ?? '';

if ($dutyArea === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to detect duty area from admin MOH office session.'
    ]);
    exit;
}

$inventoryMonthInput = trim($_POST['inventory_month'] ?? '');
$packetsReceived = $_POST['packets_received'] ?? '';
$packetsLeftPrevious = $_POST['packets_left_previous'] ?? '';
$notes = trim($_POST['notes'] ?? '');

$inventoryMonth = $inventoryMonthInput . '-01';

try {
    $stmt = $pdo->prepare("
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
            notes,
            created_at,
            updated_at
        ) VALUES (
            :midwife_id,
            :duty_area,
            :inventory_month,
            :packets_received,
            :packets_left_previous,
            0,
            0,
            0,
            0,
            :notes,
            NOW(),
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            packets_received = VALUES(packets_received),
            packets_left_previous = VALUES(packets_left_previous),
            notes = VALUES(notes),
            updated_at = NOW()
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':duty_area' => $dutyArea,
        ':inventory_month' => $inventoryMonth,
        ':packets_received' => (int) $packetsReceived,
        ':packets_left_previous' => (int) $packetsLeftPrevious,
        ':notes' => $notes !== '' ? $notes : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Triposha inventory saved successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}