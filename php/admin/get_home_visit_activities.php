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
                hv.patient_name LIKE :keyword
                OR hv.contact_number LIKE :keyword
                OR hv.address LIKE :keyword
                OR hv.duty_area LIKE :keyword
                OR hv.visit_type LIKE :keyword
                OR hv.reason LIKE :keyword
                OR hv.notes LIKE :keyword
                OR m.full_name LIKE :keyword
                OR m.employee_id LIKE :keyword
                OR m.assigned_area LIKE :keyword
            )
        ";

        $params[':keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $where .= " AND hv.visit_date >= :date_from";
        $params[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $where .= " AND hv.visit_date <= :date_to";
        $params[':date_to'] = $dateTo;
    }

    if ($status !== '') {
        $where .= " AND LOWER(hv.status) = LOWER(:status)";
        $params[':status'] = $status;
    }

    if ($midwifeId !== '') {
        $where .= " AND hv.midwife_id = :midwife_id";
        $params[':midwife_id'] = $midwifeId;
    }

    $stmt = $pdo->prepare("
        SELECT
            hv.id AS record_id,
            CONCAT('home_visit_', hv.id) AS record_key,
            hv.midwife_id,
            hv.patient_name,
            hv.contact_number,
            hv.address,
            hv.visit_date,
            hv.start_time,
            hv.end_time,
            hv.duration_minutes,
            hv.duty_area,
            hv.visit_type,
            hv.priority,
            hv.reason,
            hv.status,
            hv.notes,
            hv.completed_at,
            hv.created_at,
            hv.updated_at,

            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office
        FROM home_visits hv
        INNER JOIN midwives m
            ON hv.midwife_id = m.midwife_id
        WHERE {$where}
        ORDER BY hv.visit_date DESC, hv.start_time DESC
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
        'message' => 'Home visit loading error: ' . $e->getMessage(),
        'records' => [],
        'midwives' => []
    ]);
    exit;
}