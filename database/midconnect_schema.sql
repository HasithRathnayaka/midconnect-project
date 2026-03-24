-- MidConnect Database Schema
-- Digital Midwife Activity Tracking System
-- Created: November 2024

-- Create database
CREATE DATABASE IF NOT EXISTS midconnect_db;
USE midconnect_db;

-- ========================================
-- USER MANAGEMENT TABLES
-- ========================================

-- MOH Admin Users Table
CREATE TABLE moh_admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    moh_office VARCHAR(100) NOT NULL,
    position VARCHAR(50) DEFAULT 'MOH Officer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL
);

-- Midwives Table
CREATE TABLE midwives (
    midwife_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    assigned_area VARCHAR(100) NOT NULL,
    moh_office VARCHAR(100) NOT NULL,
    supervisor_id INT,
    hire_date DATE NOT NULL,
    birth_date DATE,
    address TEXT,
    qualification VARCHAR(200),
    experience_years INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'on_leave', 'suspended') DEFAULT 'active',
    profile_image VARCHAR(255),
    FOREIGN KEY (supervisor_id) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
);

-- ========================================
-- ACTIVITY TRACKING TABLES
-- ========================================

-- Activity Types Reference Table
CREATE TABLE activity_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_code VARCHAR(20) UNIQUE NOT NULL,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    default_duration INT DEFAULT 60, -- in minutes
    requires_patient BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default activity types
INSERT INTO activity_types (type_code, type_name, description, default_duration, requires_patient) VALUES
('HOME_VISIT', 'Home Visit', 'Routine home visit for maternal or child health checkup', 45, TRUE),
('CLINIC_VISIT', 'Clinic Visit', 'Clinic-based consultation and examination', 30, TRUE),
('VACCINATION', 'Vaccination', 'Immunization services for children and adults', 15, TRUE),
('COUNSELING', 'Counseling Session', 'Health education and counseling services', 60, TRUE),
('HEALTH_EDUCATION', 'Health Education', 'Community health education programs', 120, FALSE),
('EMERGENCY_RESPONSE', 'Emergency Response', 'Emergency medical response and care', 90, TRUE),
('PRENATAL_CARE', 'Prenatal Care', 'Antenatal care and monitoring', 45, TRUE),
('POSTNATAL_CARE', 'Postnatal Care', 'Postpartum care and follow-up', 45, TRUE),
('FAMILY_PLANNING', 'Family Planning', 'Family planning counseling and services', 60, TRUE),
('NUTRITION_COUNSELING', 'Nutrition Counseling', 'Nutritional guidance and support', 45, TRUE);

-- Main Activities Table
CREATE TABLE activities (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    activity_type_id INT NOT NULL,
    patient_name VARCHAR(100),
    patient_age INT,
    patient_contact VARCHAR(15),
    activity_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME,
    duration_minutes INT,
    location VARCHAR(200) NOT NULL,
    description TEXT,
    observations TEXT,
    recommendations TEXT,
    follow_up_required BOOLEAN DEFAULT FALSE,
    follow_up_date DATE NULL,
    priority_level ENUM('normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    gps_latitude DECIMAL(10, 8) NULL,
    gps_longitude DECIMAL(11, 8) NULL,
    weather_conditions VARCHAR(50),
    transport_method VARCHAR(50),
    FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    FOREIGN KEY (activity_type_id) REFERENCES activity_types(type_id),
    INDEX idx_midwife_date (midwife_id, activity_date),
    INDEX idx_activity_date (activity_date),
    INDEX idx_status (status)
);

-- ========================================
-- SCHEDULING TABLES
-- ========================================

-- Schedules Table
CREATE TABLE schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    activity_type_id INT NOT NULL,
    scheduled_date DATE NOT NULL,
    start_time TIME NOT NULL,
    estimated_end_time TIME NOT NULL,
    patient_name VARCHAR(100),
    location VARCHAR(200) NOT NULL,
    description TEXT,
    priority_level ENUM('normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    created_by INT, -- admin_id who created the schedule
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_activity_id INT NULL, -- Links to activities table when completed
    cancellation_reason TEXT,
    notes TEXT,
    FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    FOREIGN KEY (activity_type_id) REFERENCES activity_types(type_id),
    FOREIGN KEY (created_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (completed_activity_id) REFERENCES activities(activity_id) ON DELETE SET NULL,
    INDEX idx_midwife_schedule_date (midwife_id, scheduled_date),
    INDEX idx_schedule_status (status)
);

-- ========================================
-- PATIENT MANAGEMENT TABLES
-- ========================================

-- Patients Table
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    birth_date DATE,
    gender ENUM('male', 'female', 'other') NOT NULL,
    contact_number VARCHAR(15),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(15),
    medical_history TEXT,
    allergies TEXT,
    current_medications TEXT,
    assigned_midwife_id INT,
    registration_date DATE DEFAULT (CURRENT_DATE),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    FOREIGN KEY (assigned_midwife_id) REFERENCES midwives(midwife_id) ON DELETE SET NULL,
    INDEX idx_patient_name (patient_name),
    INDEX idx_assigned_midwife (assigned_midwife_id)
);

-- Patient Visit History (Links patients to activities)
CREATE TABLE patient_visits (
    visit_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    activity_id INT NOT NULL,
    visit_type VARCHAR(50),
    vital_signs JSON, -- Store vital signs as JSON
    diagnosis TEXT,
    treatment_given TEXT,
    medications_prescribed TEXT,
    next_visit_date DATE,
    visit_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(activity_id) ON DELETE CASCADE
);

-- ========================================
-- REPORTING TABLES
-- ========================================

-- Reports Table
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_type ENUM('daily', 'weekly', 'monthly', 'quarterly', 'annual', 'custom') NOT NULL,
    generated_by_midwife_id INT,
    generated_by_admin_id INT,
    report_period_start DATE NOT NULL,
    report_period_end DATE NOT NULL,
    title VARCHAR(200) NOT NULL,
    summary TEXT,
    total_activities INT DEFAULT 0,
    total_patients_served INT DEFAULT 0,
    total_duration_hours DECIMAL(6,2) DEFAULT 0,
    report_data JSON, -- Store detailed report data as JSON
    status ENUM('draft', 'submitted', 'reviewed', 'approved', 'rejected') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    reviewer_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by_midwife_id) REFERENCES midwives(midwife_id) ON DELETE SET NULL,
    FOREIGN KEY (generated_by_admin_id) REFERENCES moh_admins(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL,
    INDEX idx_report_period (report_period_start, report_period_end),
    INDEX idx_report_status (status)
);

