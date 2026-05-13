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

$vaccineId = $_POST['vaccine_id'] ?? null;
$stockQuantity = $_POST['stock_quantity'] ?? null;
$minimumStockLevel = $_POST['minimum_stock_level'] ?? null;
$batchNumber = trim($_POST['batch_number'] ?? '');
$expiryDate = trim($_POST['expiry_date'] ?? '');
$status = trim($_POST['status'] ?? 'available');

if (
    !$vaccineId ||
    $stockQuantity === null ||
    $stockQuantity === '' ||
    $minimumStockLevel === null ||
    $minimumStockLevel === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

$allowedStatuses = ['available', 'low_stock', 'expired', 'unavailable'];

if (!in_array($status, $allowedStatuses, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid inventory status.'
    ]);
    exit;
}

try {
    $checkStmt = $pdo->prepare("
        SELECT vaccine_id
        FROM vaccine_inventory
        WHERE vaccine_id = ?
        LIMIT 1
    ");
    $checkStmt->execute([$vaccineId]);
    $vaccine = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$vaccine) {
        echo json_encode([
            'success' => false,
            'message' => 'Selected vaccine not found.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE vaccine_inventory
        SET
            stock_quantity = :stock_quantity,
            minimum_stock_level = :minimum_stock_level,
            batch_number = :batch_number,
            expiry_date = :expiry_date,
            status = :status,
            updated_at = NOW()
        WHERE vaccine_id = :vaccine_id
    ");

    $stmt->execute([
        ':stock_quantity' => (int) $stockQuantity,
        ':minimum_stock_level' => (int) $minimumStockLevel,
        ':batch_number' => $batchNumber !== '' ? $batchNumber : null,
        ':expiry_date' => $expiryDate !== '' ? $expiryDate : null,
        ':status' => $status,
        ':vaccine_id' => (int) $vaccineId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Vaccine inventory updated successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}