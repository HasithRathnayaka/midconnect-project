<?php
/**
 * MidConnect - Get Dashboard Widgets Handler
 * Retrieves data for: Urgent Meetings, Upcoming Clinics, Time Table, and Notifications
 */

require_once 'config.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Utils::jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Emulate user for testing if session is dead but midwife_id is passed
$userId = SessionManager::get('user_id');
if (!$userId && isset($_GET['midwife_id'])) {
    $userId = (int)$_GET['midwife_id'];
} elseif (!$userId) {
    // Fallback for development server visualization
    $userId = 1;
}

try {
    $db = Database::getInstance();
    
    // 1. Urgent Meetings
    // priority_level IN ('high', 'critical') OR status = 'urgent'
    $urgentMeetingsSql = "SELECT s.*, at.type_name as activity_type_name
                          FROM schedules s
                          LEFT JOIN activity_types at ON s.activity_type_id = at.type_id
                          WHERE s.midwife_id = ? 
                          AND (s.priority_level IN ('high', 'critical') OR s.description LIKE '%urgent%')
                          AND s.scheduled_date >= DATE('now', 'localtime')
                          AND s.status != 'completed'
                          ORDER BY s.scheduled_date ASC, s.start_time ASC
                          LIMIT 5";
    $urgentMeetings = $db->fetchAll($urgentMeetingsSql, [$userId]);

    // 2. Upcoming Clinics
    // type_code = 'CLINIC_VISIT'
    $upcomingClinicsSql = "SELECT s.*, at.type_name as activity_type_name
                           FROM schedules s
                           JOIN activity_types at ON s.activity_type_id = at.type_id
                           WHERE s.midwife_id = ? 
                           AND at.type_code = 'CLINIC_VISIT'
                           AND s.scheduled_date >= DATE('now', 'localtime')
                           AND s.status != 'completed'
                           ORDER BY s.scheduled_date ASC, s.start_time ASC
                           LIMIT 5";
    $upcomingClinics = $db->fetchAll($upcomingClinicsSql, [$userId]);

    // 3. Time Table (Today's schedule)
    $timetableSql = "SELECT s.*, at.type_name as activity_type_name
                     FROM schedules s
                     LEFT JOIN activity_types at ON s.activity_type_id = at.type_id
                     WHERE s.midwife_id = ? 
                     AND s.scheduled_date = DATE('now', 'localtime')
                     ORDER BY s.start_time ASC
                     LIMIT 10";
    $timetable = $db->fetchAll($timetableSql, [$userId]);

    // 4. Notifications / Reminders
    $notificationsSql = "SELECT *
                         FROM notifications
                         WHERE recipient_type = 'midwife' 
                         AND recipient_id = ? 
                         AND is_read = 0
                         ORDER BY created_at DESC
                         LIMIT 5";
    $notifications = $db->fetchAll($notificationsSql, [$userId]);

    // Return structured response
    Utils::jsonResponse([
        'success' => true,
        'data' => [
            'urgent_meetings' => $urgentMeetings,
            'upcoming_clinics' => $upcomingClinics,
            'timetable' => $timetable,
            'notifications' => $notifications
        ]
    ]);

} catch (Exception $e) {
    error_log("Dashboard widgets error: " . $e->getMessage());
    Utils::jsonResponse([
        'success' => false, 
        'message' => 'Failed to retrieve dashboard widgets'
    ], 500);
}
?>
