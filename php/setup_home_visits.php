<?php
$dbFile = __DIR__ . '/../database/midconnect.db';

try {
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE TABLE IF NOT EXISTS home_visits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        midwife_id INTEGER NOT NULL,
        patient_name TEXT NOT NULL,
        contact_number TEXT,
        address TEXT,
        visit_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME,
        duration_minutes INTEGER,
        duty_area TEXT NOT NULL,
        visit_type TEXT DEFAULT 'routine',
        priority TEXT DEFAULT 'normal',
        reason TEXT,
        status TEXT DEFAULT 'scheduled',
        notes TEXT,
        completed_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "home_visits table created successfully!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
