<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

try {
    /*
     * Load unique MOH offices from moh_admins table.
     * TRIM() avoids duplicates caused by extra spaces.
     * GROUP BY normalized value avoids duplicate office names.
     */
    $stmt = $pdo->prepare("
        SELECT MIN(TRIM(moh_office)) AS moh_office
        FROM moh_admins
        WHERE is_active = 1
          AND moh_office IS NOT NULL
          AND TRIM(moh_office) != ''
        GROUP BY LOWER(TRIM(moh_office))
        ORDER BY moh_office ASC
    ");

    $stmt->execute();

    $offices = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'offices' => $offices
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'offices' => []
    ]);
    exit;
}