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
$visitId = $_POST['visit_id'] ?? null;

if (!$midwifeId || !$visitId) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid visit request.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE home_visits
        SET 
            status = 'completed',
            completed_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
        AND midwife_id = ?
    ");

    $stmt->execute([$visitId, $midwifeId]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Visit not found or already completed.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Home visit completed successfully.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}