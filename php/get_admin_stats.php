<?php
/**
 * MidConnect - Get Admin Dashboard Stats
 * Returns summary statistics for the admin monitoring dashboard.
 * Queries: midwives, activities tables from SQLite.
 * Now includes area-based access control.
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Utils::jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Get admin area from session or parameter (for development)
    $adminArea = $_GET['area'] ?? null;
    
    // Security: In production, get area from authenticated admin session
    // For now, we'll use the area parameter but validate it
    if (!$adminArea) {
        Utils::jsonResponse(['success' => false, 'message' => 'Admin area not specified'], 400);
    }
    
    // Validate area against known areas
    $validAreas = ['Uduthuththiripitiya', 'Kahabilihena', 'Opathella', 'Ambalangoda', 'Colombo'];
    if (!in_array($adminArea, $validAreas)) {
        Utils::jsonResponse(['success' => false, 'message' => 'Invalid area specified'], 400);
    }

    // Total active midwives in admin's area
    $totalMidwives = $conn->fetch(
        "SELECT COUNT(*) as cnt FROM midwives WHERE status = 'active' AND assigned_area = ?",
        [$adminArea]
    )['cnt'] ?? 0;

    // Total activities in admin's area (all time)
    $totalActivities = $conn->fetch(
        "SELECT COUNT(*) as cnt FROM activities a
         JOIN midwives m ON a.midwife_id = m.midwife_id
         WHERE m.assigned_area = ?",
        [$adminArea]
    )['cnt'] ?? 0;

    // Activities logged today in admin's area
    $activitiesToday = $conn->fetch(
        "SELECT COUNT(*) as cnt FROM activities a
         JOIN midwives m ON a.midwife_id = m.midwife_id
         WHERE DATE(a.activity_date) = DATE('now', 'localtime')
           AND m.assigned_area = ?",
        [$adminArea]
    )['cnt'] ?? 0;

    // Urgent or pending activities in admin's area
    $urgentPending = $conn->fetch(
        "SELECT COUNT(*) as cnt FROM activities a
         JOIN midwives m ON a.midwife_id = m.midwife_id
         WHERE (a.status IN ('pending', 'in_progress') OR a.priority_level IN ('high', 'critical', 'urgent'))
           AND m.assigned_area = ?",
        [$adminArea]
    )['cnt'] ?? 0;

    // --- 4 Summary Card metrics ---
    // Home Visits today in admin's area
    $homeVisitsToday = $conn->fetch(
        "SELECT COUNT(*) as cnt FROM activities a
         JOIN activity_types at ON a.activity_type_id = at.type_id
         JOIN midwives m ON a.midwife_id = m.midwife_id
         WHERE at.type_code = 'HOME_VISIT'
           AND DATE(a.activity_date) = DATE('now', 'localtime')
           AND m.assigned_area = ?",
        [$adminArea]
    )['cnt'] ?? 0;

    // Vaccinations (this week) in admin's area
    $vaccinationsWeek = $conn->fetch(
        "SELECT COUNT(*) as cnt FROM activities a
         JOIN activity_types at ON a.activity_type_id = at.type_id
         JOIN midwives m ON a.midwife_id = m.midwife_id
         WHERE at.type_code = 'VACCINATION'
           AND DATE(a.activity_date) >= DATE('now', 'localtime', '-7 days')
           AND m.assigned_area = ?",
        [$adminArea]
    )['cnt'] ?? 0;

    // Clinic Sessions (this week) in admin's area
    $clinicSessionsWeek = $conn->fetch(
        "SELECT COUNT(*) as cnt FROM activities a
         JOIN activity_types at ON a.activity_type_id = at.type_id
         JOIN midwives m ON a.midwife_id = m.midwife_id
         WHERE at.type_code = 'CLINIC_VISIT'
           AND DATE(a.activity_date) >= DATE('now', 'localtime', '-7 days')
           AND m.assigned_area = ?",
        [$adminArea]
    )['cnt'] ?? 0;

    // Pending Reports — activities with status pending in admin's area
    $pendingReports = $conn->fetch(
        "SELECT COUNT(*) as cnt FROM activities a
         JOIN midwives m ON a.midwife_id = m.midwife_id
         WHERE a.status = 'pending'
           AND m.assigned_area = ?",
        [$adminArea]
    )['cnt'] ?? 0;

    // Coverage rate: % of active midwives who logged at least one activity this week in admin's area
    $midwivesActiveThisWeek = $conn->fetch(
        "SELECT COUNT(DISTINCT a.midwife_id) as cnt FROM activities a
         JOIN midwives m ON a.midwife_id = m.midwife_id
         WHERE DATE(a.activity_date) >= DATE('now', 'localtime', '-7 days')
           AND m.assigned_area = ?",
        [$adminArea]
    )['cnt'] ?? 0;
    $coverageRate = $totalMidwives > 0
        ? round(($midwivesActiveThisWeek / $totalMidwives) * 100)
        : 0;

    // Activities by type (for chart: last 7 days) in admin's area
    $typeSummary = $conn->fetchAll(
        "SELECT at.type_code, at.type_name, COUNT(a.activity_id) as total
         FROM activity_types at
         LEFT JOIN activities a ON at.type_id = a.activity_type_id
            AND DATE(a.activity_date) >= DATE('now', 'localtime', '-7 days')
         LEFT JOIN midwives m ON a.midwife_id = m.midwife_id
         WHERE at.is_active = 1
           AND (m.assigned_area = ? OR m.assigned_area IS NULL)
         GROUP BY at.type_id, at.type_code, at.type_name
         ORDER BY total DESC",
        [$adminArea]
    );

    // Weekly breakdown (Mon-Sun of current ISO week) — for line chart
    // Get the Monday of the current week
    $weeklyBreakdown = $db->fetchAll(
        "SELECT DATE(activity_date) as day,
                strftime('%w', activity_date) as weekday_num,
                SUM(CASE WHEN at.type_code = 'HOME_VISIT'   THEN 1 ELSE 0 END) as home_visits,
                SUM(CASE WHEN at.type_code = 'VACCINATION'  THEN 1 ELSE 0 END) as vaccinations,
                SUM(CASE WHEN at.type_code = 'CLINIC_VISIT' THEN 1 ELSE 0 END) as clinic_visits
         FROM activities a
         JOIN activity_types at ON a.activity_type_id = at.type_id
         WHERE DATE(activity_date) >= DATE('now', 'localtime', 'weekday 1', '-7 days')
           AND DATE(activity_date) <= DATE('now', 'localtime', 'weekday 0')
         GROUP BY DATE(activity_date)
         ORDER BY day ASC"
    );

    // Performance status breakdown
    $statusBreakdown = $db->fetchAll(
        "SELECT status, COUNT(*) as cnt FROM activities GROUP BY status"
    );

    // Top midwives by activity count (last 30 days)
    $topMidwives = $db->fetchAll(
        "SELECT m.full_name, m.employee_id, m.assigned_area,
                COUNT(a.activity_id) as total_activities,
                SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed,
                ROUND(
                    CAST(SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) AS REAL)
                    / NULLIF(COUNT(a.activity_id), 0) * 100
                , 0) as completion_rate
         FROM midwives m
         LEFT JOIN activities a ON m.midwife_id = a.midwife_id
            AND DATE(a.activity_date) >= DATE('now', 'localtime', '-30 days')
         WHERE m.status = 'active'
         GROUP BY m.midwife_id, m.full_name, m.employee_id, m.assigned_area
         ORDER BY total_activities DESC
         LIMIT 5"
    );

    // All midwives list (for filter dropdown)
    $midwivesList = $db->fetchAll(
        "SELECT midwife_id, full_name, employee_id, assigned_area
         FROM midwives
         WHERE status = 'active'
         ORDER BY full_name ASC"
    );

    Utils::jsonResponse([
        'success' => true,
        'data' => [
            'total_midwives'       => (int)$totalMidwives,
            'total_activities'     => (int)$totalActivities,
            'activities_today'     => (int)$activitiesToday,
            'urgent_pending'       => (int)$urgentPending,
            'home_visits_today'    => (int)$homeVisitsToday,
            'vaccinations_week'    => (int)$vaccinationsWeek,
            'clinic_sessions_week' => (int)$clinicSessionsWeek,
            'pending_reports'      => (int)$pendingReports,
            'coverage_rate'        => (int)$coverageRate,
            'type_summary'         => $typeSummary,
            'weekly_breakdown'     => $weeklyBreakdown,
            'status_breakdown'     => $statusBreakdown,
            'top_midwives'         => $topMidwives,
            'midwives_list'        => $midwivesList,
        ]
    ]);

} catch (Exception $e) {
    error_log("Admin stats error: " . $e->getMessage());
    Utils::jsonResponse(['success' => false, 'message' => 'Failed to load stats'], 500);
}
?>
