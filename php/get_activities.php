<?php
/**
 * MidConnect - Get Activities Handler
 * Retrieves activities with filtering and pagination
 */

require_once 'config.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Utils::jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Check if user is logged in
if (!SessionManager::has('user_type')) {
    Utils::jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    $userType = SessionManager::get('user_type');
    $userId = SessionManager::get('user_id');
    
    // Get filter parameters
    $filters = [
        'midwife_id' => isset($_GET['midwife_id']) ? (int)$_GET['midwife_id'] : null,
        'activity_type' => Utils::sanitizeInput($_GET['activity_type'] ?? ''),
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
        'status' => Utils::sanitizeInput($_GET['status'] ?? ''),
        'priority' => Utils::sanitizeInput($_GET['priority'] ?? ''),
        'patient_name' => Utils::sanitizeInput($_GET['patient_name'] ?? ''),
        'location' => Utils::sanitizeInput($_GET['location'] ?? ''),
        'page' => isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1,
        'limit' => isset($_GET['limit']) ? min(100, max(10, (int)$_GET['limit'])) : 20
    ];
    
    // Calculate offset for pagination
    $offset = ($filters['page'] - 1) * $filters['limit'];
    
    // Get database connection
    $db = Database::getInstance();
    
    // Build WHERE clause based on user type and filters
    $whereConditions = [];
    $params = [];
    
    // User access control
    if ($userType === 'midwife') {
        // Midwives can only see their own activities
        $whereConditions[] = "a.midwife_id = ?";
        $params[] = $userId;
    } elseif ($userType === 'admin') {
        // Admins can see activities from their MOH office
        $mohOffice = SessionManager::get('moh_office');
        $whereConditions[] = "m.moh_office = ?";
        $params[] = $mohOffice;
        
        // Optional midwife filter for admins
        if ($filters['midwife_id']) {
            $whereConditions[] = "a.midwife_id = ?";
            $params[] = $filters['midwife_id'];
        }
    }
    
    // Apply other filters
    if ($filters['activity_type']) {
        $whereConditions[] = "at.type_code = ?";
        $params[] = $filters['activity_type'];
    }
    
    if ($filters['date_from']) {
        $whereConditions[] = "a.activity_date >= ?";
        $params[] = $filters['date_from'];
    }
    
    if ($filters['date_to']) {
        $whereConditions[] = "a.activity_date <= ?";
        $params[] = $filters['date_to'];
    }
    
    if ($filters['status']) {
        $whereConditions[] = "a.status = ?";
        $params[] = $filters['status'];
    }
    
    if ($filters['priority']) {
        $whereConditions[] = "a.priority_level = ?";
        $params[] = $filters['priority'];
    }
    
    if ($filters['patient_name']) {
        $whereConditions[] = "a.patient_name LIKE ?";
        $params[] = '%' . $filters['patient_name'] . '%';
    }
    
    if ($filters['location']) {
        $whereConditions[] = "a.location LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
    }
    
    // Build the main query
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $sql = "SELECT 
                a.*,
                m.full_name as midwife_name,
                m.employee_id,
                m.assigned_area,
                at.type_name as activity_type_name,
                at.type_code as activity_type_code,
                CASE 
                    WHEN a.follow_up_required = 1 AND a.follow_up_date IS NOT NULL THEN 1
                    ELSE 0
                END as has_follow_up
            FROM activities a
            JOIN midwives m ON a.midwife_id = m.midwife_id
            JOIN activity_types at ON a.activity_type_id = at.type_id
            {$whereClause}
            ORDER BY a.activity_date DESC, a.start_time DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $filters['limit'];
    $params[] = $offset;
    
    // Execute main query
    $activities = $db->fetchAll($sql, $params);
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total
                FROM activities a
                JOIN midwives m ON a.midwife_id = m.midwife_id
                JOIN activity_types at ON a.activity_type_id = at.type_id
                {$whereClause}";
    
    $countParams = array_slice($params, 0, -2); // Remove limit and offset
    $totalResult = $db->fetch($countSql, $countParams);
    $total = $totalResult['total'];
    
    // Format activities data
    $formattedActivities = array_map(function($activity) {
        return [
            'activity_id' => $activity['activity_id'],
            'midwife' => [
                'id' => $activity['midwife_id'],
                'name' => $activity['midwife_name'],
                'employee_id' => $activity['employee_id'],
                'assigned_area' => $activity['assigned_area']
            ],
            'activity_type' => [
                'code' => $activity['activity_type_code'],
                'name' => $activity['activity_type_name']
            ],
            'patient' => [
                'name' => $activity['patient_name'],
                'age' => $activity['patient_age'],
                'contact' => $activity['patient_contact']
            ],
            'schedule' => [
                'date' => $activity['activity_date'],
                'start_time' => $activity['start_time'],
                'end_time' => $activity['end_time'],
                'duration_minutes' => $activity['duration_minutes']
            ],
            'details' => [
                'location' => $activity['location'],
                'description' => $activity['description'],
                'observations' => $activity['observations'],
                'recommendations' => $activity['recommendations']
            ],
            'follow_up' => [
                'required' => (bool)$activity['follow_up_required'],
                'date' => $activity['follow_up_date'],
                'has_follow_up' => (bool)$activity['has_follow_up']
            ],
            'metadata' => [
                'priority_level' => $activity['priority_level'],
                'status' => $activity['status'],
                'gps_location' => [
                    'latitude' => $activity['gps_latitude'],
                    'longitude' => $activity['gps_longitude']
                ],
                'weather_conditions' => $activity['weather_conditions'],
                'transport_method' => $activity['transport_method'],
                'created_at' => $activity['created_at'],
                'updated_at' => $activity['updated_at']
            ]
        ];
    }, $activities);
    
    // Get summary statistics
    $statsSql = "SELECT 
                    COUNT(*) as total_activities,
                    COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_activities,
                    COUNT(CASE WHEN a.priority_level = 'high' THEN 1 END) as high_priority,
                    COUNT(CASE WHEN a.priority_level = 'urgent' THEN 1 END) as urgent_priority,
                    COUNT(CASE WHEN a.follow_up_required = 1 THEN 1 END) as follow_ups_required,
                    COUNT(DISTINCT a.patient_name) as unique_patients,
                    ROUND(AVG(a.duration_minutes), 2) as avg_duration_minutes,
                    SUM(a.duration_minutes) as total_duration_minutes
                FROM activities a
                JOIN midwives m ON a.midwife_id = m.midwife_id
                JOIN activity_types at ON a.activity_type_id = at.type_id
                {$whereClause}";
    
    $stats = $db->fetch($statsSql, $countParams);
    
    // Calculate pagination info
    $totalPages = ceil($total / $filters['limit']);
    $hasNextPage = $filters['page'] < $totalPages;
    $hasPrevPage = $filters['page'] > 1;
    
    // Return response
    Utils::jsonResponse([
        'success' => true,
        'data' => [
            'activities' => $formattedActivities,
            'pagination' => [
                'current_page' => $filters['page'],
                'total_pages' => $totalPages,
                'per_page' => $filters['limit'],
                'total_records' => $total,
                'has_next_page' => $hasNextPage,
                'has_prev_page' => $hasPrevPage
            ],
            'summary' => [
                'total_activities' => (int)$stats['total_activities'],
                'completed_activities' => (int)$stats['completed_activities'],
                'high_priority' => (int)$stats['high_priority'],
                'urgent_priority' => (int)$stats['urgent_priority'],
                'follow_ups_required' => (int)$stats['follow_ups_required'],
                'unique_patients' => (int)$stats['unique_patients'],
                'avg_duration_minutes' => (float)$stats['avg_duration_minutes'],
                'total_duration_hours' => round($stats['total_duration_minutes'] / 60, 2)
            ],
            'filters_applied' => array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            })
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get activities error: " . $e->getMessage());
    Utils::jsonResponse([
        'success' => false, 
        'message' => 'Failed to retrieve activities'
    ], 500);
}
?>