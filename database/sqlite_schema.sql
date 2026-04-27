-- MidConnect SQLite Database Schema
-- Adapted from MySQL schema

PRAGMA foreign_keys = ON;

-- ========================================
-- USER MANAGEMENT TABLES
-- ========================================

-- MOH Admin Users Table
CREATE TABLE IF NOT EXISTS moh_admins (
    admin_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    phone TEXT,
    moh_office TEXT NOT NULL,
    position TEXT DEFAULT 'MOH Officer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    is_active BOOLEAN DEFAULT 1,
    login_attempts INTEGER DEFAULT 0,
    locked_until DATETIME
);

-- Midwives Table
CREATE TABLE IF NOT EXISTS midwives (
    midwife_id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    phone TEXT,
    assigned_area TEXT NOT NULL,
    moh_office TEXT NOT NULL,
    supervisor_id INTEGER,
    hire_date DATE NOT NULL,
    birth_date DATE,
    address TEXT,
    qualification TEXT,
    experience_years INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    status TEXT DEFAULT 'active', -- ENUM('active',...) -> TEXT
    profile_image TEXT,
    FOREIGN KEY (supervisor_id) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
);

-- ========================================
-- ACTIVITY TRACKING TABLES
-- ========================================

-- Activity Types Reference Table
CREATE TABLE IF NOT EXISTS activity_types (
    type_id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_code TEXT UNIQUE NOT NULL,
    type_name TEXT NOT NULL,
    description TEXT,
    default_duration INTEGER DEFAULT 60, -- in minutes
    requires_patient BOOLEAN DEFAULT 1,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default activity types
INSERT OR IGNORE INTO activity_types (type_code, type_name, description, default_duration, requires_patient) VALUES
('HOME_VISIT', 'Home Visit', 'Routine home visit for maternal or child health checkup', 45, 1),
('CLINIC_VISIT', 'Clinic Visit', 'Clinic-based consultation and examination', 30, 1),
('VACCINATION', 'Vaccination', 'Immunization services for children and adults', 15, 1),
('COUNSELING', 'Counseling Session', 'Health education and counseling services', 60, 1),
('HEALTH_EDUCATION', 'Health Education', 'Community health education programs', 120, 0),
('EMERGENCY_RESPONSE', 'Emergency Response', 'Emergency medical response and care', 90, 1),
('PRENATAL_CARE', 'Prenatal Care', 'Antenatal care and monitoring', 45, 1),
('POSTNATAL_CARE', 'Postnatal Care', 'Postpartum care and follow-up', 45, 1),
('FAMILY_PLANNING', 'Family Planning', 'Family planning counseling and services', 60, 1),
('NUTRITION_COUNSELING', 'Nutrition Counseling', 'Nutritional guidance and support', 45, 1);

-- Main Activities Table
CREATE TABLE IF NOT EXISTS activities (
    activity_id INTEGER PRIMARY KEY AUTOINCREMENT,
    midwife_id INTEGER NOT NULL,
    activity_type_id INTEGER NOT NULL,
    patient_name TEXT,
    patient_age INTEGER,
    patient_contact TEXT,
    activity_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME,
    duration_minutes INTEGER,
    location TEXT NOT NULL,
    description TEXT,
    observations TEXT,
    recommendations TEXT,
    follow_up_required BOOLEAN DEFAULT 0,
    follow_up_date DATE NULL,
    priority_level TEXT DEFAULT 'normal',
    status TEXT DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    gps_latitude REAL NULL,
    gps_longitude REAL NULL,
    weather_conditions TEXT,
    transport_method TEXT,
    FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    FOREIGN KEY (activity_type_id) REFERENCES activity_types(type_id)
);

CREATE INDEX IF NOT EXISTS idx_midwife_date ON activities (midwife_id, activity_date);
CREATE INDEX IF NOT EXISTS idx_activity_date ON activities (activity_date);
CREATE INDEX IF NOT EXISTS idx_status ON activities (status);

-- ========================================
-- SCHEDULING TABLES
-- ========================================

-- Schedules Table
CREATE TABLE IF NOT EXISTS schedules (
    schedule_id INTEGER PRIMARY KEY AUTOINCREMENT,
    midwife_id INTEGER NOT NULL,
    activity_type_id INTEGER NOT NULL,
    scheduled_date DATE NOT NULL,
    start_time TIME NOT NULL,
    estimated_end_time TIME NOT NULL,
    patient_name TEXT,
    location TEXT NOT NULL,
    description TEXT,
    priority_level TEXT DEFAULT 'normal',
    status TEXT DEFAULT 'scheduled',
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_activity_id INTEGER NULL,
    cancellation_reason TEXT,
    notes TEXT,
    FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    FOREIGN KEY (activity_type_id) REFERENCES activity_types(type_id),
    FOREIGN KEY (created_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (completed_activity_id) REFERENCES activities(activity_id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_midwife_schedule_date ON schedules (midwife_id, scheduled_date);
CREATE INDEX IF NOT EXISTS idx_schedule_status ON schedules (status);


-- ========================================
-- PATIENT MANAGEMENT TABLES
-- ========================================

-- Patients Table
CREATE TABLE IF NOT EXISTS patients (
    patient_id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_name TEXT NOT NULL,
    birth_date DATE,
    gender TEXT NOT NULL, -- ENUM -> TEXT
    contact_number TEXT,
    address TEXT,
    emergency_contact TEXT,
    emergency_phone TEXT,
    medical_history TEXT,
    allergies TEXT,
    current_medications TEXT,
    assigned_midwife_id INTEGER,
    registration_date DATE DEFAULT (CURRENT_DATE),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1,
    notes TEXT,
    FOREIGN KEY (assigned_midwife_id) REFERENCES midwives(midwife_id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_patient_name ON patients (patient_name);
CREATE INDEX IF NOT EXISTS idx_assigned_midwife ON patients (assigned_midwife_id);

-- Patient Visit History
CREATE TABLE IF NOT EXISTS patient_visits (
    visit_id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    activity_id INTEGER NOT NULL,
    visit_type TEXT,
    vital_signs TEXT, -- JSON stored as TEXT
    diagnosis TEXT,
    treatment_given TEXT,
    medications_prescribed TEXT,
    next_visit_date DATE,
    visit_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(activity_id) ON DELETE CASCADE
);

-- ========================================
-- REPORTING TABLES
-- ========================================

-- Reports Table
CREATE TABLE IF NOT EXISTS reports (
    report_id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_type TEXT NOT NULL,
    generated_by_midwife_id INTEGER,
    generated_by_admin_id INTEGER,
    report_period_start DATE NOT NULL,
    report_period_end DATE NOT NULL,
    title TEXT NOT NULL,
    summary TEXT,
    total_activities INTEGER DEFAULT 0,
    total_patients_served INTEGER DEFAULT 0,
    total_duration_hours REAL DEFAULT 0,
    report_data TEXT, -- JSON stored as TEXT
    status TEXT DEFAULT 'draft',
    submitted_at DATETIME NULL,
    reviewed_by INTEGER,
    reviewed_at DATETIME NULL,
    reviewer_comments TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by_midwife_id) REFERENCES midwives(midwife_id) ON DELETE SET NULL,
    FOREIGN KEY (generated_by_admin_id) REFERENCES moh_admins(admin_id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_report_period ON reports (report_period_start, report_period_end);
CREATE INDEX IF NOT EXISTS idx_report_status ON reports (status);

-- ========================================
-- PERFORMANCE TRACKING TABLES
-- ========================================

-- Performance Metrics Table
CREATE TABLE IF NOT EXISTS performance_metrics (
    metric_id INTEGER PRIMARY KEY AUTOINCREMENT,
    midwife_id INTEGER NOT NULL,
    metric_date DATE NOT NULL,
    total_activities INTEGER DEFAULT 0,
    home_visits INTEGER DEFAULT 0,
    clinic_visits INTEGER DEFAULT 0,
    vaccinations INTEGER DEFAULT 0,
    counseling_sessions INTEGER DEFAULT 0,
    emergency_responses INTEGER DEFAULT 0,
    patients_served INTEGER DEFAULT 0,
    total_duration_hours REAL DEFAULT 0,
    completion_rate REAL DEFAULT 0,
    punctuality_score REAL DEFAULT 0,
    patient_satisfaction REAL DEFAULT 0,
    goals_met INTEGER DEFAULT 0,
    goals_total INTEGER DEFAULT 0,
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS unique_midwife_date ON performance_metrics (midwife_id, metric_date);
CREATE INDEX IF NOT EXISTS idx_performance_date ON performance_metrics (metric_date);

-- Performance Goals Table
CREATE TABLE IF NOT EXISTS performance_goals (
    goal_id INTEGER PRIMARY KEY AUTOINCREMENT,
    midwife_id INTEGER,
    moh_office TEXT,
    goal_type TEXT NOT NULL,
    metric_name TEXT NOT NULL,
    target_value REAL NOT NULL,
    current_value REAL DEFAULT 0,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    description TEXT,
    created_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1,
    FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES moh_admins(admin_id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_goal_period ON performance_goals (period_start, period_end);

-- ========================================
-- NOTIFICATIONS AND COMMUNICATION
-- ========================================

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INTEGER PRIMARY KEY AUTOINCREMENT,
    recipient_type TEXT NOT NULL,
    recipient_id INTEGER NOT NULL,
    sender_type TEXT NOT NULL,
    sender_id INTEGER,
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    notification_type TEXT DEFAULT 'info',
    is_read BOOLEAN DEFAULT 0,
    action_required BOOLEAN DEFAULT 0,
    action_url TEXT,
    priority TEXT DEFAULT 'normal',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    expires_at DATETIME NULL
);

CREATE INDEX IF NOT EXISTS idx_recipient ON notifications (recipient_type, recipient_id, is_read);
CREATE INDEX IF NOT EXISTS idx_created_at_notif ON notifications (created_at);

-- ========================================
-- SYSTEM CONFIGURATION TABLES
-- ========================================

-- System Settings Table
CREATE TABLE IF NOT EXISTS system_settings (
    setting_id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type TEXT DEFAULT 'string',
    description TEXT,
    is_editable BOOLEAN DEFAULT 1,
    updated_by INTEGER,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
);

-- Insert default system settings
INSERT OR IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
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
CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_type TEXT NOT NULL,
    user_id INTEGER,
    action TEXT NOT NULL,
    table_name TEXT,
    record_id INTEGER,
    old_values TEXT, -- JSON stored as TEXT
    new_values TEXT, -- JSON stored as TEXT
    ip_address TEXT,
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_user ON audit_logs (user_type, user_id);
CREATE INDEX IF NOT EXISTS idx_action_date ON audit_logs (action, created_at);
CREATE INDEX IF NOT EXISTS idx_table_record ON audit_logs (table_name, record_id);

-- ========================================
-- VIEWS FOR COMMON QUERIES
-- ========================================

-- View: Midwife Activity Summary
CREATE VIEW IF NOT EXISTS view_midwife_activity_summary AS
SELECT 
    m.midwife_id,
    m.full_name,
    m.employee_id,
    m.assigned_area,
    COUNT(a.activity_id) as total_activities,
    COUNT(CASE WHEN DATE(a.activity_date) = DATE('now', 'localtime') THEN 1 END) as activities_today,
    COUNT(CASE WHEN DATE(a.activity_date) >= DATE('now', '-7 days', 'localtime') THEN 1 END) as activities_this_week,
    AVG(a.duration_minutes) as avg_duration_minutes,
    MAX(a.activity_date) as last_activity_date,
    m.status as midwife_status
FROM midwives m
LEFT JOIN activities a ON m.midwife_id = a.midwife_id
GROUP BY m.midwife_id, m.full_name, m.employee_id, m.assigned_area, m.status;

-- View: Daily Activity Report
CREATE VIEW IF NOT EXISTS view_daily_activities AS
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
CREATE VIEW IF NOT EXISTS view_performance_dashboard AS
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
WHERE pm.metric_date >= DATE('now', '-30 days') OR pm.metric_date IS NULL;

-- ========================================
-- HOME VISITS TABLE
-- ========================================

CREATE TABLE IF NOT EXISTS home_visits (
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
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_home_visit_midwife ON home_visits (midwife_id);
CREATE INDEX IF NOT EXISTS idx_home_visit_date ON home_visits (visit_date);
CREATE INDEX IF NOT EXISTS idx_home_visit_status ON home_visits (status);
CREATE INDEX IF NOT EXISTS idx_home_visit_area ON home_visits (duty_area);
