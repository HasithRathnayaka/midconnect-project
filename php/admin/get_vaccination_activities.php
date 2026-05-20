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
                vr.patient_name LIKE :keyword_1
                OR vr.mother_name LIKE :keyword_2
                OR CAST(vr.patient_age AS CHAR) LIKE :keyword_3
                OR vr.contact_number LIKE :keyword_4
                OR vr.address LIKE :keyword_5
                OR vr.location LIKE :keyword_6
                OR vr.dose_number LIKE :keyword_7
                OR vr.batch_number LIKE :keyword_8
                OR vr.notes LIKE :keyword_9
                OR vi.vaccine_name LIKE :keyword_10
                OR vi.vaccine_code LIKE :keyword_11
                OR m.full_name LIKE :keyword_12
                OR m.employee_id LIKE :keyword_13
                OR m.assigned_area LIKE :keyword_14
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
        $params[':keyword_9'] = $keywordValue;
        $params[':keyword_10'] = $keywordValue;
        $params[':keyword_11'] = $keywordValue;
        $params[':keyword_12'] = $keywordValue;
        $params[':keyword_13'] = $keywordValue;
        $params[':keyword_14'] = $keywordValue;
    }

    if ($dateFrom !== '') {
        $where .= " AND vr.vaccination_date >= :date_from";
        $params[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $where .= " AND vr.vaccination_date <= :date_to";
        $params[':date_to'] = $dateTo;
    }

    if ($status !== '') {
        $where .= " AND LOWER(TRIM(vr.status)) = LOWER(TRIM(:status))";
        $params[':status'] = $status;
    }

    if ($midwifeId !== '') {
        $where .= " AND m.midwife_id = :midwife_id";
        $params[':midwife_id'] = $midwifeId;
    }

    $stmt = $pdo->prepare("
        SELECT
            vr.vaccination_id,
            vr.midwife_id,
            vr.duty_area,
            vr.patient_name,
            vr.patient_age,
            vr.mother_name,
            vr.pregnancy_status,
            vr.condition_note,
            vr.contact_number,
            vr.address,
            vr.vaccination_date,
            vr.vaccination_time,
            vr.duration_minutes,
            vr.location,
            vr.dose_number,
            vr.batch_number,
            vr.next_due_date,
            vr.priority,
            vr.status,
            vr.notes,
            vr.administered_at,
            vr.completed_at,
            vr.created_at,
            vr.updated_at,

            vi.vaccine_id,
            vi.vaccine_name,
            vi.vaccine_code,
            vi.category AS vaccine_category,

            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office
        FROM vaccination_records vr
        INNER JOIN midwives m
            ON vr.midwife_id = m.midwife_id
        INNER JOIN vaccine_inventory vi
            ON vr.vaccine_id = vi.vaccine_id
        WHERE {$where}
        ORDER BY vr.vaccination_date DESC, vr.vaccination_time DESC
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
        'message' => 'Vaccination loading error: ' . $e->getMessage(),
        'records' => [],
        'midwives' => []
    ]);
    exit;
}