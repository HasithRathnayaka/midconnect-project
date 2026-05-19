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
$distributionId = $_POST['distribution_id'] ?? null;

if (!$midwifeId || !$distributionId) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $selectStmt = $pdo->prepare("
        SELECT *
        FROM triposha_distribution_records
        WHERE distribution_id = ?
        AND midwife_id = ?
        LIMIT 1
    ");
    $selectStmt->execute([$distributionId, $midwifeId]);
    $record = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'Distribution record not found.'
        ]);
        exit;
    }

    if ($record['status'] === 'completed') {
        $inventoryMonth = date('Y-m-01', strtotime($record['distribution_date']));

        $columnMap = [
            'pregnant' => 'pregnant_packets',
            'lactating' => 'lactating_packets',
            'children' => 'children_packets'
        ];

        $categoryColumn = $columnMap[$record['category']] ?? null;

        if ($categoryColumn) {
            $updateStmt = $pdo->prepare("
                UPDATE triposha_inventory
                SET
                    packets_distributed = GREATEST(packets_distributed - :packets, 0),
                    $categoryColumn = GREATEST($categoryColumn - :category_packets, 0),
                    updated_at = NOW()
                WHERE midwife_id = :midwife_id
                AND duty_area = :duty_area
                AND inventory_month = :inventory_month
            ");

            $updateStmt->execute([
                ':packets' => (int) $record['packets'],
                ':category_packets' => (int) $record['packets'],
                ':midwife_id' => $midwifeId,
                ':duty_area' => $record['duty_area'],
                ':inventory_month' => $inventoryMonth
            ]);
        }
    }

    $deleteStmt = $pdo->prepare("
        DELETE FROM triposha_distribution_records
        WHERE distribution_id = ?
        AND midwife_id = ?
    ");
    $deleteStmt->execute([$distributionId, $midwifeId]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Distribution record removed successfully.'
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