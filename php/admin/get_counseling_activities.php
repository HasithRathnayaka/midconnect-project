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
                cs.client_ref LIKE :keyword
                OR cs.focus LIKE :keyword
                OR cs.location_type LIKE :keyword
                OR cs.notes LIKE :keyword
                OR cs.referral_details LIKE :keyword
                OR m.full_name LIKE :keyword
                OR m.employee_id LIKE :keyword
                OR m.assigned_area LIKE :keyword
            )
        ";

        $params[':keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $where .= " AND DATE(cs.session_datetime) >= :date_from";
        $params[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $where .= " AND DATE(cs.session_datetime) <= :date_to";
        $params[':date_to'] = $dateTo;
    }

    if ($status !== '') {
        $where .= " AND LOWER(cs.status) = LOWER(:status)";
        $params[':status'] = $status;
    }

    if ($midwifeId !== '') {
        $where .= " AND cs.midwife_id = :midwife_id";
        $params[':midwife_id'] = $midwifeId;
    }

    $stmt = $pdo->prepare("
        SELECT
            cs.counseling_id AS record_id,
            CONCAT('counseling_', cs.counseling_id) AS record_key,
            cs.midwife_id,
            cs.session_datetime,
            DATE(cs.session_datetime) AS session_date,
            TIME(cs.session_datetime) AS session_time,
            cs.duration_mins,
            cs.client_ref,
            cs.focus,
            cs.location_type,
            cs.notes,
            cs.followup,
            cs.followup_date,
            cs.referral_details,
            cs.status,
            cs.created_at,
            cs.updated_at,

            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office
        FROM counseling_sessions cs
        INNER JOIN midwives m
            ON cs.midwife_id = m.midwife_id
        WHERE {$where}
        ORDER BY cs.session_datetime DESC
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
        'message' => 'Counseling session loading error: ' . $e->getMessage(),
        'records' => [],
        'midwives' => []
    ]);
    exit;
}