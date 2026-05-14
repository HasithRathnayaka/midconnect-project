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
$vaccinationId = $_POST['vaccination_id'] ?? null;

if (!$midwifeId || !$vaccinationId) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid vaccination request.'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $findStmt = $pdo->prepare("
        SELECT vaccination_id, vaccine_id, status
        FROM vaccination_records
        WHERE vaccination_id = ?
        AND midwife_id = ?
        LIMIT 1
    ");

    $findStmt->execute([$vaccinationId, $midwifeId]);
    $record = $findStmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'Vaccination record not found.'
        ]);
        exit;
    }

    if ($record['status'] === 'completed') {
        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'This vaccination is already completed.'
        ]);
        exit;
    }

    $stockStmt = $pdo->prepare("
        SELECT stock_quantity
        FROM vaccine_inventory
        WHERE vaccine_id = ?
        LIMIT 1
    ");

    $stockStmt->execute([$record['vaccine_id']]);
    $stock = $stockStmt->fetch(PDO::FETCH_ASSOC);

    if (!$stock || (int)$stock['stock_quantity'] <= 0) {
        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'Cannot administer. Vaccine stock is empty.'
        ]);
        exit;
    }

    $updateRecordStmt = $pdo->prepare("
        UPDATE vaccination_records
        SET status = 'completed',
            completed_at = NOW(),
            updated_at = NOW()
        WHERE vaccination_id = ?
        AND midwife_id = ?
    ");

    $updateRecordStmt->execute([$vaccinationId, $midwifeId]);

    $updateStockStmt = $pdo->prepare("
        UPDATE vaccine_inventory
        SET stock_quantity = stock_quantity - 1,
            updated_at = NOW()
        WHERE vaccine_id = ?
        AND stock_quantity > 0
    ");

    $updateStockStmt->execute([$record['vaccine_id']]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Vaccination administered successfully.'
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