-- ========================================
-- PERFORMANCE TRACKING TABLES
-- ========================================

-- Performance Metrics Table
CREATE TABLE performance_metrics (
    metric_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    metric_date DATE NOT NULL,
    total_activities INT DEFAULT 0,
    home_visits INT DEFAULT 0,
    clinic_visits INT DEFAULT 0,
    vaccinations INT DEFAULT 0,
    counseling_sessions INT DEFAULT 0,
    emergency_responses INT DEFAULT 0,
    patients_served INT DEFAULT 0,
    total_duration_hours DECIMAL(6,2) DEFAULT 0,
    completion_rate DECIMAL(5,2) DEFAULT 0, -- Percentage
    punctuality_score DECIMAL(5,2) DEFAULT 0, -- Percentage
    patient_satisfaction DECIMAL(3,2) DEFAULT 0, -- Out of 5
    goals_met INT DEFAULT 0,
    goals_total INT DEFAULT 0,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    UNIQUE KEY unique_midwife_date (midwife_id, metric_date),
    INDEX idx_performance_date (metric_date)
);

-- Performance Goals Table
CREATE TABLE performance_goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT,
    moh_office VARCHAR(100), -- If goal applies to entire office
    goal_type ENUM('individual', 'office', 'district') NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    target_value DECIMAL(10,2) NOT NULL,
    current_value DECIMAL(10,2) DEFAULT 0,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES moh_admins(admin_id) ON DELETE CASCADE,
    INDEX idx_goal_period (period_start, period_end)
);

-- ========================================
-- NOTIFICATIONS AND COMMUNICATION
-- ========================================

-- Notifications Table
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_type ENUM('midwife', 'admin') NOT NULL,
    recipient_id INT NOT NULL,
    sender_type ENUM('system', 'midwife', 'admin') NOT NULL,
    sender_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('info', 'warning', 'success', 'error', 'reminder') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    action_required BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(500),
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    INDEX idx_recipient (recipient_type, recipient_id, is_read),
    INDEX idx_created_at (created_at)
);

-- ========================================
-- SYSTEM CONFIGURATION TABLES
-- ========================================

-- System Settings Table
CREATE TABLE system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_editable BOOLEAN DEFAULT TRUE,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('system_name', 'MidConnect', 'string', 'System name displayed in the application'),
('max_login_attempts', '5', 'number', 'Maximum number of login attempts before account lockout'),
('session_timeout', '3600', 'number', 'Session timeout in seconds'),
('report_retention_days', '365', 'number', 'Number of days to retain reports'),
('backup_frequency', 'daily', 'string', 'Database backup frequency'),
('notification_retention_days', '30', 'number', 'Number of days to keep read notifications'),
('working_hours_start', '08:00', 'string', 'Standard working hours start time'),
('working_hours_end', '17:00', 'string', 'Standard working hours end time'),
('emergency_contact', '+94 11 2 691 757', 'string', 'Emergency contact number for system issues');

