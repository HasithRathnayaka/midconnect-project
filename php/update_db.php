<?php
/**
 * Update SQLite Database Structure
 * Adds missing columns to midwives table
 */

require_once 'config.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "Adding missing columns to midwives table...<br>";
    
    // Add login_attempts column
    try {
        $conn->exec("ALTER TABLE midwives ADD COLUMN login_attempts INTEGER DEFAULT 0");
        echo "Added login_attempts column.<br>";
    } catch (PDOException $e) {
        echo "login_attempts column might already exist: " . $e->getMessage() . "<br>";
    }
    
    // Add locked_until column
    try {
        $conn->exec("ALTER TABLE midwives ADD COLUMN locked_until DATETIME DEFAULT NULL");
        echo "Added locked_until column.<br>";
    } catch (PDOException $e) {
        echo "locked_until column might already exist: " . $e->getMessage() . "<br>";
    }
    
    echo "Database update completed.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
