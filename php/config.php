<?php
/**
 * MidConnect Database Configuration
 * Database connection settings and utilities
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Database configuration
    private $host = 'localhost';
    private $dbname = 'midconnect_db';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Create database connection
     */
    private function connect() {
        try {
            // Database configuration for SQLite
            $dbPath = __DIR__ . '/../database/midconnect.db';
            $dsn = "sqlite:$dbPath";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->exec('PRAGMA foreign_keys = ON;');
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        // Check if connection is still alive
        try {
            $this->connection->query('SELECT 1');
        } catch (PDOException $e) {
            // Reconnect if connection is lost
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Execute prepared statement
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Database operation failed");
        }
    }
    
    /**
     * Fetch single row
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
}

/**
 * Base Model class
 */
abstract class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by ID
     */
    public function find($id, $primaryKey = 'id') {
        $sql = "SELECT * FROM {$this->table} WHERE {$primaryKey} = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Find all records with optional conditions
     */
    public function findAll($conditions = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->db->execute($sql, array_values($data));
        return $this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    public function update($id, $data, $primaryKey = 'id') {
        $fields = array_keys($data);
        $setClauses = array_map(function($field) {
            return "{$field} = ?";
        }, $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . 
               " WHERE {$primaryKey} = ?";
        
        $params = array_values($data);
        $params[] = $id;
        
        $stmt = $this->db->execute($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete record
     */
    public function delete($id, $primaryKey = 'id') {
        $sql = "DELETE FROM {$this->table} WHERE {$primaryKey} = ?";
        $stmt = $this->db->execute($sql, [$id]);
        return $stmt->rowCount();
    }
}

/**
 * Utility class for common functions
 */
class Utils {
    /**
     * Generate secure password hash
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Sri Lankan format)
     */
    public static function isValidPhone($phone) {
        $cleanPhone = preg_replace('/\s+/', '', $phone);
        return preg_match('/^(\+94|0)?[1-9]\d{8}$/', $cleanPhone);
    }
    
    /**
     * Generate JSON response
     */
    public static function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Log activity
     */
    public static function logActivity($userType, $userId, $action, $details = null) {
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO audit_logs (user_type, user_id, action, table_name, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, date('now', 'localtime'))";
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $db->execute($sql, [
                $userType,
                $userId,
                $action,
                $details['table'] ?? null,
                $ipAddress,
                $userAgent
            ]);
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
    
    /**
     * Send notification
     */
    public static function sendNotification($recipientType, $recipientId, $title, $message, $type = 'info') {
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO notifications (recipient_type, recipient_id, sender_type, title, message, notification_type, created_at) 
                    VALUES (?, ?, 'system', ?, ?, ?, date('now', 'localtime'))";
            
            $db->execute($sql, [$recipientType, $recipientId, $title, $message, $type]);
        } catch (Exception $e) {
            error_log("Failed to send notification: " . $e->getMessage());
        }
    }
}

/**
 * Session Management
 */
class SessionManager {
    private static $sessionTimeout = 3600; // 1 hour
    
    /**
     * Start session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set custom session path
            $sessionPath = __DIR__ . '/../sessions';
            if (!file_exists($sessionPath)) {
                mkdir($sessionPath, 0777, true);
            }
            session_save_path($sessionPath);
            session_start();
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > self::$sessionTimeout) {
                self::destroy();
                return false;
            }
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Set session data
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session data
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session exists
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session data
     */
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    
    /**
     * Regenerate session ID
     */
    public static function regenerate() {
        session_regenerate_id(true);
    }
}

/**
 * CORS and Security Headers
 */
function setSecurityHeaders() {
    // CORS headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// Set security headers for all requests
setSecurityHeaders();

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

// Start session
SessionManager::start();
?>