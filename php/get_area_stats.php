<?php
/**
 * MidConnect - Get Area Statistics
 * Admin-only endpoint that returns statistics and activities for a specific area
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Utils::jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get and sanitize area parameter
    $area = Utils::sanitizeInput($_GET['area'] ?? '');
    
    if (empty($area)) {
        Utils::jsonResponse(['success' => false, 'message' => 'Area parameter is required'], 400);
    }
    
    // Get area statistics
    $today = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    
    // 1. Get active midwives in this area
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM midwives WHERE assigned_area = ? AND status = 'active'");
    $stmt->execute([$area]);
    $activeMidwives = $stmt->fetch()['count'];
    
    // 2. Get today's activities for this area
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM activities a 
        JOIN midwives m ON a.midwife_id = m.midwife_id 
        WHERE m.assigned_area = ? AND a.activity_date = ?
    ");
    $stmt->execute([$area, $today]);
    $todayActivities = $stmt->fetch()['count'];
    
    // 3. Get this week's activities for this area
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM activities a 
        JOIN midwives m ON a.midwife_id = m.midwife_id 
        WHERE m.assigned_area = ? AND a.activity_date >= ?
    ");
    $stmt->execute([$area, $weekStart]);
    $weekActivities = $stmt->fetch()['count'];
    
    // 4. Get activity breakdown by type for this area
    $stmt = $conn->prepare("
        SELECT 
            at.type_code,
            at.type_name,
            COUNT(*) as count
        FROM activities a
        JOIN activity_types at ON a.activity_type_id = at.type_id
        JOIN midwives m ON a.midwife_id = m.midwife_id
        WHERE m.assigned_area = ? AND a.activity_date >= ?
        GROUP BY at.type_code, at.type_name
        ORDER BY count DESC
    ");
    $stmt->execute([$area, $weekStart]);
    $activityBreakdown = $stmt->fetchAll();
    
    // 5. Get status breakdown for this area
    $stmt = $conn->prepare("
        SELECT 
            a.status,
            COUNT(*) as count
        FROM activities a
        JOIN midwives m ON a.midwife_id = m.midwife_id
        WHERE m.assigned_area = ? AND a.activity_date >= ?
        GROUP BY a.status
    ");
    $stmt->execute([$area, $weekStart]);
    $statusBreakdown = $stmt->fetchAll();
    
    // 6. Get recent activities for this area
    $stmt = $conn->prepare("
        SELECT 
            a.activity_id,
            a.activity_date,
            a.start_time,
            a.end_time,
            a.location,
            a.description,
            a.patient_name,
            a.patient_age,
            a.status,
            a.priority_level,
            m.full_name as midwife_name,
            m.assigned_area,
            at.type_code,
            at.type_name
        FROM activities a
        JOIN midwives m ON a.midwife_id = m.midwife_id
        JOIN activity_types at ON a.activity_type_id = at.type_id
        WHERE m.assigned_area = ?
        ORDER BY a.activity_date DESC, a.start_time DESC
        LIMIT 50
    ");
    $stmt->execute([$area]);
    $recentActivities = $stmt->fetchAll();
    
    // 7. Get last activity date for this area
    $stmt = $conn->prepare("
        SELECT a.activity_date, a.start_time, m.full_name
        FROM activities a
        JOIN midwives m ON a.midwife_id = m.midwife_id
        WHERE m.assigned_area = ?
        ORDER BY a.activity_date DESC, a.start_time DESC
        LIMIT 1
    ");
    $stmt->execute([$area]);
    $lastActivity = $stmt->fetch();
    
    // Calculate completed and pending counts
    $completedCount = 0;
    $pendingCount = 0;
    foreach ($statusBreakdown as $status) {
        if ($status['status'] === 'completed') {
            $completedCount = $status['count'];
        } elseif ($status['status'] === 'pending') {
            $pendingCount = $status['count'];
        }
    }
    
    // Format last activity text
    $lastActivityText = 'No recent activity';
    if ($lastActivity) {
        $lastDate = new DateTime($lastActivity['activity_date']);
        $today = new DateTime();
        $diffDays = $today->diff($lastDate)->days;
        
        if ($diffDays === 0) {
            $lastActivityText = 'Today - ' . $lastActivity['full_name'];
        } elseif ($diffDays === 1) {
            $lastActivityText = 'Yesterday - ' . $lastActivity['full_name'];
        } elseif ($diffDays < 7) {
            $lastActivityText = $diffDays . ' days ago';
        } else {
            $lastActivityText = $lastDate->format('M j, Y');
        }
    }
    
    Utils::jsonResponse([
        'success' => true,
        'data' => [
            'area' => $area,
            'active_midwives' => $activeMidwives,
            'today_activities' => $todayActivities,
            'week_activities' => $weekActivities,
            'completed_activities' => $completedCount,
            'pending_activities' => $pendingCount,
            'last_activity' => $lastActivityText,
            'activity_breakdown' => $activityBreakdown,
            'status_breakdown' => $statusBreakdown,
            'recent_activities' => $recentActivities
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get area stats error: " . $e->getMessage());
    Utils::jsonResponse(['success' => false, 'message' => 'Internal server error'], 500);
}
?>
