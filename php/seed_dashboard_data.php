<?php
/**
 * Seed Dashboard Widget Data
 * Run this from the CLI: php php/seed_dashboard_data.php
 */

require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Seeding dashboard data...\n";
    $conn->beginTransaction();

    $midwifeId = 1; // Madhavi Perera from init_sqlite
    $creatorId = 1; // Admin Sarah

    // Clear old sample data to prevent duplicate spam if run multiple times
    $db->execute("DELETE FROM schedules WHERE description LIKE '%[SEED]%'");
    $db->execute("DELETE FROM notifications WHERE message LIKE '%[SEED]%'");

    // 1. Seed Urgent Meetings (priority = 'critical' or 'high')
    $urgentMeetingSql = "INSERT INTO schedules (midwife_id, activity_type_id, scheduled_date, start_time, estimated_end_time, location, description, priority_level, created_by) VALUES (?, ?, DATE('now', 'localtime', '+1 day'), ?, ?, ?, ?, ?, ?)";
    $db->execute($urgentMeetingSql, [$midwifeId, 6, '14:00', '15:30', 'Regional Hospital', '[SEED] Urgent Maternal Transport Protocol Review', 'critical', $creatorId]);
    $db->execute($urgentMeetingSql, [$midwifeId, 4, '09:00', '10:00', 'MOH Office', '[SEED] High Risk Patient Briefing', 'high', $creatorId]);

    // 2. Seed Upcoming Clinics (activity_type_id = 2 is CLINIC_VISIT)
    $clinicSql = "INSERT INTO schedules (midwife_id, activity_type_id, scheduled_date, start_time, estimated_end_time, location, description, priority_level, created_by) VALUES (?, ?, DATE('now', 'localtime', '+2 days'), ?, ?, ?, ?, ?, ?)";
    $db->execute($clinicSql, [$midwifeId, 2, '08:30', '12:30', 'Colombo North Clinic Hall', '[SEED] Routine Antenatal Clinic', 'normal', $creatorId]);
    $db->execute($clinicSql, [$midwifeId, 2, '13:00', '16:00', 'MOH Office', '[SEED] Family Planning Clinic', 'normal', $creatorId]);

    // 3. Seed Time Table (TODAY)
    $todaySql = "INSERT INTO schedules (midwife_id, activity_type_id, scheduled_date, start_time, estimated_end_time, patient_name, location, description, priority_level, created_by) VALUES (?, ?, DATE('now', 'localtime'), ?, ?, ?, ?, ?, ?, ?)";
    $db->execute($todaySql, [$midwifeId, 1, '08:00', '08:45', 'Mrs. Kamala Silva', 'No. 45, Galle Road', '[SEED] Postnatal Visit Day 5', 'normal', $creatorId]);
    $db->execute($todaySql, [$midwifeId, 3, '10:00', '11:00', 'Multiple Patients', 'Temple Road School', '[SEED] Preschool Vaccination Catch-up', 'normal', $creatorId]);
    $db->execute($todaySql, [$midwifeId, 4, '14:00', '15:00', 'Ms. Nimali', 'MOH Office Room 2', '[SEED] Nutritional Counseling', 'normal', $creatorId]);

    // 4. Seed Notifications (is_read = 0)
    $notifSql = "INSERT INTO notifications (recipient_type, recipient_id, sender_type, title, message, notification_type) VALUES (?, ?, ?, ?, ?, ?)";
    $db->execute($notifSql, ['midwife', $midwifeId, 'system', 'Urgent Notice', '[SEED] Monthly reports are due tomorrow at 5PM.', 'warning']);
    $db->execute($notifSql, ['midwife', $midwifeId, 'admin', 'Schedule Updated', '[SEED] Dr. Sarah has updated your clinic roster for next week.', 'info']);

    $conn->commit();
    echo "Seeding completed successfully!\n";

} catch (Exception $e) {
    if (isset($conn)) $conn->rollBack();
    echo "Error seeding data: " . $e->getMessage() . "\n";
}
?>
