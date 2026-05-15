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
     * Get all meeting-related activity type IDs directly from activity_types.
     * This is better than only checking description/location text.
     */
    $typeStmt = $pdo->prepare("
        SELECT type_id
        FROM activity_types
        WHERE LOWER(TRIM(type_code)) = 'meeting'
           OR LOWER(TRIM(type_name)) = 'meeting'
           OR LOWER(TRIM(type_code)) LIKE '%meeting%'
           OR LOWER(TRIM(type_name)) LIKE '%meeting%'
    ");

    $typeStmt->execute();
    $meetingTypeIds = $typeStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$meetingTypeIds || count($meetingTypeIds) === 0) {
        echo json_encode([
            'success' => true,
            'records' => [],
            'midwives' => [],
            'message' => 'No meeting activity type found in activity_types table.'
        ]);
        exit;
    }

    $typePlaceholders = [];
    $typeParams = [];

    foreach ($meetingTypeIds as $index => $typeId) {
        $key = ':type_id_' . $index;
        $typePlaceholders[] = $key;
        $typeParams[$key] = $typeId;
    }

    $typeInSql = implode(',', $typePlaceholders);

    /*
     * ACTIVITIES table meeting records
     */
    $activityWhere = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:activity_moh_office))
        AND a.activity_type_id IN ($typeInSql)
    ";

    $activityParams = array_merge([
        ':activity_moh_office' => $adminMohOffice
    ], $typeParams);

    if ($keyword !== '') {
        $activityWhere .= "
            AND (
                a.patient_name LIKE :activity_keyword
                OR a.location LIKE :activity_keyword
                OR a.description LIKE :activity_keyword
                OR a.observations LIKE :activity_keyword
                OR a.recommendations LIKE :activity_keyword
                OR at.type_name LIKE :activity_keyword
                OR at.type_code LIKE :activity_keyword
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
            a.activity_date AS meeting_date,
            a.start_time,
            a.end_time,
            a.patient_name,
            a.patient_age,
            a.patient_contact,
            a.location,
            a.description,
            a.observations,
            a.recommendations,
            a.priority_level,
            a.status,
            a.created_at,

            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,

            at.type_code,
            at.type_name AS meeting_topic
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


    /*
     * SCHEDULES table meeting records
     */
    $scheduleWhere = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:schedule_moh_office))
        AND s.activity_type_id IN ($typeInSql)
    ";

    $scheduleParams = array_merge([
        ':schedule_moh_office' => $adminMohOffice
    ], $typeParams);

    if ($keyword !== '') {
        $scheduleWhere .= "
            AND (
                s.patient_name LIKE :schedule_keyword
                OR s.location LIKE :schedule_keyword
                OR s.description LIKE :schedule_keyword
                OR at.type_name LIKE :schedule_keyword
                OR at.type_code LIKE :schedule_keyword
                OR m.full_name LIKE :schedule_keyword
                OR m.employee_id LIKE :schedule_keyword
                OR m.assigned_area LIKE :schedule_keyword
            )
        ";

        $scheduleParams[':schedule_keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $scheduleWhere .= " AND s.scheduled_date >= :schedule_date_from";
        $scheduleParams[':schedule_date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $scheduleWhere .= " AND s.scheduled_date <= :schedule_date_to";
        $scheduleParams[':schedule_date_to'] = $dateTo;
    }

    if ($status !== '') {
        $scheduleWhere .= " AND LOWER(s.status) = LOWER(:schedule_status)";
        $scheduleParams[':schedule_status'] = $status;
    }

    if ($midwifeId !== '') {
        $scheduleWhere .= " AND s.midwife_id = :schedule_midwife_id";
        $scheduleParams[':schedule_midwife_id'] = $midwifeId;
    }

    $scheduleSql = "
        SELECT
            CONCAT('schedule_', s.schedule_id) AS record_key,
            'schedule' AS record_source,
            s.schedule_id AS record_id,
            s.scheduled_date AS meeting_date,
            s.start_time,
            s.estimated_end_time AS end_time,
            s.patient_name,
            NULL AS patient_age,
            NULL AS patient_contact,
            s.location,
            s.description,
            NULL AS observations,
            NULL AS recommendations,
            s.priority_level,
            s.status,
            s.created_at,

            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,

            at.type_code,
            at.type_name AS meeting_topic
        FROM schedules s
        INNER JOIN midwives m
            ON s.midwife_id = m.midwife_id
        INNER JOIN activity_types at
            ON s.activity_type_id = at.type_id
        WHERE {$scheduleWhere}
    ";

    $scheduleStmt = $pdo->prepare($scheduleSql);
    $scheduleStmt->execute($scheduleParams);
    $scheduleRecords = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);

    $records = array_merge($activityRecords, $scheduleRecords);

    usort($records, function ($a, $b) {
        $aDateTime = ($a['meeting_date'] ?? '') . ' ' . ($a['start_time'] ?? '');
        $bDateTime = ($b['meeting_date'] ?? '') . ' ' . ($b['start_time'] ?? '');

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
        'midwives' => $midwives,
        'debug' => [
            'admin_moh_office' => $adminMohOffice,
            'meeting_type_ids' => $meetingTypeIds,
            'activity_count' => count($activityRecords),
            'schedule_count' => count($scheduleRecords)
        ]
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Meeting loading error: ' . $e->getMessage(),
        'records' => [],
        'midwives' => []
    ]);
    exit;
}