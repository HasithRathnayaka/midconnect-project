<?php
session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized request.'
    ]);
    exit;
}

$midwifeId = (int)($_POST['midwife_id'] ?? 0);

if ($midwifeId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Valid midwife ID is required.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE midwives
        SET 
            status = 'inactive',
            updated_at = NOW()
        WHERE midwife_id = :midwife_id
        LIMIT 1
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId
    ]);

    if ($stmt->rowCount() < 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Midwife not found or already inactive.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Midwife deactivated successfully.'
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to deactivate midwife: ' . $e->getMessage()
    ]);
    exit;
}