-- Audit Log Table
CREATE TABLE audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('midwife', 'admin', 'system') NOT NULL,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_type, user_id),
    INDEX idx_action_date (action, created_at),
    INDEX idx_table_record (table_name, record_id)
);

-- ========================================
-- VIEWS FOR COMMON QUERIES
-- ========================================

-- View: Midwife Activity Summary
CREATE VIEW view_midwife_activity_summary AS
SELECT 
    m.midwife_id,
    m.full_name,
    m.employee_id,
    m.assigned_area,
    COUNT(a.activity_id) as total_activities,
    COUNT(CASE WHEN a.activity_date = CURRENT_DATE THEN 1 END) as activities_today,
    COUNT(CASE WHEN a.activity_date >= CURRENT_DATE - INTERVAL 7 DAY THEN 1 END) as activities_this_week,
    AVG(a.duration_minutes) as avg_duration_minutes,
    MAX(a.activity_date) as last_activity_date,
    m.status as midwife_status
FROM midwives m
LEFT JOIN activities a ON m.midwife_id = a.midwife_id
GROUP BY m.midwife_id, m.full_name, m.employee_id, m.assigned_area, m.status;

-- View: Daily Activity Report
CREATE VIEW view_daily_activities AS
SELECT 
    a.activity_date,
    m.full_name as midwife_name,
    m.assigned_area,
    at.type_name as activity_type,
    a.patient_name,
    a.location,
    a.duration_minutes,
    a.status,
    a.priority_level
FROM activities a
JOIN midwives m ON a.midwife_id = m.midwife_id
JOIN activity_types at ON a.activity_type_id = at.type_id
ORDER BY a.activity_date DESC, a.start_time ASC;

-- View: Performance Dashboard
CREATE VIEW view_performance_dashboard AS
SELECT 
    m.midwife_id,
    m.full_name,
    m.assigned_area,
    pm.metric_date,
    pm.total_activities,
    pm.completion_rate,
    pm.punctuality_score,
    pm.patient_satisfaction,
    CASE 
        WHEN pm.completion_rate >= 95 THEN 'Excellent'
        WHEN pm.completion_rate >= 85 THEN 'Good'
        WHEN pm.completion_rate >= 75 THEN 'Satisfactory'
        ELSE 'Needs Improvement'
    END as performance_rating
FROM midwives m
LEFT JOIN performance_metrics pm ON m.midwife_id = pm.midwife_id
WHERE pm.metric_date >= CURRENT_DATE - INTERVAL 30 DAY OR pm.metric_date IS NULL;

-- ========================================
-- STORED PROCEDURES
-- ========================================

DELIMITER //

-- Procedure: Calculate Daily Performance Metrics
CREATE PROCEDURE CalculateDailyMetrics(IN target_date DATE)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE midwife_id_var INT;
    DECLARE midwife_cursor CURSOR FOR 
        SELECT midwife_id FROM midwives WHERE status = 'active';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN midwife_cursor;
    read_loop: LOOP
        FETCH midwife_cursor INTO midwife_id_var;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Insert or update daily metrics
        INSERT INTO performance_metrics (
            midwife_id, 
            metric_date,
            total_activities,
            home_visits,
            clinic_visits,
            vaccinations,
            counseling_sessions,
            emergency_responses,
            patients_served,
            total_duration_hours
        )
        SELECT 
            midwife_id_var,
            target_date,
            COUNT(*) as total_activities,
            COUNT(CASE WHEN at.type_code = 'HOME_VISIT' THEN 1 END) as home_visits,
            COUNT(CASE WHEN at.type_code = 'CLINIC_VISIT' THEN 1 END) as clinic_visits,
            COUNT(CASE WHEN at.type_code = 'VACCINATION' THEN 1 END) as vaccinations,
            COUNT(CASE WHEN at.type_code = 'COUNSELING' THEN 1 END) as counseling_sessions,
            COUNT(CASE WHEN at.type_code = 'EMERGENCY_RESPONSE' THEN 1 END) as emergency_responses,
            COUNT(DISTINCT a.patient_name) as patients_served,
            ROUND(SUM(a.duration_minutes) / 60, 2) as total_duration_hours
        FROM activities a
        JOIN activity_types at ON a.activity_type_id = at.type_id
        WHERE a.midwife_id = midwife_id_var 
        AND a.activity_date = target_date 
        AND a.status = 'completed'
        ON DUPLICATE KEY UPDATE
            total_activities = VALUES(total_activities),
            home_visits = VALUES(home_visits),
            clinic_visits = VALUES(clinic_visits),
            vaccinations = VALUES(vaccinations),
            counseling_sessions = VALUES(counseling_sessions),
            emergency_responses = VALUES(emergency_responses),
            patients_served = VALUES(patients_served),
            total_duration_hours = VALUES(total_duration_hours);

    END LOOP;
    CLOSE midwife_cursor;
