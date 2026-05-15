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
     * 1. Activities table
     * Used for completed/recorded clinic-related activities.
     */
    $activityWhere = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        AND (
            LOWER(at.type_name) LIKE '%clinic%'
            OR LOWER(at.type_code) LIKE '%clinic%'
            OR LOWER(at.type_name) LIKE '%maternal%'
            OR LOWER(at.type_name) LIKE '%child%'
            OR LOWER(at.type_name) LIKE '%antenatal%'
            OR LOWER(at.type_name) LIKE '%postnatal%'
            OR LOWER(at.type_name) LIKE '%vaccination%'
            OR LOWER(a.location) LIKE '%clinic%'
            OR LOWER(a.description) LIKE '%clinic%'
        )
    ";

    $activityParams = [
        ':moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $activityWhere .= "
            AND (
                a.patient_name LIKE :keyword
                OR a.location LIKE :keyword
                OR a.description LIKE :keyword
                OR m.full_name LIKE :keyword
                OR m.assigned_area LIKE :keyword
                OR at.type_name LIKE :keyword
            )
        ";
        $activityParams[':keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $activityWhere .= " AND a.activity_date >= :date_from";
        $activityParams[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $activityWhere .= " AND a.activity_date <= :date_to";
        $activityParams[':date_to'] = $dateTo;
    }

    if ($status !== '') {
        $activityWhere .= " AND LOWER(a.status) = LOWER(:status)";
        $activityParams[':status'] = $status;
    }

    if ($midwifeId !== '') {
        $activityWhere .= " AND m.midwife_id = :midwife_id";
        $activityParams[':midwife_id'] = $midwifeId;
    }

    $activityStmt = $pdo->prepare("
        SELECT
            CONCAT('activity_', a.activity_id) AS record_key,
            'Activity Record' AS record_source,
            a.activity_id AS record_id,
            a.activity_date AS clinic_date,
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
            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,
            at.type_name AS activity_type
        FROM activities a
        INNER JOIN midwives m ON a.midwife_id = m.midwife_id
        INNER JOIN activity_types at ON a.activity_type_id = at.type_id
        WHERE {$activityWhere}
    ");

    $activityStmt->execute($activityParams);
    $records = array_merge($records, $activityStmt->fetchAll(PDO::FETCH_ASSOC));


    /*
     * 2. Schedules table
     * Used for planned/upcoming clinic visits.
     */
    $scheduleWhere = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        AND (
            LOWER(at.type_name) LIKE '%clinic%'
            OR LOWER(at.type_code) LIKE '%clinic%'
            OR LOWER(at.type_name) LIKE '%maternal%'
            OR LOWER(at.type_name) LIKE '%child%'
            OR LOWER(at.type_name) LIKE '%antenatal%'
            OR LOWER(at.type_name) LIKE '%postnatal%'
            OR LOWER(at.type_name) LIKE '%vaccination%'
            OR LOWER(s.location) LIKE '%clinic%'
            OR LOWER(s.description) LIKE '%clinic%'
        )
    ";

    $scheduleParams = [
        ':moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $scheduleWhere .= "
            AND (
                s.patient_name LIKE :keyword
                OR s.location LIKE :keyword
                OR s.description LIKE :keyword
                OR m.full_name LIKE :keyword
                OR m.assigned_area LIKE :keyword
                OR at.type_name LIKE :keyword
            )
        ";
        $scheduleParams[':keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $scheduleWhere .= " AND s.scheduled_date >= :date_from";
        $scheduleParams[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $scheduleWhere .= " AND s.scheduled_date <= :date_to";
        $scheduleParams[':date_to'] = $dateTo;
    }

    if ($status !== '') {
        $scheduleWhere .= " AND LOWER(s.status) = LOWER(:status)";
        $scheduleParams[':status'] = $status;
    }

    if ($midwifeId !== '') {
        $scheduleWhere .= " AND m.midwife_id = :midwife_id";
        $scheduleParams[':midwife_id'] = $midwifeId;
    }

    $scheduleStmt = $pdo->prepare("
        SELECT
            CONCAT('schedule_', s.schedule_id) AS record_key,
            'Scheduled Activity' AS record_source,
            s.schedule_id AS record_id,
            s.scheduled_date AS clinic_date,
            s.start_time,
            s.estimated_end_time AS end_time,
            s.patient_name,
            NULL AS patient_age,
            NULL AS patient_contact,
            s.location,
            s.description,
            NULL AS observations,
            s.notes AS recommendations,
            s.priority_level,
            s.status,
            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,
            at.type_name AS activity_type
        FROM schedules s
        INNER JOIN midwives m ON s.midwife_id = m.midwife_id
        INNER JOIN activity_types at ON s.activity_type_id = at.type_id
        WHERE {$scheduleWhere}
    ");

    $scheduleStmt->execute($scheduleParams);
    $records = array_merge($records, $scheduleStmt->fetchAll(PDO::FETCH_ASSOC));


    /*
     * 3. Vaccination records
     * Relevant because many clinic visits are vaccination-clinic activities.
     */
    $vaccinationWhere = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
    ";

    $vaccinationParams = [
        ':moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $vaccinationWhere .= "
            AND (
                vr.patient_name LIKE :keyword
                OR vr.mother_name LIKE :keyword
                OR vr.location LIKE :keyword
                OR vr.address LIKE :keyword
                OR m.full_name LIKE :keyword
                OR vi.vaccine_name LIKE :keyword
            )
        ";
        $vaccinationParams[':keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $vaccinationWhere .= " AND vr.vaccination_date >= :date_from";
        $vaccinationParams[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $vaccinationWhere .= " AND vr.vaccination_date <= :date_to";
        $vaccinationParams[':date_to'] = $dateTo;
    }

    if ($status !== '') {
        $vaccinationWhere .= " AND LOWER(vr.status) = LOWER(:status)";
        $vaccinationParams[':status'] = $status;
    }

    if ($midwifeId !== '') {
        $vaccinationWhere .= " AND m.midwife_id = :midwife_id";
        $vaccinationParams[':midwife_id'] = $midwifeId;
    }

    $vaccinationStmt = $pdo->prepare("
        SELECT
            CONCAT('vaccination_', vr.vaccination_id) AS record_key,
            'Vaccination Record' AS record_source,
            vr.vaccination_id AS record_id,
            vr.vaccination_date AS clinic_date,
            vr.vaccination_time AS start_time,
            NULL AS end_time,
            vr.patient_name,
            vr.patient_age,
            vr.contact_number AS patient_contact,
            vr.location,
            CONCAT('Vaccination: ', vi.vaccine_name, ' | Dose: ', COALESCE(vr.dose_number, '-')) AS description,
            NULL AS observations,
            vr.notes AS recommendations,
            vr.priority AS priority_level,
            vr.status,
            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,
            'Vaccination Clinic' AS activity_type
        FROM vaccination_records vr
        INNER JOIN midwives m ON vr.midwife_id = m.midwife_id
        INNER JOIN vaccine_inventory vi ON vr.vaccine_id = vi.vaccine_id
        WHERE {$vaccinationWhere}
    ");

    $vaccinationStmt->execute($vaccinationParams);
    $records = array_merge($records, $vaccinationStmt->fetchAll(PDO::FETCH_ASSOC));


    /*
     * 4. Counseling sessions
     * Only include clinic-location counseling.
     */
    $counselingWhere = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        AND LOWER(cs.location_type) = 'clinic'
    ";

    $counselingParams = [
        ':moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $counselingWhere .= "
            AND (
                cs.client_ref LIKE :keyword
                OR cs.focus LIKE :keyword
                OR cs.notes LIKE :keyword
                OR m.full_name LIKE :keyword
            )
        ";
        $counselingParams[':keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $counselingWhere .= " AND DATE(cs.session_datetime) >= :date_from";
        $counselingParams[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $counselingWhere .= " AND DATE(cs.session_datetime) <= :date_to";
        $counselingParams[':date_to'] = $dateTo;
    }

    if ($status !== '') {
        $counselingWhere .= " AND LOWER(cs.status) = LOWER(:status)";
        $counselingParams[':status'] = $status;
    }

    if ($midwifeId !== '') {
        $counselingWhere .= " AND m.midwife_id = :midwife_id";
        $counselingParams[':midwife_id'] = $midwifeId;
    }

    $counselingStmt = $pdo->prepare("
        SELECT
            CONCAT('counseling_', cs.counseling_id) AS record_key,
            'Counseling Session' AS record_source,
            cs.counseling_id AS record_id,
            DATE(cs.session_datetime) AS clinic_date,
            TIME(cs.session_datetime) AS start_time,
            NULL AS end_time,
            cs.client_ref AS patient_name,
            NULL AS patient_age,
            NULL AS patient_contact,
            cs.location_type AS location,
            CONCAT('Counseling focus: ', cs.focus) AS description,
            cs.notes AS observations,
            cs.referral_details AS recommendations,
            'normal' AS priority_level,
            cs.status,
            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,
            'Clinic Counseling' AS activity_type
        FROM counseling_sessions cs
        INNER JOIN midwives m ON cs.midwife_id = m.midwife_id
        WHERE {$counselingWhere}
    ");

    $counselingStmt->execute($counselingParams);
    $records = array_merge($records, $counselingStmt->fetchAll(PDO::FETCH_ASSOC));


    /*
     * 5. Health education sessions
     * Include sessions held in clinic venues.
     */
    $educationWhere = "
        LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        AND LOWER(hes.venue) LIKE '%clinic%'
    ";

    $educationParams = [
        ':moh_office' => $adminMohOffice
    ];

    if ($keyword !== '') {
        $educationWhere .= "
            AND (
                hes.venue LIKE :keyword
                OR hes.topic LIKE :keyword
                OR hes.audience LIKE :keyword
                OR hes.outcomes LIKE :keyword
                OR m.full_name LIKE :keyword
            )
        ";
        $educationParams[':keyword'] = '%' . $keyword . '%';
    }

    if ($dateFrom !== '') {
        $educationWhere .= " AND hes.session_date >= :date_from";
        $educationParams[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $educationWhere .= " AND hes.session_date <= :date_to";
        $educationParams[':date_to'] = $dateTo;
    }

    if ($status !== '') {
        $educationWhere .= " AND LOWER(hes.status) = LOWER(:status)";
        $educationParams[':status'] = $status;
    }

    if ($midwifeId !== '') {
        $educationWhere .= " AND m.midwife_id = :midwife_id";
        $educationParams[':midwife_id'] = $midwifeId;
    }

    $educationStmt = $pdo->prepare("
        SELECT
            CONCAT('education_', hes.health_education_id) AS record_key,
            'Health Education Session' AS record_source,
            hes.health_education_id AS record_id,
            hes.session_date AS clinic_date,
            NULL AS start_time,
            NULL AS end_time,
            hes.audience AS patient_name,
            NULL AS patient_age,
            NULL AS patient_contact,
            hes.venue AS location,
            CONCAT('Health education topic: ', hes.topic, ' | Attendees: ', hes.attendees) AS description,
            hes.outcomes AS observations,
            hes.materials AS recommendations,
            'normal' AS priority_level,
            hes.status,
            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,
            'Clinic Health Education' AS activity_type
        FROM health_education_sessions hes
        INNER JOIN midwives m ON hes.midwife_id = m.midwife_id
        WHERE {$educationWhere}
    ");

    $educationStmt->execute($educationParams);
    $records = array_merge($records, $educationStmt->fetchAll(PDO::FETCH_ASSOC));


    /*
     * Sort latest first.
     */
    usort($records, function ($a, $b) {
        $aDateTime = ($a['clinic_date'] ?? '') . ' ' . ($a['start_time'] ?? '');
        $bDateTime = ($b['clinic_date'] ?? '') . ' ' . ($b['start_time'] ?? '');

        return strcmp($bDateTime, $aDateTime);
    });

    $records = array_slice($records, 0, 150);


    /*
     * Midwife dropdown.
     */
    $midwifeStmt = $pdo->prepare("
        SELECT
            midwife_id,
            full_name,
            employee_id,
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
        'message' => 'Clinic visit loading error: ' . $e->getMessage(),
        'records' => [],
        'midwives' => []
    ]);
    exit;
}