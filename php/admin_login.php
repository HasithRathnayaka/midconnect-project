<?php
/**
 * MidConnect - Admin Login Handler
 * Handles MOH admin authentication
 */

require_once 'config.php';

// Temporary debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    $username = Utils::sanitizeInput($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $mohOffice = Utils::sanitizeInput($data['moh_office'] ?? '');
    $secureSession = isset($data['secure_session']) && $data['secure_session'];
    
    // Validate required fields
    if (empty($username) || empty($password) || empty($mohOffice)) {
        Utils::jsonResponse([
            'success' => false, 
            'message' => 'Username, password, and MOH office are required'
        ], 400);
    }
    
    // Get database connection
    $db = Database::getInstance();
    
    // Find admin by username and MOH office
    $sql = "SELECT * FROM moh_admins 
            WHERE username = ? AND moh_office = ? AND is_active = 1";
    
    $admin = $db->fetch($sql, [$username, $mohOffice]);
    
    if (!$admin) {
        // Log failed login attempt
        Utils::logActivity('admin', null, 'failed_login', [
            'username' => $username,
            'moh_office' => $mohOffice,
            'reason' => 'user_not_found'
        ]);
        
        Utils::jsonResponse([
            'success' => false, 
            'message' => 'Invalid username, password, or MOH office'
        ], 401);
    }
    
    // Check if account is locked
    if ($admin['locked_until'] && new DateTime($admin['locked_until']) > new DateTime()) {
        Utils::jsonResponse([
            'success' => false, 
            'message' => 'Account is temporarily locked. Please contact system administrator.'
        ], 423);
    }
    
    // Verify password
    if (!Utils::verifyPassword($password, $admin['password_hash'])) {
        // Increment login attempts
        $attempts = $admin['login_attempts'] + 1;
        $lockUntil = null;
        
        // Lock account after 5 failed attempts
        if ($attempts >= 5) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Send security notification to all admins in the same office
            $notificationSql = "SELECT admin_id FROM moh_admins WHERE moh_office = ? AND is_active = 1";
            $admins = $db->fetchAll($notificationSql, [$mohOffice]);
            
            foreach ($admins as $notifyAdmin) {
                Utils::sendNotification(
                    'admin',
                    $notifyAdmin['admin_id'],
                    'Security Alert',
                    "Multiple failed login attempts detected for user: {$username}",
                    'warning'
                );
            }
        }
        
        // Update login attempts
        $updateSql = "UPDATE moh_admins SET login_attempts = ?, locked_until = ? WHERE admin_id = ?";
        $db->execute($updateSql, [$attempts, $lockUntil, $admin['admin_id']]);
        
        // Log failed login attempt
        Utils::logActivity('admin', $admin['admin_id'], 'failed_login', [
            'username' => $username,
            'moh_office' => $mohOffice,
            'reason' => 'invalid_password',
            'attempts' => $attempts
        ]);
        
        $message = $attempts >= 5 
            ? 'Account locked due to security reasons. Please contact system administrator.'
            : 'Invalid username, password, or MOH office';
            
        Utils::jsonResponse([
            'success' => false, 
            'message' => $message
        ], 401);
    }
    
    // Successful login - reset login attempts and update last login
    $db->execute(
        "UPDATE moh_admins SET login_attempts = 0, locked_until = NULL, last_login = date('now', 'localtime') WHERE admin_id = ?", 
        [$admin['admin_id']]
    );
    
    // Create session with enhanced security for secure sessions
    SessionManager::regenerate();
    SessionManager::set('user_type', 'admin');
    SessionManager::set('user_id', $admin['admin_id']);
    SessionManager::set('username', $admin['username']);
    SessionManager::set('moh_office', $admin['moh_office']);
    SessionManager::set('secure_session', $secureSession);
    
    // Set shorter timeout for secure sessions (shared computers)
    if ($secureSession) {
        SessionManager::set('session_timeout', 1800); // 30 minutes
    } else {
        SessionManager::set('session_timeout', 3600); // 1 hour
    }
    
    // Get admin statistics for dashboard
    $statsSql = "SELECT 
                    (SELECT COUNT(*) FROM midwives WHERE moh_office = ? AND status = 'active') as active_midwives,
                    (SELECT COUNT(*) FROM activities a 
                     JOIN midwives m ON a.midwife_id = m.midwife_id 
                     WHERE m.moh_office = ? AND DATE(a.activity_date) = date('now', 'localtime')) as activities_today,
                    (SELECT COUNT(*) FROM reports r 
                     JOIN midwives m ON r.generated_by_midwife_id = m.midwife_id 
                     WHERE m.moh_office = ? AND r.status = 'pending') as pending_reports";
    
    $stats = $db->fetch($statsSql, [$mohOffice, $mohOffice, $mohOffice]);
    
    // Prepare user data for response (exclude sensitive information)
    $userData = [
        'admin_id' => $admin['admin_id'],
        'username' => $admin['username'],
        'name' => $admin['full_name'],
        'email' => $admin['email'],
        'phone' => $admin['phone'],
        'moh_office' => $admin['moh_office'],
        'position' => $admin['position'],
        'created_at' => $admin['created_at'],
        'stats' => $stats,
        'permissions' => [
            'view_all_activities' => true,
            'manage_midwives' => true,
            'generate_reports' => true,
            'system_settings' => $admin['position'] === 'System Administrator'
        ]
    ];
    
    // Log successful login
    Utils::logActivity('admin', $admin['admin_id'], 'successful_login', [
        'moh_office' => $mohOffice,
        'secure_session' => $secureSession
    ]);
    
    // Send welcome notification
    Utils::sendNotification(
        'admin', 
        $admin['admin_id'], 
        'Admin Login Successful', 
        "Welcome back to MidConnect admin panel. Session type: " . ($secureSession ? 'Secure' : 'Standard'),
        'success'
    );
    
    // Get recent notifications
    $notificationsSql = "SELECT * FROM notifications 
                        WHERE recipient_type = 'admin' AND recipient_id = ? 
                        ORDER BY created_at DESC LIMIT 10";
    $notifications = $db->fetchAll($notificationsSql, [$admin['admin_id']]);
    
    // Return success response
header("Location: ../admin/dashboard.html");
exit();
    
} catch (Exception $e) {
    error_log("Admin login error: " . $e->getMessage());
    Utils::jsonResponse([
        'success' => false, 
        'message' => 'Internal server error'
    ], 500);
}
?>