END //

DELIMITER ;

-- ========================================
-- TRIGGERS
-- ========================================

DELIMITER //

-- Trigger: Auto-update performance metrics when activity is completed
CREATE TRIGGER after_activity_update
AFTER UPDATE ON activities
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        CALL CalculateDailyMetrics(NEW.activity_date);
    END IF;
END //

-- Trigger: Create audit log entry for important table changes
CREATE TRIGGER audit_midwives_changes
AFTER UPDATE ON midwives
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (
        user_type, action, table_name, record_id, 
        old_values, new_values, created_at
    ) VALUES (
        'system', 'UPDATE', 'midwives', NEW.midwife_id,
        JSON_OBJECT(
            'full_name', OLD.full_name,
            'email', OLD.email,
            'status', OLD.status,
            'assigned_area', OLD.assigned_area
        ),
        JSON_OBJECT(
            'full_name', NEW.full_name,
            'email', NEW.email,
            'status', NEW.status,
            'assigned_area', NEW.assigned_area
        ),
        NOW()
    );
END //

DELIMITER ;

-- ========================================
-- SAMPLE DATA FOR TESTING
-- ========================================

-- Insert sample MOH admin
INSERT INTO moh_admins (username, password_hash, full_name, email, moh_office) VALUES
('admin001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Sarah Johnson', 'sarah.johnson@health.gov.lk', 'MOH Colombo 01'),
('admin002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Kumara Silva', 'kumara.silva@health.gov.lk', 'MOH Gampaha');

-- Insert sample midwives
INSERT INTO midwives (employee_id, password_hash, full_name, email, phone, assigned_area, moh_office, hire_date, supervisor_id) VALUES
('MW001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Madhavi Perera', 'madhavi.perera@health.gov.lk', '+94771234567', 'Colombo Central', 'MOH Colombo 01', '2020-01-15', 1),
('MW002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kumari Silva', 'kumari.silva@health.gov.lk', '+94771234568', 'Colombo North', 'MOH Colombo 01', '2019-03-20', 1),
('MW003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Anura Fernando', 'anura.fernando@health.gov.lk', '+94771234569', 'Colombo South', 'MOH Colombo 01', '2021-06-10', 1);

-- Insert sample patients
INSERT INTO patients (patient_name, birth_date, gender, contact_number, address, assigned_midwife_id) VALUES
('Kamala Silva', '1995-05-15', 'female', '+94712345678', 'No. 45, Galle Road, Colombo 03', 1),
('Anura Fernando', '1990-08-22', 'female', '+94712345679', 'No. 78, Kandy Road, Colombo 07', 1),
('Dilani Jayawardene', '1988-12-10', 'female', '+94712345680', 'No. 123, Main Street, Colombo 05', 2);

-- Insert sample activities
INSERT INTO activities (midwife_id, activity_type_id, patient_name, activity_date, start_time, end_time, duration_minutes, location, description, status) VALUES
(1, 1, 'Kamala Silva', '2024-11-25', '09:00:00', '09:45:00', 45, 'No. 45, Galle Road, Colombo 03', 'Routine prenatal checkup - 28 weeks pregnant', 'completed'),
(1, 3, 'Baby Fernando', '2024-11-25', '11:00:00', '11:15:00', 15, 'Clinic Center', 'BCG vaccination for 2-month-old', 'completed'),
(2, 2, 'Dilani Jayawardene', '2024-11-25', '14:00:00', '14:30:00', 30, 'Clinic Center', 'Postnatal checkup - 6 weeks postpartum', 'completed');

-- ========================================
-- INDEXES FOR OPTIMIZATION
-- ========================================

-- Additional indexes for better performance
CREATE INDEX idx_activities_date_status ON activities(activity_date, status);
CREATE INDEX idx_midwives_area_status ON midwives(assigned_area, status);
CREATE INDEX idx_notifications_unread ON notifications(recipient_type, recipient_id, is_read, created_at);
CREATE INDEX idx_performance_metrics_composite ON performance_metrics(midwife_id, metric_date, completion_rate);

-- ========================================
-- END OF SCHEMA
-- ========================================

-- Display creation summary
SELECT 'Database schema created successfully!' as Status;
SELECT COUNT(*) as 'Total Tables Created' FROM information_schema.tables WHERE table_schema = 'midconnect_db';
SELECT COUNT(*) as 'Sample Midwives Added' FROM midwives;
SELECT COUNT(*) as 'Sample Activities Added' FROM activities;