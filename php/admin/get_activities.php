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

try {
    $stmt = $pdo->prepare("
        SELECT
            a.activity_id,
            a.activity_date,
            a.start_time,
            a.end_time,
            a.duration_minutes,
            a.patient_name,
            a.patient_age,
            a.patient_contact,
            a.location,
            a.description,
            a.observations,
            a.recommendations,
            a.follow_up_required,
            a.follow_up_date,
            a.priority_level,
            a.status,
            a.created_at,

            m.midwife_id,
            m.employee_id,
            m.full_name AS midwife_name,
            m.assigned_area,
            m.moh_office,

            at.type_name AS activity_type,
            at.type_code AS activity_code

        FROM activities a
        INNER JOIN midwives m 
            ON a.midwife_id = m.midwife_id
        INNER JOIN activity_types at 
            ON a.activity_type_id = at.type_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        ORDER BY a.activity_date DESC, a.start_time DESC
        LIMIT 100
    ");

    $stmt->execute([
        ':moh_office' => $adminMohOffice
    ]);

    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Activity loading error: ' . $e->getMessage(),
        'activities' => []
    ]);
    exit;
}