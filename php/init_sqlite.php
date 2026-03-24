<?php
/**
 * Initialize SQLite Database
 * Run this script once to create the database and sample data
 */

// Define database path
$dbDir = __DIR__ . '/../database';
$dbFile = $dbDir . '/midconnect.db';

// Create database directory if it doesn't exist
if (!file_exists($dbDir)) {
    mkdir($dbDir, 0777, true);
}

try {
    // Connect to SQLite database (will be created if it doesn't exist)
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database created successfully.<br>";
    
    // Read schema file
    $schemaFile = $dbDir . '/sqlite_schema.sql';
    if (!file_exists($schemaFile)) {
        die("Schema file not found at $schemaFile");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Split SQL by semicolons to execute statements individually (basic splitting)
    // Note: This is a simple splitter and might fail on robust SQL with semicolons in strings
    // But for our schema it should be fine
    
    // Execute schema
    $pdo->exec($sql);
    echo "Schema imported successfully.<br>";
    
    // Insert sample data
    echo "Inserting sample data...<br>";
    
    // Sample Admins
    // Password is 'password' hashed with bcrypt
    $password = password_hash('password', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO moh_admins (username, password_hash, full_name, email, moh_office) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin001', $password, 'Dr. Sarah Johnson', 'sarah.johnson@health.gov.lk', 'MOH Colombo 01']);
    $stmt->execute(['admin002', $password, 'Dr. Kumara Silva', 'kumara.silva@health.gov.lk', 'MOH Gampaha']);
    
    // Sample Midwives
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO midwives (employee_id, password_hash, full_name, email, phone, assigned_area, moh_office, hire_date, supervisor_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['MW001', $password, 'Madhavi Perera', 'madhavi.perera@midwife.lk', '+94771234567', 'Colombo Central', 'MOH Colombo 01', '2020-01-15', 1]);
    $stmt->execute(['MW002', $password, 'Kumari Silva', 'kumari.silva@midwife.lk', '+94771234568', 'Colombo North', 'MOH Colombo 01', '2019-03-20', 1]);
    
    // Sample Patients
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO patients (patient_name, birth_date, gender, contact_number, address, assigned_midwife_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Kamala Silva', '1995-05-15', 'female', '+94712345678', 'No. 45, Galle Road, Colombo 03', 1]);
    
    // Sample Activities
    $stmt = $pdo->prepare("INSERT INTO activities (midwife_id, activity_type_id, patient_name, activity_date, start_time, end_time, duration_minutes, location, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, 1, 'Kamala Silva', '2024-11-25', '09:00', '09:45', 45, 'No. 45, Galle Road, Colombo 03', 'Routine prenatal checkup', 'completed']);
    
    echo "Sample data inserted successfully!<br>";
    echo "You can now login with:<br>";
    echo "<b>Admin:</b> admin001 / password<br>";
    echo "<b>Midwife:</b> MW001 / password<br>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
