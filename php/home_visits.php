<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = [];
if ($method === 'POST' || $method === 'PUT') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? [];
}

$action = $_GET['action'] ?? '';

try {
    $db = Database::getInstance();
    
    switch ($action) {
        case 'get':
            $area = $_GET['area'] ?? null;
            
            $sql = "SELECT * FROM home_visits WHERE 1=1";
            $params = [];
            
            if ($area) {
                $sql .= " AND duty_area = ?";
                $params[] = $area;
            }
            
            $sql .= " ORDER BY visit_date DESC, start_time DESC";
            $results = $db->fetchAll($sql, $params);
            Utils::jsonResponse(['success' => true, 'data' => $results]);
            break;
            
        case 'get_by_id':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                Utils::jsonResponse(['success' => false, 'message' => 'Visit ID is required'], 400);
            }
            $result = $db->fetch("SELECT * FROM home_visits WHERE id = ?", [$id]);
            if ($result) {
                Utils::jsonResponse(['success' => true, 'data' => $result]);
            } else {
                Utils::jsonResponse(['success' => false, 'message' => 'Visit not found'], 404);
            }
            break;
            
        case 'create':
            if (empty($input['patient_name']) || empty($input['visit_date']) || empty($input['start_time'])) {
                Utils::jsonResponse(['success' => false, 'message' => 'Patient name, date and time are required'], 400);
            }
            
            // Get valid midwife_id from database
            $midwife = $db->fetch("SELECT midwife_id FROM midwives LIMIT 1");
            $validMidwifeId = $midwife ? $midwife['midwife_id'] : 1;
            
            $sql = "INSERT INTO home_visits (
                        midwife_id, patient_name, contact_number, address, 
                        visit_date, start_time, duration_minutes,
                        duty_area, visit_type, priority, reason, status, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $validMidwifeId,
                $input['patient_name'],
                $input['contact_number'] ?? '',
                $input['address'] ?? '',
                $input['visit_date'],
                $input['start_time'],
                $input['duration_minutes'] ?? 45,
                $input['duty_area'] ?? 'uduthuththiripitiya',
                $input['visit_type'] ?? 'routine',
                $input['priority'] ?? 'normal',
                $input['reason'] ?? '',
                'scheduled',
                $input['notes'] ?? ''
            ];
            
            $db->execute($sql, $params);
            $id = $db->lastInsertId();
            $created = $db->fetch("SELECT * FROM home_visits WHERE id = ?", [$id]);
            
            Utils::jsonResponse(['success' => true, 'message' => 'Home visit created successfully', 'data' => $created], 201);
            break;
            
        case 'update':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                Utils::jsonResponse(['success' => false, 'message' => 'Visit ID is required'], 400);
            }
            
            $updateFields = [];
            $params = [];
            
            $allowedFields = ['patient_name', 'contact_number', 'address', 'visit_date', 
                            'start_time', 'duration_minutes', 'duty_area', 'visit_type', 
                            'priority', 'reason', 'status', 'notes'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            if (empty($updateFields)) {
                Utils::jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
            }
            
            $params[] = $id;
            $sql = "UPDATE home_visits SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $db->execute($sql, $params);
            
            $updated = $db->fetch("SELECT * FROM home_visits WHERE id = ?", [$id]);
            Utils::jsonResponse(['success' => true, 'message' => 'Home visit updated successfully', 'data' => $updated]);
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                Utils::jsonResponse(['success' => false, 'message' => 'Visit ID is required'], 400);
            }
            $db->execute("DELETE FROM home_visits WHERE id = ?", [$id]);
            Utils::jsonResponse(['success' => true, 'message' => 'Home visit deleted successfully']);
            break;
            
        case 'complete':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                Utils::jsonResponse(['success' => false, 'message' => 'Visit ID is required'], 400);
            }
            
            $endTime = $input['end_time'] ?? date('H:i:s');
            $notes = $input['notes'] ?? '';
            
            $sql = "UPDATE home_visits SET status = 'completed', end_time = ?, notes = ?, completed_at = datetime('now') WHERE id = ?";
            $db->execute($sql, [$endTime, $notes, $id]);
            
            $completed = $db->fetch("SELECT * FROM home_visits WHERE id = ?", [$id]);
            Utils::jsonResponse(['success' => true, 'message' => 'Home visit marked as completed', 'data' => $completed]);
            break;
            
        default:
            Utils::jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    error_log("Home Visits API Error: " . $e->getMessage());
    Utils::jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
