<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'midwife') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
    ]);
    exit;
}

$midwifeId = $_SESSION['midwife_id'] ?? null;

if (!$midwifeId) {
    echo json_encode([
        'success' => false,
        'message' => 'Midwife session not found.'
    ]);
    exit;
}

try {
    /*
     * Urgent Meetings
     * Uses schedules table.
     * High/urgent priority upcoming schedules are shown.
     */
    $urgentStmt = $pdo->prepare("
        SELECT
            s.schedule_id,
            s.scheduled_date,
            s.start_time,
            s.estimated_end_time,
            s.patient_name,
            s.location,
            s.description,
            s.priority_level,
            s.status,
            at.type_name
        FROM schedules s
        INNER JOIN activity_types at ON s.activity_type_id = at.type_id
        WHERE s.midwife_id = :midwife_id
          AND s.status IN ('scheduled', 'pending')
          AND s.scheduled_date >= CURDATE()
          AND LOWER(s.priority_level) IN ('urgent', 'high')
        ORDER BY s.scheduled_date ASC, s.start_time ASC
        LIMIT 5
    ");

    $urgentStmt->execute([
        ':midwife_id' => $midwifeId
    ]);

    $urgentMeetings = $urgentStmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     * Upcoming Clinics
     * Uses schedules + activity_types.
     * It detects clinic-related activities by type name/code/location.
     */
    $clinicStmt = $pdo->prepare("
        SELECT
            s.schedule_id,
            s.scheduled_date,
            s.start_time,
            s.estimated_end_time,
            s.patient_name,
            s.location,
            s.description,
            s.priority_level,
            s.status,
            at.type_code,
            at.type_name
        FROM schedules s
        INNER JOIN activity_types at ON s.activity_type_id = at.type_id
        WHERE s.midwife_id = :midwife_id
          AND s.status IN ('scheduled', 'pending')
          AND s.scheduled_date >= CURDATE()
          AND (
                LOWER(at.type_name) LIKE '%clinic%'
                OR LOWER(at.type_code) LIKE '%clinic%'
                OR LOWER(s.location) LIKE '%clinic%'
                OR LOWER(s.description) LIKE '%clinic%'
          )
        ORDER BY s.scheduled_date ASC, s.start_time ASC
        LIMIT 5
    ");

    $clinicStmt->execute([
        ':midwife_id' => $midwifeId
    ]);

    $upcomingClinics = $clinicStmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     * Today's Time Table
     * Uses today's schedules.
     */
    $todayStmt = $pdo->prepare("
        SELECT
            s.schedule_id,
            s.scheduled_date,
            s.start_time,
            s.estimated_end_time,
            s.patient_name,
            s.location,
            s.description,
            s.priority_level,
            s.status,
            at.type_name
        FROM schedules s
        INNER JOIN activity_types at ON s.activity_type_id = at.type_id
        WHERE s.midwife_id = :midwife_id
          AND s.scheduled_date = CURDATE()
          AND s.status IN ('scheduled', 'pending', 'completed')
        ORDER BY s.start_time ASC
        LIMIT 10
    ");

    $todayStmt->execute([
        ':midwife_id' => $midwifeId
    ]);

    $todaysTimetable = $todayStmt->fetchAll(PDO::FETCH_ASSOC);


    /*
     * Optional small summary counts.
     */
    $countStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS scheduled_activities
        FROM schedules
        WHERE midwife_id = :midwife_id
          AND scheduled_date >= CURDATE()
          AND status IN ('scheduled', 'pending')
    ");

    $countStmt->execute([
        ':midwife_id' => $midwifeId
    ]);

    $summary = $countStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'urgent_meetings' => $urgentMeetings,
        'upcoming_clinics' => $upcomingClinics,
        'todays_timetable' => $todaysTimetable,
        'summary' => [
            'scheduled_activities' => (int)($summary['scheduled_activities'] ?? 0)
        ]
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}