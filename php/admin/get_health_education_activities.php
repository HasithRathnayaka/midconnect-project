<?php
session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.',
        'records' => [],
        'midwives' => []
    ]);
    exit;
}

$adminMohOffice = trim($_SESSION['moh_office'] ?? '');

if ($adminMohOffice === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Admin MOH office not found in session.',
        'records' => [],
        'midwives' => []
    ]);
    exit;
}

$keyword = trim($_GET['keyword'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$status = trim($_GET['status'] ?? '');
$midwifeId = trim($_GET['midwife_id'] ?? '');

try {
    $where = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
    ";

    $params = [
        ':moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $where .= "
            AND (
                he.topic LIKE :keyword_1
                OR he.venue LIKE :keyword_2
                OR he.audience LIKE :keyword_3
                OR he.materials LIKE :keyword_4
                OR he.outcomes LIKE :keyword_5
                OR m.full_name LIKE :keyword_6
                OR m.employee_id LIKE :keyword_7
                OR m.assigned_area LIKE :keyword_8
            )
        ";

        $keywordValue = '%' . $keyword . '%';

        $params[':keyword_1'] = $keywordValue;
        $params[':keyword_2'] = $keywordValue;
        $params[':keyword_3'] = $keywordValue;
        $params[':keyword_4'] = $keywordValue;
        $params[':keyword_5'] = $keywordValue;
        $params[':keyword_6'] = $keywordValue;
        $params[':keyword_7'] = $keywordValue;
        $params[':keyword_8'] = $keywordValue;
    }

    if ($dateFrom !== '') {
        $where .= " AND he.session_date >= :date_from";
        $params[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $where .= " AND he.session_date <= :date_to";
        $params[':date_to'] = $dateTo;
    }

    if ($status !== '') {
        $where .= " AND LOWER(TRIM(he.status)) = LOWER(TRIM(:status))";
        $params[':status'] = $status;
    }

    if ($midwifeId !== '') {
        $where .= " AND he.midwife_id = :midwife_id";
        $params[':midwife_id'] = $midwifeId;
    }

    $stmt = $pdo->prepare("
        SELECT
            he.health_education_id AS record_id,
            CONCAT('health_education_', he.health_education_id) AS record_key,
            he.midwife_id,
            he.session_date,
            he.venue,
            he.topic,
            he.audience,
            he.attendees,
            he.duration_mins,
            he.materials,
            he.outcomes,
            he.status,
            he.created_at,
            he.updated_at,

            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office
        FROM health_education_sessions he
        INNER JOIN midwives m
            ON he.midwife_id = m.midwife_id
        WHERE {$where}
        ORDER BY he.session_date DESC, he.created_at DESC
        LIMIT 200
    ");

    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $midwifeStmt = $pdo->prepare("
        SELECT
            midwife_id,
            employee_id,
            full_name,
            assigned_area
        FROM midwives
        WHERE LOWER(TRIM(moh_office)) = LOWER(TRIM(:moh_office))
        ORDER BY full_name ASC
    ");

    $midwifeStmt->execute([
        ':moh_office' => $adminMohOffice
    ]);

    $midwives = $midwifeStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'records' => $records,
        'midwives' => $midwives
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Health education session loading error: ' . $e->getMessage(),
        'records' => [],
        'midwives' => []
    ]);
    exit;
}