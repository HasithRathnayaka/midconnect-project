<?php
/**
 * MidConnect - Midwife Login Handler
 * Handles midwife authentication
 */

require_once 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    // Get input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // If JSON decode fails, try form data
    if (!$data) {
        $data = $_POST;
    }
    
    // Sanitize input
    $employeeId = Utils::sanitizeInput($data['employee_id'] ?? '');
    $password = $data['password'] ?? '';
    $rememberMe = isset($data['remember_me']) && $data['remember_me'];
    
    // Validate required fields
    if (empty($employeeId) || empty($password)) {
        Utils::jsonResponse([
            'success' => false, 
            'message' => 'Employee ID and password are required'
        ], 400);
    }
    
    // Get database connection
    $db = Database::getInstance();
    
    // Find midwife by employee ID
    $sql = "SELECT m.*, ma.full_name as supervisor_name 
            FROM midwives m 
            LEFT JOIN moh_admins ma ON m.supervisor_id = ma.admin_id 
            WHERE m.employee_id = ? AND m.status = 'active'";
    
    $midwife = $db->fetch($sql, [$employeeId]);
    
    if (!$midwife) {
        // Log failed login attempt
        Utils::logActivity('midwife', null, 'failed_login', [
            'employee_id' => $employeeId,
            'reason' => 'user_not_found'
        ]);
        
        Utils::jsonResponse([
            'success' => false, 
            'message' => 'Invalid employee ID or password'
        ], 401);
    }
    
    // Check if account is locked
    if ($midwife['locked_until'] && new DateTime($midwife['locked_until']) > new DateTime()) {
        Utils::jsonResponse([
            'success' => false, 
            'message' => 'Account is temporarily locked. Please try again later.'
        ], 423);
    }
    
    // Verify password
    if (!Utils::verifyPassword($password, $midwife['password_hash'])) {
        // Increment login attempts
        $attempts = $midwife['login_attempts'] + 1;
        $lockUntil = null;
        
        // Lock account after 5 failed attempts
        if ($attempts >= 5) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        }
        
        // Update login attempts
        $updateSql = "UPDATE midwives SET login_attempts = ?, locked_until = ? WHERE midwife_id = ?";
        $db->execute($updateSql, [$attempts, $lockUntil, $midwife['midwife_id']]);
        
        // Log failed login attempt
        Utils::logActivity('midwife', $midwife['midwife_id'], 'failed_login', [
            'employee_id' => $employeeId,
            'reason' => 'invalid_password',
            'attempts' => $attempts
        ]);
        
        $message = $attempts >= 5 
            ? 'Account locked due to multiple failed attempts. Please try again in 30 minutes.'
            : 'Invalid employee ID or password';
            
        Utils::jsonResponse([
            'success' => false, 
            'message' => $message
        ], 401);
    }
    
    // Successful login - reset login attempts and update last login
    $db->execute(
        "UPDATE midwives SET login_attempts = 0, locked_until = NULL, last_login = date('now', 'localtime') WHERE midwife_id = ?", 
        [$midwife['midwife_id']]
    );
    
    // Create session
    SessionManager::regenerate();
    SessionManager::set('user_type', 'midwife');
    SessionManager::set('user_id', $midwife['midwife_id']);
    SessionManager::set('employee_id', $midwife['employee_id']);
    SessionManager::set('assigned_area', $midwife['assigned_area']);
    SessionManager::set('name', $midwife['full_name']);
    
    // Set remember me cookie if requested
    if ($rememberMe) {
        $token = Utils::generateToken();
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
        
        // Store token in database (you might want to create a remember_tokens table)
        // For demo purposes, we'll skip this implementation
    }
    
    // Prepare user data for response (exclude sensitive information)
    $userData = [
        'midwife_id' => $midwife['midwife_id'],
        'employee_id' => $midwife['employee_id'],
        'name' => $midwife['full_name'],
        'email' => $midwife['email'],
        'phone' => $midwife['phone'],
        'assigned_area' => $midwife['assigned_area'],
        'moh_office' => $midwife['moh_office'],
        'supervisor' => $midwife['supervisor_name'],
        'hire_date' => $midwife['hire_date'],
        'experience_years' => $midwife['experience_years'],
        'profile_image' => $midwife['profile_image']
    ];
    
    // Log successful login
    Utils::logActivity('midwife', $midwife['midwife_id'], 'successful_login');
    
    // Send welcome notification
    Utils::sendNotification(
        'midwife', 
        $midwife['midwife_id'], 
        'Welcome Back!', 
        'You have successfully logged into MidConnect.',
        'success'
    );
    
    // Return success response
    Utils::jsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => $userData,
        'session_timeout' => 3600 // 1 hour in seconds
    ]);
    
} catch (Exception $e) {
    error_log("Midwife login error: " . $e->getMessage());
    Utils::jsonResponse([
        'success' => false, 
        'message' => 'Internal server error'
    ], 500);
}
?>