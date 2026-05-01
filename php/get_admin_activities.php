<?php
/**
 * MidConnect - Get Admin Activities
 * Admin-only endpoint that returns midwife activity records for admin's assigned area.
 * Supports filtering by: activity_type, midwife_id, date_from, date_to, status, keyword, sort.
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

    // --- Read and sanitize filters ---
    $activityType = Utils::sanitizeInput($_GET['activity_type'] ?? '');
    $midwifeId    = isset($_GET['midwife_id']) && (int)$_GET['midwife_id'] > 0
                        ? (int)$_GET['midwife_id'] : null;
    $dateFrom     = isset($_GET['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_from'])
                        ? $_GET['date_from'] : null;
    $dateTo       = isset($_GET['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date_to'])
                        ? $_GET['date_to'] : null;
    $status       = Utils::sanitizeInput($_GET['status'] ?? '');
    $keyword      = Utils::sanitizeInput($_GET['keyword'] ?? '');
    $sort         = in_array($_GET['sort'] ?? '', ['latest', 'oldest', 'urgent']) ? $_GET['sort'] : 'latest';
    $limit        = min(200, max(10, (int)($_GET['limit'] ?? 100)));
    $offset       = max(0, (int)($_GET['offset'] ?? 0));

    // --- Build WHERE clause ---
    $where  = [];
    $params = [];

    // ALWAYS filter by admin's area for security
    $where[] = "m.assigned_area = ?";
    $params[] = $adminArea;

    if ($activityType) {
        $where[]  = "at.type_code = ?";
        $params[] = $activityType;
    }
    if ($midwifeId) {
        $where[]  = "a.midwife_id = ?";
        $params[] = $midwifeId;
    }
    if ($dateFrom) {
        $where[]  = "a.activity_date >= ?";
        $params[] = $dateFrom;
    }
    if ($dateTo) {
        $where[]  = "a.activity_date <= ?";
        $params[] = $dateTo;
    }
    if ($status) {
        $where[]  = "a.status = ?";
        $params[] = $status;
    }
    if ($keyword) {
        $where[]  = "(a.patient_name LIKE ? OR a.location LIKE ? OR a.description LIKE ? OR m.full_name LIKE ?)";
        $kw = '%' . $keyword . '%';
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
    }

    $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // --- ORDER BY ---
    $orderBy = match($sort) {
        'oldest'  => 'ORDER BY a.activity_date ASC, a.start_time ASC',
        'urgent'  => "ORDER BY CASE a.priority_level
                        WHEN 'critical' THEN 1
                        WHEN 'urgent'   THEN 2
                        WHEN 'high'     THEN 3
                        ELSE 4
                      END ASC, a.activity_date DESC",
        default   => 'ORDER BY a.activity_date DESC, a.start_time DESC',
    };

    // --- Count for pagination ---
    $countSql = "SELECT COUNT(*) as cnt
                 FROM activities a
                 JOIN midwives m         ON a.midwife_id        = m.midwife_id
                 JOIN activity_types at  ON a.activity_type_id  = at.type_id
                 $whereClause";
    $totalCount = $db->fetch($countSql, $params)['cnt'] ?? 0;

    // --- Main query ---
    $paginatedParams   = array_merge($params, [$limit, $offset]);
    $sql = "SELECT
                a.activity_id,
                a.activity_date,
                a.start_time,
                a.end_time,
                a.duration_minutes,
                a.patient_name,
                a.patient_age,
                a.location,
                a.description,
                a.observations,
                a.status,
                a.priority_level,
                a.follow_up_required,
                a.follow_up_date,
                a.created_at,
                m.midwife_id,
                m.full_name   AS midwife_name,
                m.employee_id AS midwife_emp_id,
                m.assigned_area,
                at.type_code  AS activity_type_code,
                at.type_name  AS activity_type_name
            FROM activities a
            JOIN midwives m         ON a.midwife_id        = m.midwife_id
            JOIN activity_types at  ON a.activity_type_id  = at.type_id
            $whereClause
            $orderBy
            LIMIT ? OFFSET ?";

    $rows = $db->fetchAll($sql, $paginatedParams);

    // --- Format rows ---
    $activities = array_map(function($row) {
        return [
            'activity_id'         => (int)$row['activity_id'],
            'date'                => $row['activity_date'],
            'start_time'          => $row['start_time'],
            'end_time'            => $row['end_time'],
            'duration_minutes'    => $row['duration_minutes'],
            'midwife_name'        => $row['midwife_name'],
            'midwife_emp_id'      => $row['midwife_emp_id'],
            'assigned_area'       => $row['assigned_area'],
            'activity_type_code'  => $row['activity_type_code'],
            'activity_type_name'  => $row['activity_type_name'],
            'patient_name'        => $row['patient_name'],
            'patient_age'         => $row['patient_age'],
            'location'            => $row['location'],
            'description'         => $row['description'],
            'observations'        => $row['observations'],
            'status'              => $row['status'],
            'priority_level'      => $row['priority_level'],
            'follow_up_required'  => (bool)$row['follow_up_required'],
            'follow_up_date'      => $row['follow_up_date'],
            'created_at'          => $row['created_at'],
        ];
    }, $rows);

    Utils::jsonResponse([
        'success' => true,
        'data' => [
            'activities'   => $activities,
            'total'        => (int)$totalCount,
            'limit'        => $limit,
            'offset'       => $offset,
            'has_more'     => ($offset + $limit) < $totalCount,
        ]
    ]);

} catch (Exception $e) {
    error_log("Admin activities error: " . $e->getMessage());
    Utils::jsonResponse(['success' => false, 'message' => 'Failed to load activities'], 500);
}
?>
