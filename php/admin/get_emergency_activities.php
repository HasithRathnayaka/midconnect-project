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
    /*
     * Emergency data is collected from existing DB tables:
     * 1. activities table where activity type / description / priority indicates emergency
     * 2. home_visits table where visit_type / reason / priority indicates emergency
     */

    $activityWhere = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:activity_moh_office))
        AND (
            LOWER(at.type_name) LIKE '%emergency%'
            OR LOWER(at.type_code) LIKE '%emergency%'
            OR LOWER(a.description) LIKE '%emergency%'
            OR LOWER(a.observations) LIKE '%emergency%'
            OR LOWER(a.priority_level) IN ('urgent', 'high', 'critical')
        )
    ";

    $activityParams = [
        ':activity_moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $activityWhere .= "
            AND (
                a.patient_name LIKE :activity_keyword
                OR a.patient_contact LIKE :activity_keyword
                OR a.location LIKE :activity_keyword
                OR a.description LIKE :activity_keyword
                OR a.observations LIKE :activity_keyword
                OR a.recommendations LIKE :activity_keyword
                OR at.type_name LIKE :activity_keyword
                OR m.full_name LIKE :activity_keyword
                OR m.employee_id LIKE :activity_keyword
                OR m.assigned_area LIKE :activity_keyword
            )
        ";
        $activityParams[':activity_keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $activityWhere .= " AND a.activity_date >= :activity_date_from";
        $activityParams[':activity_date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $activityWhere .= " AND a.activity_date <= :activity_date_to";
        $activityParams[':activity_date_to'] = $dateTo;
    }

    if ($status !== '') {
        $activityWhere .= " AND LOWER(a.status) = LOWER(:activity_status)";
        $activityParams[':activity_status'] = $status;
    }

    if ($midwifeId !== '') {
        $activityWhere .= " AND a.midwife_id = :activity_midwife_id";
        $activityParams[':activity_midwife_id'] = $midwifeId;
    }

    $activitySql = "
        SELECT
            CONCAT('activity_', a.activity_id) AS record_key,
            'activity' AS record_source,
            a.activity_id AS record_id,
            a.activity_date AS emergency_date,
            a.start_time,
            a.end_time,
            a.patient_name,
            a.patient_age,
            a.patient_contact,
            a.location,
            a.description,
            a.observations,
            a.recommendations,
            a.priority_level AS priority,
            a.status,
            a.created_at,
            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,
            at.type_name AS emergency_type
        FROM activities a
        INNER JOIN midwives m
            ON a.midwife_id = m.midwife_id
        INNER JOIN activity_types at
            ON a.activity_type_id = at.type_id
        WHERE {$activityWhere}
    ";

    $activityStmt = $pdo->prepare($activitySql);
    $activityStmt->execute($activityParams);
    $activityRecords = $activityStmt->fetchAll(PDO::FETCH_ASSOC);


    $homeVisitWhere = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:hv_moh_office))
        AND (
            LOWER(hv.visit_type) LIKE '%emergency%'
            OR LOWER(hv.reason) LIKE '%emergency%'
            OR LOWER(hv.notes) LIKE '%emergency%'
            OR LOWER(hv.priority) IN ('urgent', 'high', 'critical')
        )
    ";

    $homeVisitParams = [
        ':hv_moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $homeVisitWhere .= "
            AND (
                hv.patient_name LIKE :hv_keyword
                OR hv.contact_number LIKE :hv_keyword
                OR hv.address LIKE :hv_keyword
                OR hv.duty_area LIKE :hv_keyword
                OR hv.visit_type LIKE :hv_keyword
                OR hv.reason LIKE :hv_keyword
                OR hv.notes LIKE :hv_keyword
                OR m.full_name LIKE :hv_keyword
                OR m.employee_id LIKE :hv_keyword
                OR m.assigned_area LIKE :hv_keyword
            )
        ";
        $homeVisitParams[':hv_keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $homeVisitWhere .= " AND hv.visit_date >= :hv_date_from";
        $homeVisitParams[':hv_date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $homeVisitWhere .= " AND hv.visit_date <= :hv_date_to";
        $homeVisitParams[':hv_date_to'] = $dateTo;
    }

    if ($status !== '') {
        $homeVisitWhere .= " AND LOWER(hv.status) = LOWER(:hv_status)";
        $homeVisitParams[':hv_status'] = $status;
    }

    if ($midwifeId !== '') {
        $homeVisitWhere .= " AND hv.midwife_id = :hv_midwife_id";
        $homeVisitParams[':hv_midwife_id'] = $midwifeId;
    }

    $homeVisitSql = "
        SELECT
            CONCAT('home_visit_', hv.id) AS record_key,
            'home_visit' AS record_source,
            hv.id AS record_id,
            hv.visit_date AS emergency_date,
            hv.start_time,
            hv.end_time,
            hv.patient_name,
            NULL AS patient_age,
            hv.contact_number AS patient_contact,
            hv.address AS location,
            hv.reason AS description,
            hv.notes AS observations,
            NULL AS recommendations,
            hv.priority AS priority,
            hv.status,
            hv.created_at,
            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,
            hv.visit_type AS emergency_type
        FROM home_visits hv
        INNER JOIN midwives m
            ON hv.midwife_id = m.midwife_id
        WHERE {$homeVisitWhere}
    ";

    $homeVisitStmt = $pdo->prepare($homeVisitSql);
    $homeVisitStmt->execute($homeVisitParams);
    $homeVisitRecords = $homeVisitStmt->fetchAll(PDO::FETCH_ASSOC);

    $records = array_merge($activityRecords, $homeVisitRecords);

    usort($records, function ($a, $b) {
        $aDateTime = ($a['emergency_date'] ?? '') . ' ' . ($a['start_time'] ?? '');
        $bDateTime = ($b['emergency_date'] ?? '') . ' ' . ($b['start_time'] ?? '');

        return strcmp($bDateTime, $aDateTime);
    });

    $records = array_slice($records, 0, 200);

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
        'message' => 'Emergency response loading error: ' . $e->getMessage(),
        'records' => [],
        'midwives' => []
    ]);
    exit;
}