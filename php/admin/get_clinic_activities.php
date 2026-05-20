<?php
session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
    ]);
    exit;
}

$adminMohOffice = trim($_SESSION['moh_office'] ?? '');

if ($adminMohOffice === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Admin MOH office not found in session.'
    ]);
    exit;
}

$keyword = trim($_GET['keyword'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$status = trim($_GET['status'] ?? '');
$midwifeId = trim($_GET['midwife_id'] ?? '');

try {
    $records = [];

    /*
     * Completed / logged clinic activities
     */
    $activitySql = "
        SELECT
            'activity' AS record_source,
            a.activity_id AS record_id,
            a.activity_date AS clinic_date,
            a.start_time,
            a.end_time,
            a.patient_name,
            a.patient_age,
            a.location,
            a.description,
            a.observations,
            a.recommendations,
            a.status,
            at.type_name AS activity_type,
            m.midwife_id,
            m.full_name AS midwife_name,
            m.employee_id,
            m.assigned_area,
            m.moh_office
        FROM activities a
        INNER JOIN midwives m ON a.midwife_id = m.midwife_id
        INNER JOIN activity_types at ON a.activity_type_id = at.type_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:activity_moh_office))
          AND LOWER(TRIM(at.type_code)) = 'clinic_visit'
    ";

    $activityParams = [
        ':activity_moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $activitySql .= "
            AND (
                a.patient_name LIKE :activity_keyword_1
                OR a.location LIKE :activity_keyword_2
                OR a.description LIKE :activity_keyword_3
                OR m.full_name LIKE :activity_keyword_4
                OR m.employee_id LIKE :activity_keyword_5
                OR m.assigned_area LIKE :activity_keyword_6
            )
        ";

        $keywordValue = '%' . $keyword . '%';

        $activityParams[':activity_keyword_1'] = $keywordValue;
        $activityParams[':activity_keyword_2'] = $keywordValue;
        $activityParams[':activity_keyword_3'] = $keywordValue;
        $activityParams[':activity_keyword_4'] = $keywordValue;
        $activityParams[':activity_keyword_5'] = $keywordValue;
        $activityParams[':activity_keyword_6'] = $keywordValue;
    }

    if ($dateFrom !== '') {
        $activitySql .= " AND a.activity_date >= :activity_date_from";
        $activityParams[':activity_date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $activitySql .= " AND a.activity_date <= :activity_date_to";
        $activityParams[':activity_date_to'] = $dateTo;
    }

    if ($status !== '') {
        $activitySql .= " AND LOWER(TRIM(a.status)) = LOWER(TRIM(:activity_status))";
        $activityParams[':activity_status'] = $status;
    }

    if ($midwifeId !== '') {
        $activitySql .= " AND a.midwife_id = :activity_midwife_id";
        $activityParams[':activity_midwife_id'] = $midwifeId;
    }

    $activitySql .= " ORDER BY a.activity_date DESC, a.start_time DESC";

    $stmt = $pdo->prepare($activitySql);
    $stmt->execute($activityParams);

    $records = array_merge($records, $stmt->fetchAll(PDO::FETCH_ASSOC));

    /*
     * Scheduled clinic visits
     */
    $scheduleSql = "
        SELECT
            'schedule' AS record_source,
            s.schedule_id AS record_id,
            s.scheduled_date AS clinic_date,
            s.start_time,
            s.estimated_end_time AS end_time,
            s.patient_name,
            NULL AS patient_age,
            s.location,
            s.description,
            NULL AS observations,
            NULL AS recommendations,
            s.status,
            at.type_name AS activity_type,
            m.midwife_id,
            m.full_name AS midwife_name,
            m.employee_id,
            m.assigned_area,
            m.moh_office
        FROM schedules s
        INNER JOIN midwives m ON s.midwife_id = m.midwife_id
        INNER JOIN activity_types at ON s.activity_type_id = at.type_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:schedule_moh_office))
          AND LOWER(TRIM(at.type_code)) = 'clinic_visit'
    ";

    $scheduleParams = [
        ':schedule_moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $scheduleSql .= "
            AND (
                s.patient_name LIKE :schedule_keyword_1
                OR s.location LIKE :schedule_keyword_2
                OR s.description LIKE :schedule_keyword_3
                OR m.full_name LIKE :schedule_keyword_4
                OR m.employee_id LIKE :schedule_keyword_5
                OR m.assigned_area LIKE :schedule_keyword_6
            )
        ";

        $keywordValue = '%' . $keyword . '%';

        $scheduleParams[':schedule_keyword_1'] = $keywordValue;
        $scheduleParams[':schedule_keyword_2'] = $keywordValue;
        $scheduleParams[':schedule_keyword_3'] = $keywordValue;
        $scheduleParams[':schedule_keyword_4'] = $keywordValue;
        $scheduleParams[':schedule_keyword_5'] = $keywordValue;
        $scheduleParams[':schedule_keyword_6'] = $keywordValue;
    }

    if ($dateFrom !== '') {
        $scheduleSql .= " AND s.scheduled_date >= :schedule_date_from";
        $scheduleParams[':schedule_date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $scheduleSql .= " AND s.scheduled_date <= :schedule_date_to";
        $scheduleParams[':schedule_date_to'] = $dateTo;
    }

    if ($status !== '') {
        $scheduleSql .= " AND LOWER(TRIM(s.status)) = LOWER(TRIM(:schedule_status))";
        $scheduleParams[':schedule_status'] = $status;
    }

    if ($midwifeId !== '') {
        $scheduleSql .= " AND s.midwife_id = :schedule_midwife_id";
        $scheduleParams[':schedule_midwife_id'] = $midwifeId;
    }

    $scheduleSql .= " ORDER BY s.scheduled_date DESC, s.start_time DESC";

    $stmt = $pdo->prepare($scheduleSql);
    $stmt->execute($scheduleParams);

    $records = array_merge($records, $stmt->fetchAll(PDO::FETCH_ASSOC));

    usort($records, function ($a, $b) {
        $dateA = ($a['clinic_date'] ?? '') . ' ' . ($a['start_time'] ?? '');
        $dateB = ($b['clinic_date'] ?? '') . ' ' . ($b['start_time'] ?? '');

        return strcmp($dateB, $dateA);
    });

    /*
     * Midwife dropdown list
     */
    $midwifeStmt = $pdo->prepare("
        SELECT
            midwife_id,
            full_name,
            employee_id
        FROM midwives
        WHERE LOWER(TRIM(moh_office)) = LOWER(TRIM(:moh_office))
        ORDER BY full_name ASC
    ");

    $midwifeStmt->execute([
        ':moh_office' => $adminMohOffice
    ]);

    echo json_encode([
        'success' => true,
        'records' => array_slice($records, 0, 100),
        'midwives' => $midwifeStmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Clinic visit loading error: ' . $e->getMessage()
    ]);
    exit;
}