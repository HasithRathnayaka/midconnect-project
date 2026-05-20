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

$area = trim($_GET['area'] ?? '');

if ($area === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Area is required.'
    ]);
    exit;
}

try {
    $records = [];

    /*
     * 1. Logged activities
     */
    $stmt = $pdo->prepare("
        SELECT
            CONCAT('activity_', a.activity_id) AS record_key,
            a.activity_id AS record_id,
            a.activity_date,
            a.start_time,
            a.location,
            a.status,
            a.patient_name,
            a.description,
            at.type_name,
            m.full_name AS midwife_name,
            m.assigned_area
        FROM activities a
        INNER JOIN midwives m ON a.midwife_id = m.midwife_id
        INNER JOIN activity_types at ON a.activity_type_id = at.type_id
        WHERE LOWER(TRIM(m.assigned_area)) = LOWER(TRIM(:area))
        ORDER BY a.activity_date DESC, a.start_time DESC
        LIMIT 20
    ");

    $stmt->execute([':area' => $area]);
    $records = array_merge($records, $stmt->fetchAll(PDO::FETCH_ASSOC));

    /*
     * 2. Schedules
     */
    $stmt = $pdo->prepare("
        SELECT
            CONCAT('schedule_', s.schedule_id) AS record_key,
            s.schedule_id AS record_id,
            s.scheduled_date AS activity_date,
            s.start_time,
            s.location,
            s.status,
            s.patient_name,
            s.description,
            at.type_name,
            m.full_name AS midwife_name,
            m.assigned_area
        FROM schedules s
        INNER JOIN midwives m ON s.midwife_id = m.midwife_id
        INNER JOIN activity_types at ON s.activity_type_id = at.type_id
        WHERE LOWER(TRIM(m.assigned_area)) = LOWER(TRIM(:area))
        ORDER BY s.scheduled_date DESC, s.start_time DESC
        LIMIT 20
    ");

    $stmt->execute([':area' => $area]);
    $records = array_merge($records, $stmt->fetchAll(PDO::FETCH_ASSOC));

    /*
     * 3. Home visits
     * Your home_visits primary key is id, not visit_id.
     */
    $stmt = $pdo->prepare("
        SELECT
            CONCAT('home_visit_', hv.id) AS record_key,
            hv.id AS record_id,
            hv.visit_date AS activity_date,
            hv.start_time,
            hv.address AS location,
            hv.status,
            hv.patient_name,
            hv.reason AS description,
            'Home Visit' AS type_name,
            m.full_name AS midwife_name,
            hv.duty_area AS assigned_area
        FROM home_visits hv
        INNER JOIN midwives m ON hv.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(hv.duty_area)) = LOWER(TRIM(:area))
        ORDER BY hv.visit_date DESC, hv.start_time DESC
        LIMIT 20
    ");

    $stmt->execute([':area' => $area]);
    $records = array_merge($records, $stmt->fetchAll(PDO::FETCH_ASSOC));

    /*
     * 4. Vaccination records
     * Your vaccination_records primary key is vaccination_id.
     */
    $stmt = $pdo->prepare("
        SELECT
            CONCAT('vaccination_', vr.vaccination_id) AS record_key,
            vr.vaccination_id AS record_id,
            vr.vaccination_date AS activity_date,
            vr.vaccination_time AS start_time,
            vr.location,
            vr.status,
            vr.patient_name,
            vr.notes AS description,
            'Vaccination' AS type_name,
            m.full_name AS midwife_name,
            vr.duty_area AS assigned_area
        FROM vaccination_records vr
        INNER JOIN midwives m ON vr.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(vr.duty_area)) = LOWER(TRIM(:area))
        ORDER BY vr.vaccination_date DESC, vr.vaccination_time DESC
        LIMIT 20
    ");

    $stmt->execute([':area' => $area]);
    $records = array_merge($records, $stmt->fetchAll(PDO::FETCH_ASSOC));

    /*
     * 5. Counseling sessions
     */
    $stmt = $pdo->prepare("
        SELECT
            CONCAT('counseling_', cs.counseling_id) AS record_key,
            cs.counseling_id AS record_id,
            DATE(cs.session_datetime) AS activity_date,
            TIME(cs.session_datetime) AS start_time,
            cs.location_type AS location,
            cs.status,
            cs.client_ref AS patient_name,
            cs.notes AS description,
            'Counseling Session' AS type_name,
            m.full_name AS midwife_name,
            m.assigned_area
        FROM counseling_sessions cs
        INNER JOIN midwives m ON cs.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.assigned_area)) = LOWER(TRIM(:area))
        ORDER BY cs.session_datetime DESC
        LIMIT 20
    ");

    $stmt->execute([':area' => $area]);
    $records = array_merge($records, $stmt->fetchAll(PDO::FETCH_ASSOC));

    /*
     * 6. Health education sessions
     */
    $stmt = $pdo->prepare("
        SELECT
            CONCAT('health_education_', hes.health_education_id) AS record_key,
            hes.health_education_id AS record_id,
            hes.session_date AS activity_date,
            NULL AS start_time,
            hes.venue AS location,
            hes.status,
            hes.audience AS patient_name,
            hes.outcomes AS description,
            CONCAT('Health Education - ', hes.topic) AS type_name,
            m.full_name AS midwife_name,
            m.assigned_area
        FROM health_education_sessions hes
        INNER JOIN midwives m ON hes.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.assigned_area)) = LOWER(TRIM(:area))
        ORDER BY hes.session_date DESC
        LIMIT 20
    ");

    $stmt->execute([':area' => $area]);
    $records = array_merge($records, $stmt->fetchAll(PDO::FETCH_ASSOC));

    usort($records, function ($a, $b) {
        $dateA = ($a['activity_date'] ?? '') . ' ' . ($a['start_time'] ?? '');
        $dateB = ($b['activity_date'] ?? '') . ' ' . ($b['start_time'] ?? '');

        return strcmp($dateB, $dateA);
    });

    echo json_encode([
        'success' => true,
        'data' => [
            'recent_activities' => array_slice($records, 0, 20)
        ]
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Area stats loading error: ' . $e->getMessage()
    ]);
    exit;
}