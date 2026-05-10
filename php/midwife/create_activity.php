<?php
/**
 * MidConnect - Create Activity Handler
 * Handles creation of new midwife activities
 */

require_once '../config.php';



session_start();

$userType = $_SESSION['user_type'] ?? null;

$userId = $_SESSION['user_id'] ?? null;
$sessionId = session_id();


?>

<h1>hello boss</h1>

<script>
console.log("Session ID:", <?php echo json_encode($sessionId); ?>);
console.log("User Type:", <?php echo json_encode($userType); ?>);
console.log("User ID:", <?php echo json_encode($userId); ?>);
</script>










// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Check if user is logged in as midwife
if (!SessionManager::has('user_type') || SessionManager::get('user_type') !== 'midwife') {
    Utils::jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    // Get input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // If JSON decode fails, try form data
    if (!$data) {
        $data = $_POST;
    }
    
    // Sanitize input data
    $activityData = [
        'midwife_id' => SessionManager::get('user_id'),
        'activity_type' => Utils::sanitizeInput($data['activity_type'] ?? ''),
        'patient_name' => Utils::sanitizeInput($data['patient_name'] ?? ''),
        'patient_age' => isset($data['patient_age']) ? (int)$data['patient_age'] : null,
        'patient_contact' => Utils::sanitizeInput($data['patient_contact'] ?? ''),
        'activity_datetime' => $data['activity_datetime'] ?? '',
        'location' => Utils::sanitizeInput($data['location'] ?? ''),
        'description' => Utils::sanitizeInput($data['description'] ?? ''),
        'observations' => Utils::sanitizeInput($data['observations'] ?? ''),
        'recommendations' => Utils::sanitizeInput($data['recommendations'] ?? ''),
        'follow_up_required' => isset($data['follow_up_required']) && $data['follow_up_required'] === 'yes',
        'follow_up_date' => $data['follow_up_date'] ?? null,
        'priority_level' => Utils::sanitizeInput($data['priority_level'] ?? 'normal'),
        'duration_minutes' => isset($data['duration']) ? (int)$data['duration'] : null,
        'gps_latitude' => isset($data['gps_latitude']) ? (float)$data['gps_latitude'] : null,
        'gps_longitude' => isset($data['gps_longitude']) ? (float)$data['gps_longitude'] : null,
        'weather_conditions' => Utils::sanitizeInput($data['weather_conditions'] ?? ''),
        'transport_method' => Utils::sanitizeInput($data['transport_method'] ?? '')
    ];
    
    // Validate required fields
    $requiredFields = ['activity_type', 'patient_name', 'activity_datetime', 'location'];
    $errors = [];
    
    foreach ($requiredFields as $field) {
        if (empty($activityData[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    if (!empty($errors)) {
        Utils::jsonResponse([
            'success' => false, 
            'message' => 'Validation failed',
            'errors' => $errors
        ], 400);
    }
    
    // Parse datetime
    $datetime = new DateTime($activityData['activity_datetime']);
    $activityData['activity_date'] = $datetime->format('Y-m-d');
    $activityData['start_time'] = $datetime->format('H:i:s');
    
    // Calculate end time if duration is provided
    if ($activityData['duration_minutes']) {
        $endTime = clone $datetime;
        $endTime->add(new DateInterval('PT' . $activityData['duration_minutes'] . 'M'));
        $activityData['end_time'] = $endTime->format('H:i:s');
    }
    
    // Get database connection
    $db = Database::getInstance();
    
    // Get activity type ID
    $activityTypeSql = "SELECT type_id FROM activity_types WHERE type_code = ? AND is_active = 1";
    $activityType = $db->fetch($activityTypeSql, [$activityData['activity_type']]);
    
    if (!$activityType) {
        Utils::jsonResponse([
            'success' => false, 
            'message' => 'Invalid activity type'
        ], 400);
    }
    
    $activityData['activity_type_id'] = $activityType['type_id'];
    unset($activityData['activity_type']); // Remove the code, we have the ID now
    
    // Set status and timestamps
    $activityData['status'] = 'completed';
    $activityData['created_at'] = date('Y-m-d H:i:s');
    $activityData['updated_at'] = date('Y-m-d H:i:s');
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Insert activity
        $activityId = $db->execute(
            "INSERT INTO activities (
                midwife_id, activity_type_id, patient_name, patient_age, patient_contact,
                activity_date, start_time, end_time, duration_minutes, location,
                description, observations, recommendations, follow_up_required, follow_up_date,
                priority_level, gps_latitude, gps_longitude, weather_conditions, 
                transport_method, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $activityData['midwife_id'],
                $activityData['activity_type_id'],
                $activityData['patient_name'],
                $activityData['patient_age'],
                $activityData['patient_contact'],
                $activityData['activity_date'],
                $activityData['start_time'],
                $activityData['end_time'],
                $activityData['duration_minutes'],
                $activityData['location'],
                $activityData['description'],
                $activityData['observations'],
                $activityData['recommendations'],
                $activityData['follow_up_required'] ? 1 : 0,
                $activityData['follow_up_date'],
                $activityData['priority_level'],
                $activityData['gps_latitude'],
                $activityData['gps_longitude'],
                $activityData['weather_conditions'],
                $activityData['transport_method'],
                $activityData['status'],
                $activityData['created_at'],
                $activityData['updated_at']
            ]
        );
        
        $insertedId = $db->lastInsertId();
        
        // Create patient record if it doesn't exist
        if (!empty($activityData['patient_name'])) {
            $existingPatient = $db->fetch(
                "SELECT patient_id FROM patients WHERE patient_name = ? AND assigned_midwife_id = ?",
                [$activityData['patient_name'], $activityData['midwife_id']]
            );
            
            if (!$existingPatient) {
                $patientId = $db->execute(
                    "INSERT INTO patients (patient_name, contact_number, assigned_midwife_id, registration_date, created_at) 
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        $activityData['patient_name'],
                        $activityData['patient_contact'],
                        $activityData['midwife_id'],
                        $activityData['activity_date'],
                        $activityData['created_at']
                    ]
                );
                
                $patientId = $db->lastInsertId();
            } else {
                $patientId = $existingPatient['patient_id'];
            }
            
            // Create patient visit record
            $db->execute(
                "INSERT INTO patient_visits (patient_id, activity_id, visit_type, diagnosis, treatment_given, visit_notes, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $patientId,
                    $insertedId,
                    $activityData['activity_type'],
                    $activityData['observations'],
                    $activityData['recommendations'],
                    $activityData['description'],
                    $activityData['created_at']
                ]
            );
        }
        
        // Update daily performance metrics
        $db->execute("CALL CalculateDailyMetrics(?)", [$activityData['activity_date']]);
        
        // Create follow-up schedule if required
        if ($activityData['follow_up_required'] && $activityData['follow_up_date']) {
            $db->execute(
                "INSERT INTO schedules (
                    midwife_id, activity_type_id, scheduled_date, start_time, estimated_end_time,
                    patient_name, location, description, priority_level, status, created_at
                ) VALUES (?, ?, ?, '09:00:00', '10:00:00', ?, ?, ?, ?, 'scheduled', ?)",
                [
                    $activityData['midwife_id'],
                    $activityData['activity_type_id'],
                    $activityData['follow_up_date'],
                    $activityData['patient_name'],
                    $activityData['location'],
                    'Follow-up: ' . $activityData['description'],
                    $activityData['priority_level'],
                    $activityData['created_at']
                ]
            );
        }
        
        // Commit transaction
        $db->commit();
        
        // Log activity creation
        Utils::logActivity('midwife', $activityData['midwife_id'], 'create_activity', [
            'activity_id' => $insertedId,
            'activity_type' => $activityData['activity_type'],
            'table' => 'activities'
        ]);
        
        // Send notification to supervisor if high priority or emergency
        if (in_array($activityData['priority_level'], ['high', 'urgent'])) {
            $midwife = $db->fetch(
                "SELECT supervisor_id, full_name FROM midwives WHERE midwife_id = ?",
                [$activityData['midwife_id']]
            );
            
            if ($midwife && $midwife['supervisor_id']) {
                Utils::sendNotification(
                    'admin',
                    $midwife['supervisor_id'],
                    'High Priority Activity Logged',
                    "{$midwife['full_name']} logged a {$activityData['priority_level']} priority activity: {$activityData['description']}",
                    $activityData['priority_level'] === 'urgent' ? 'danger' : 'warning'
                );
            }
        }
        
        // Return success response
        Utils::jsonResponse([
            'success' => true,
            'message' => 'Activity logged successfully',
            'activity_id' => $insertedId,
            'follow_up_scheduled' => $activityData['follow_up_required']
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Create activity error: " . $e->getMessage());
    Utils::jsonResponse([
        'success' => false, 
        'message' => 'Failed to create activity'
    ], 500);
}
?>