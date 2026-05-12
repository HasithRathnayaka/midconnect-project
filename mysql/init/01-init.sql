-- MidConnect MySQL initialization
-- Preserves existing MidConnect tables/columns and adds module tables for:
-- Vaccination Management, Triposha Distribution, Counseling Sessions,
-- Health Education Sessions, Maternal & Child Care, and Midwife Profile extensions.
-- MySQL runs this file only when the mysql_data volume is empty.

CREATE DATABASE IF NOT EXISTS midconnect_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE midconnect_db;

SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS view_performance_dashboard;
DROP VIEW IF EXISTS view_daily_activities;
DROP VIEW IF EXISTS view_midwife_activity_summary;

-- New module tables must be dropped before parent tables
DROP TABLE IF EXISTS midwife_system_preferences;
DROP TABLE IF EXISTS midwife_professional_memberships;
DROP TABLE IF EXISTS midwife_trainings;
DROP TABLE IF EXISTS midwife_qualifications;
DROP TABLE IF EXISTS child_care_records;
DROP TABLE IF EXISTS maternal_care_records;
DROP TABLE IF EXISTS health_education_sessions;
DROP TABLE IF EXISTS counseling_sessions;
DROP TABLE IF EXISTS triposha_distribution_records;
DROP TABLE IF EXISTS triposha_inventory;
DROP TABLE IF EXISTS vaccination_overdue_followups;
DROP TABLE IF EXISTS vaccination_records;
DROP TABLE IF EXISTS vaccine_inventory;

DROP TABLE IF EXISTS home_visits;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS performance_goals;
DROP TABLE IF EXISTS performance_metrics;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS patient_visits;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS activity_types;
DROP TABLE IF EXISTS midwives;
DROP TABLE IF EXISTS moh_admins;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE moh_admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    moh_office VARCHAR(100) NOT NULL,
    position VARCHAR(50) DEFAULT 'MOH Officer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE midwives (
    midwife_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    assigned_area VARCHAR(100) NOT NULL,
    moh_office VARCHAR(100) NOT NULL,
    supervisor_id INT NULL,
    hire_date DATE NOT NULL,
    birth_date DATE NULL,
    address TEXT,
    qualification VARCHAR(255),
    experience_years INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    status VARCHAR(20) DEFAULT 'active',
    profile_image VARCHAR(255),
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    CONSTRAINT fk_midwives_supervisor FOREIGN KEY (supervisor_id) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_code VARCHAR(30) UNIQUE NOT NULL,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    default_duration INT DEFAULT 60,
    requires_patient TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activities (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    activity_type_id INT NOT NULL,
    patient_name VARCHAR(100),
    patient_age INT,
    patient_contact VARCHAR(20),
    activity_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NULL,
    duration_minutes INT,
    location VARCHAR(200) NOT NULL,
    description TEXT,
    observations TEXT,
    recommendations TEXT,
    follow_up_required TINYINT(1) DEFAULT 0,
    follow_up_date DATE NULL,
    priority_level VARCHAR(20) DEFAULT 'normal',
    status VARCHAR(30) DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    gps_latitude DECIMAL(10,8) NULL,
    gps_longitude DECIMAL(11,8) NULL,
    weather_conditions VARCHAR(50),
    transport_method VARCHAR(50),
    CONSTRAINT fk_activities_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    CONSTRAINT fk_activities_type FOREIGN KEY (activity_type_id) REFERENCES activity_types(type_id),
    INDEX idx_midwife_date (midwife_id, activity_date),
    INDEX idx_activity_date (activity_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    priority_level VARCHAR(20) DEFAULT 'normal',
    status VARCHAR(30) DEFAULT 'scheduled',
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_activity_id INT NULL,
    cancellation_reason TEXT,
    notes TEXT,
    CONSTRAINT fk_schedules_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    CONSTRAINT fk_schedules_type FOREIGN KEY (activity_type_id) REFERENCES activity_types(type_id),
    CONSTRAINT fk_schedules_created_by FOREIGN KEY (created_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL,
    CONSTRAINT fk_schedules_completed_activity FOREIGN KEY (completed_activity_id) REFERENCES activities(activity_id) ON DELETE SET NULL,
    INDEX idx_midwife_schedule_date (midwife_id, scheduled_date),
    INDEX idx_schedule_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    birth_date DATE NULL,
    gender VARCHAR(20) NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    medical_history TEXT,
    allergies TEXT,
    current_medications TEXT,
    assigned_midwife_id INT NULL,
    registration_date DATE DEFAULT (CURRENT_DATE),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    notes TEXT,
    CONSTRAINT fk_patients_midwife FOREIGN KEY (assigned_midwife_id) REFERENCES midwives(midwife_id) ON DELETE SET NULL,
    INDEX idx_patient_name (patient_name),
    INDEX idx_assigned_midwife (assigned_midwife_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vaccination Management
CREATE TABLE vaccine_inventory (
    vaccine_id INT AUTO_INCREMENT PRIMARY KEY,
    vaccine_name VARCHAR(100) NOT NULL,
    vaccine_code VARCHAR(50) UNIQUE NOT NULL,
    category VARCHAR(50) DEFAULT 'general',
    stock_quantity INT DEFAULT 0,
    minimum_stock_level INT DEFAULT 10,
    batch_number VARCHAR(100),
    expiry_date DATE NULL,
    storage_temperature VARCHAR(50),
    supplier VARCHAR(150),
    status VARCHAR(30) DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vaccine_code (vaccine_code),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vaccination_records (
    vaccination_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NULL,
    midwife_id INT NOT NULL,
    vaccine_id INT NOT NULL,
    duty_area VARCHAR(100) NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    patient_age VARCHAR(50) NULL,
    mother_name VARCHAR(100) NULL,
    pregnancy_status VARCHAR(100) NULL,
    condition_note VARCHAR(150) NULL,
    contact_number VARCHAR(20),
    address TEXT,
    vaccination_date DATE NOT NULL,
    vaccination_time TIME NOT NULL,
    duration_minutes INT DEFAULT 15,
    location VARCHAR(200) NOT NULL,
    dose_number VARCHAR(50),
    batch_number VARCHAR(100),
    next_due_date DATE NULL,
    priority VARCHAR(20) DEFAULT 'normal',
    status VARCHAR(30) DEFAULT 'scheduled',
    notes TEXT,
    administered_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vaccination_patient FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE SET NULL,
    CONSTRAINT fk_vaccination_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    CONSTRAINT fk_vaccination_vaccine FOREIGN KEY (vaccine_id) REFERENCES vaccine_inventory(vaccine_id),
    INDEX idx_vaccination_midwife (midwife_id),
    INDEX idx_vaccination_area (duty_area),
    INDEX idx_vaccination_date (vaccination_date),
    INDEX idx_vaccination_status (status),
    INDEX idx_vaccination_patient (patient_id),
    INDEX idx_vaccination_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vaccination_overdue_followups (
    overdue_id INT AUTO_INCREMENT PRIMARY KEY,
    vaccination_id INT NULL,
    midwife_id INT NOT NULL,
    duty_area VARCHAR(100) NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    mother_name VARCHAR(100) NULL,
    contact_number VARCHAR(20),
    vaccine_details VARCHAR(255) NOT NULL,
    due_date DATE NOT NULL,
    days_overdue INT DEFAULT 0,
    priority VARCHAR(20) DEFAULT 'urgent',
    status VARCHAR(30) DEFAULT 'pending',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_overdue_vaccination FOREIGN KEY (vaccination_id) REFERENCES vaccination_records(vaccination_id) ON DELETE SET NULL,
    CONSTRAINT fk_overdue_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_overdue_midwife (midwife_id),
    INDEX idx_overdue_area (duty_area),
    INDEX idx_overdue_due_date (due_date),
    INDEX idx_overdue_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Triposha Distribution Management
CREATE TABLE triposha_inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    duty_area VARCHAR(100) NOT NULL,
    inventory_month DATE NOT NULL,
    packets_received INT DEFAULT 0,
    packets_left_previous INT DEFAULT 0,
    packets_distributed INT DEFAULT 0,
    pregnant_packets INT DEFAULT 0,
    lactating_packets INT DEFAULT 0,
    children_packets INT DEFAULT 0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_triposha_inventory_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    UNIQUE KEY unique_triposha_area_month (midwife_id, duty_area, inventory_month),
    INDEX idx_triposha_inventory_area (duty_area),
    INDEX idx_triposha_inventory_month (inventory_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE triposha_distribution_records (
    distribution_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    duty_area VARCHAR(100) NOT NULL,
    distribution_date DATE NOT NULL,
    beneficiary_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    packets INT NOT NULL DEFAULT 1,
    category VARCHAR(50) NOT NULL,
    status VARCHAR(30) DEFAULT 'completed',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_triposha_distribution_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_triposha_distribution_midwife (midwife_id),
    INDEX idx_triposha_distribution_area (duty_area),
    INDEX idx_triposha_distribution_date (distribution_date),
    INDEX idx_triposha_distribution_category (category),
    INDEX idx_triposha_distribution_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Counseling Sessions
CREATE TABLE counseling_sessions (
    counseling_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    session_datetime DATETIME NOT NULL,
    duration_mins INT NOT NULL DEFAULT 30,
    client_ref VARCHAR(100) NOT NULL,
    focus VARCHAR(50) NOT NULL,
    location_type VARCHAR(30) DEFAULT 'clinic',
    notes TEXT NOT NULL,
    followup VARCHAR(30) DEFAULT 'no',
    followup_date DATE NULL,
    referral_details TEXT NULL,
    status VARCHAR(30) DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_counseling_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_counseling_midwife (midwife_id),
    INDEX idx_counseling_datetime (session_datetime),
    INDEX idx_counseling_focus (focus),
    INDEX idx_counseling_followup (followup)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Health Education Sessions
CREATE TABLE health_education_sessions (
    health_education_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    session_date DATE NOT NULL,
    venue VARCHAR(200) NOT NULL,
    topic VARCHAR(50) NOT NULL,
    audience VARCHAR(50) DEFAULT 'mixed',
    attendees INT DEFAULT 0,
    duration_mins INT DEFAULT 45,
    materials VARCHAR(255),
    outcomes TEXT,
    status VARCHAR(30) DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_health_education_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_health_education_midwife (midwife_id),
    INDEX idx_health_education_date (session_date),
    INDEX idx_health_education_topic (topic),
    INDEX idx_health_education_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Maternal and Child Care
CREATE TABLE maternal_care_records (
    mother_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    duty_area VARCHAR(100) NOT NULL,
    mother_name VARCHAR(100) NOT NULL,
    age INT NULL,
    category VARCHAR(30) NOT NULL,
    weeks_pregnant INT NULL,
    baby_age VARCHAR(50) NULL,
    breastfeeding_status VARCHAR(100) NULL,
    delivery_date DATE NULL,
    delivery_type VARCHAR(50) NULL,
    recovery_status VARCHAR(100) NULL,
    last_visit DATE NULL,
    next_appointment DATE NULL,
    risk_level VARCHAR(50) DEFAULT 'Low Risk',
    support_level VARCHAR(50),
    health_status VARCHAR(50),
    contact_number VARCHAR(20),
    address TEXT,
    notes TEXT,
    status VARCHAR(30) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_maternal_care_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_maternal_midwife (midwife_id),
    INDEX idx_maternal_area (duty_area),
    INDEX idx_maternal_category (category),
    INDEX idx_maternal_status (status),
    INDEX idx_maternal_next_appointment (next_appointment)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE child_care_records (
    child_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    duty_area VARCHAR(100) NOT NULL,
    child_name VARCHAR(100) NOT NULL,
    mother_name VARCHAR(100),
    child_category VARCHAR(30) NOT NULL,
    date_of_birth DATE NULL,
    age_label VARCHAR(50),
    birth_weight DECIMAL(5,2) NULL,
    current_weight DECIMAL(5,2) NULL,
    height_cm DECIMAL(5,2) NULL,
    school VARCHAR(150),
    last_checkup DATE NULL,
    development_status VARCHAR(50),
    health_status VARCHAR(50) DEFAULT 'Healthy',
    notes TEXT,
    status VARCHAR(30) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_child_care_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_child_midwife (midwife_id),
    INDEX idx_child_area (duty_area),
    INDEX idx_child_category (child_category),
    INDEX idx_child_status (status),
    INDEX idx_child_last_checkup (last_checkup)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Midwife Profile Extensions
CREATE TABLE midwife_qualifications (
    qualification_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    qualification_title VARCHAR(150) NOT NULL,
    institution VARCHAR(150),
    completed_year VARCHAR(20),
    grade VARCHAR(50),
    valid_until DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_qualification_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_qualification_midwife (midwife_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE midwife_trainings (
    training_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    training_title VARCHAR(150) NOT NULL,
    provider VARCHAR(150),
    completed_date DATE NULL,
    duration_hours INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_training_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_training_midwife (midwife_id),
    INDEX idx_training_date (completed_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE midwife_professional_memberships (
    membership_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    organization VARCHAR(150) NOT NULL,
    license_number VARCHAR(100),
    member_since VARCHAR(20),
    valid_until DATE NULL,
    status VARCHAR(30) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_membership_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_membership_midwife (midwife_id),
    INDEX idx_membership_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE midwife_system_preferences (
    preference_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    email_notifications TINYINT(1) DEFAULT 1,
    sms_alerts TINYINT(1) DEFAULT 1,
    push_notifications TINYINT(1) DEFAULT 0,
    weekly_report TINYINT(1) DEFAULT 1,
    language_code VARCHAR(10) DEFAULT 'en',
    date_format VARCHAR(30) DEFAULT 'dd/mm/yyyy',
    time_format VARCHAR(10) DEFAULT '24h',
    dashboard_theme VARCHAR(20) DEFAULT 'light',
    session_timeout_minutes INT DEFAULT 60,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_preferences_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    UNIQUE KEY unique_midwife_preferences (midwife_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE patient_visits (
    visit_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    activity_id INT NOT NULL,
    visit_type VARCHAR(50),
    vital_signs JSON NULL,
    diagnosis TEXT,
    treatment_given TEXT,
    medications_prescribed TEXT,
    next_visit_date DATE NULL,
    visit_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_patient_visits_patient FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    CONSTRAINT fk_patient_visits_activity FOREIGN KEY (activity_id) REFERENCES activities(activity_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(30) NOT NULL,
    generated_by_midwife_id INT NULL,
    generated_by_admin_id INT NULL,
    report_period_start DATE NOT NULL,
    report_period_end DATE NOT NULL,
    title VARCHAR(200) NOT NULL,
    summary TEXT,
    total_activities INT DEFAULT 0,
    total_patients_served INT DEFAULT 0,
    total_duration_hours DECIMAL(6,2) DEFAULT 0,
    report_data JSON NULL,
    status VARCHAR(30) DEFAULT 'draft',
    submitted_at DATETIME NULL,
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    reviewer_comments TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_midwife FOREIGN KEY (generated_by_midwife_id) REFERENCES midwives(midwife_id) ON DELETE SET NULL,
    CONSTRAINT fk_reports_admin FOREIGN KEY (generated_by_admin_id) REFERENCES moh_admins(admin_id) ON DELETE SET NULL,
    CONSTRAINT fk_reports_reviewer FOREIGN KEY (reviewed_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    completion_rate DECIMAL(5,2) DEFAULT 0,
    punctuality_score DECIMAL(5,2) DEFAULT 0,
    patient_satisfaction DECIMAL(3,2) DEFAULT 0,
    goals_met INT DEFAULT 0,
    goals_total INT DEFAULT 0,
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_perf_metrics_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    UNIQUE KEY unique_midwife_date (midwife_id, metric_date),
    INDEX idx_metric_date (metric_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE performance_goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NULL,
    moh_office VARCHAR(100) NULL,
    goal_type VARCHAR(30) NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    target_value DECIMAL(10,2) NOT NULL,
    current_value DECIMAL(10,2) DEFAULT 0,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    description TEXT,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    CONSTRAINT fk_perf_goals_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    CONSTRAINT fk_perf_goals_created_by FOREIGN KEY (created_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_type VARCHAR(20) NOT NULL,
    recipient_id INT NOT NULL,
    sender_type VARCHAR(20) NULL,
    sender_id INT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    notification_type VARCHAR(30) DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    action_required TINYINT(1) DEFAULT 0,
    action_url VARCHAR(255),
    priority VARCHAR(20) DEFAULT 'normal',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    expires_at DATETIME NULL,
    INDEX idx_recipient (recipient_type, recipient_id),
    INDEX idx_unread (is_read, created_at),
    INDEX idx_notification_type (notification_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'string',
    description TEXT,
    is_editable TINYINT(1) DEFAULT 1,
    updated_by INT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_settings_updated_by FOREIGN KEY (updated_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_type VARCHAR(20) NOT NULL,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_type, user_id),
    INDEX idx_action_date (action, created_at),
    INDEX idx_table_record (table_name, record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE home_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    midwife_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    visit_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NULL,
    duration_minutes INT,
    duty_area VARCHAR(100) NOT NULL,
    visit_type VARCHAR(30) DEFAULT 'routine',
    priority VARCHAR(20) DEFAULT 'normal',
    reason TEXT,
    status VARCHAR(30) DEFAULT 'scheduled',
    notes TEXT,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_home_visits_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
    INDEX idx_home_visit_midwife (midwife_id),
    INDEX idx_home_visit_date (visit_date),
    INDEX idx_home_visit_status (status),
    INDEX idx_home_visit_area (duty_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Existing seed data
INSERT INTO moh_admins (admin_id, username, password_hash, full_name, email, phone, moh_office, position, created_at, updated_at, last_login, is_active, login_attempts, locked_until) VALUES
(1, 'admin001', '$2y$10$4cmkYFt/eElLt/T2aK65meoXLgCQGIUaGO5XMlyK3cN5jWBHs8d.G', 'Dr. Sarah Johnson', 'sarah.johnson@health.gov.lk', NULL, 'MOH Colombo 01', 'MOH Officer', '2026-02-11 07:15:53', '2026-02-11 07:15:53', '2026-02-11', 1, 0, NULL),
(2, 'admin002', '$2y$10$4cmkYFt/eElLt/T2aK65meoXLgCQGIUaGO5XMlyK3cN5jWBHs8d.G', 'Dr. Kumara Silva', 'kumara.silva@health.gov.lk', NULL, 'MOH Gampaha', 'MOH Officer', '2026-02-11 07:15:53', '2026-02-11 07:15:53', NULL, 1, 0, NULL);

INSERT INTO midwives (midwife_id, employee_id, password_hash, full_name, email, phone, assigned_area, moh_office, supervisor_id, hire_date, birth_date, address, qualification, experience_years, created_at, updated_at, last_login, status, profile_image, login_attempts, locked_until) VALUES
(1, 'MW001', '$2y$10$oKgGJdcx8NmEAwzqOBYREeeO/7sEAqiVMzfsa1lDIBW50Wj1pc./C', 'Madhavi Jayawardene', 'madhavi.j@midconnect.lk', '+94712345678', 'Uduthuththiripitiya', 'MOH Uduthuththiripitiya', NULL, '2020-01-15', '1980-06-03', '57/1/A Pitipana, Homagama', 'Diploma in Midwifery', 5, '2026-04-30 17:55:45', '2026-04-30 17:55:45', NULL, 'active', '../images/profile picture.png', 0, NULL),
(2, 'MW002', '$2y$10$OJojUc5WkzbVYQuMatJN8.YJ72EWKDyt0BX1JvnYW7ZPmKIc8guI6', 'Sandya Thennakoon', 'sandya.t@midconnect.lk', '+94712345679', 'Kahabilihena', 'MOH Kahabilihena', NULL, '2020-02-20', NULL, NULL, NULL, 0, '2026-04-30 17:55:45', '2026-04-30 17:55:45', NULL, 'active', NULL, 0, NULL),
(3, 'MW003', '$2y$10$tCgy3xfk0/i4uMvhEDTzgOGJs76EJ.euER/V.mC8JER3u.kqJ/n/K', 'Kumari Hathurusingha', 'kumari.h@midconnect.lk', '+94712345680', 'Opathella', 'MOH Opathella', NULL, '2020-03-10', NULL, NULL, NULL, 0, '2026-04-30 17:55:45', '2026-04-30 17:55:45', NULL, 'active', NULL, 0, NULL),
(4, 'MW004', '$2y$10$11aLkQOYlwcnuS4JX5bOlOpPJWWfzMMLGGZErmoRiSXvvASwJzzX.', 'Anusha Fernando', 'anusha.f@midconnect.lk', '+94712345681', 'Ambalangoda', 'MOH Ambalangoda', NULL, '2020-04-05', NULL, NULL, NULL, 0, '2026-04-30 17:55:45', '2026-04-30 17:55:45', NULL, 'active', NULL, 0, NULL),
(7, 'MW005', '$2y$10$TZw91Tkdfecud01Hpy3bSu2uKhJnKmAJDQ1sUZXM4JvESOP2R.FlO', 'Sunil Fernando', 'sunil@midconnect.com', '0771234569', 'Opathella', 'MOH Colombo 01', NULL, '2026-03-30', NULL, NULL, NULL, 0, '2026-03-29 22:30:59', '2026-03-29 22:30:59', NULL, 'active', NULL, 0, NULL),
(8, 'MW006', '$2y$10$tWNvGrNmM45YFWMzSkRvcuFMsWblcbkpwSNY0xFO0iHjpZ0UTgv1a', 'Chandra Jayawardene', 'chandra@midconnect.com', '0771234570', 'Ambalangoda', 'MOH Colombo 01', NULL, '2026-03-30', NULL, NULL, NULL, 0, '2026-03-29 22:30:59', '2026-03-29 22:30:59', NULL, 'active', NULL, 0, NULL),
(9, 'MW007', '$2y$10$6xEmXYaPCbbmp0JwA72c6./08c2g/UmXv5XwcE0YAaERXqhzfnKdG', 'Rashmi Bandara', 'rashmi.bandara@midconnect.gov.lk', '0773525192', 'Ambalangoda', 'MOH Colombo 01', NULL, '2023-01-01', NULL, NULL, NULL, 0, '2026-03-30 07:19:31', '2026-03-30 07:19:31', NULL, 'active', NULL, 0, NULL),
(10, 'MW008', '$2y$10$8jAI.MdVqZhr7cJa9hatce1Zz3ZKeusWKboAPXxtxDxURKLWarVWS', 'Dilani Wijeratne', 'dilani.wijeratne@midconnect.gov.lk', '0777325138', 'Ambalangoda', 'MOH Colombo 01', NULL, '2023-01-01', NULL, NULL, NULL, 0, '2026-03-30 07:19:31', '2026-03-30 07:19:31', NULL, 'active', NULL, 0, NULL);

INSERT INTO activity_types (type_id, type_code, type_name, description, default_duration, requires_patient, is_active, created_at) VALUES
(1, 'HOME_VISIT', 'Home Visit', 'Routine home visit for maternal or child health checkup', 45, 1, 1, '2026-02-11 07:15:52'),
(2, 'CLINIC_VISIT', 'Clinic Visit', 'Clinic-based consultation and examination', 30, 1, 1, '2026-02-11 07:15:52'),
(3, 'VACCINATION', 'Vaccination', 'Immunization services for children and adults', 15, 1, 1, '2026-02-11 07:15:52'),
(4, 'COUNSELING', 'Counseling Session', 'Health education and counseling services', 60, 1, 1, '2026-02-11 07:15:52'),
(5, 'HEALTH_EDUCATION', 'Health Education', 'Community health education programs', 120, 0, 1, '2026-02-11 07:15:52'),
(6, 'EMERGENCY_RESPONSE', 'Emergency Response', 'Emergency medical response and care', 90, 1, 1, '2026-02-11 07:15:52'),
(7, 'PRENATAL_CARE', 'Prenatal Care', 'Antenatal care and monitoring', 45, 1, 1, '2026-02-11 07:15:52'),
(8, 'POSTNATAL_CARE', 'Postnatal Care', 'Postpartum care and follow-up', 45, 1, 1, '2026-02-11 07:15:52'),
(9, 'FAMILY_PLANNING', 'Family Planning', 'Family planning counseling and services', 60, 1, 1, '2026-02-11 07:15:52'),
(10, 'NUTRITION_COUNSELING', 'Nutrition Counseling', 'Nutritional guidance and support', 45, 1, 1, '2026-02-11 07:15:52'),
(21, 'MEETING', 'Meeting', 'Professional meetings and conferences', 60, 0, 1, '2026-03-29 22:24:25');

INSERT INTO patients (patient_id, patient_name, birth_date, gender, contact_number, address, assigned_midwife_id, registration_date, created_at, updated_at, is_active) VALUES
(1, 'Kamala Silva', '1995-05-15', 'female', '+94712345678', 'No. 45, Galle Road, Colombo 03', NULL, '2026-02-11', '2026-02-11 07:15:53', '2026-02-11 07:15:53', 1),
(4, 'Mrs. Nirmala Fernando', '1990-05-15', 'F', '+94711234567', 'No. 45, Galle Road, Uduthuththiripitiya', 1, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1),
(5, 'Mrs. Kamani Wickramasinghe', '1988-08-22', 'F', '+94711234568', 'No. 78, Temple Road, Uduthuththiripitiya', 1, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1),
(6, 'Mrs. Sandya Peris', '1992-03-10', 'F', '+94711234569', 'No. 23, Flower Road, Uduthuththiripitiya', 1, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1),
(10, 'Mrs. Lakshmi Perera', '1993-01-25', 'F', '+94711234573', 'No. 34, Main Road, Kahabilihena', 2, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1),
(15, 'Mrs. Kumudini Jayawardena', '1988-10-30', 'F', '+94711234578', 'No. 23, Coastal Road, Opathella', 3, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1),
(21, 'Mrs. Anura Silva', '1985-12-03', 'F', '+94711234584', 'No. 34, Port Road, Ambalangoda', 4, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1);

INSERT INTO activities (activity_id, midwife_id, activity_type_id, patient_name, patient_age, activity_date, start_time, end_time, location, description, priority_level, status, created_at, updated_at) VALUES
(70, 7, 1, 'Priyanka Jayasinghe', 25, '2024-03-30', '08:30', '10:00', 'Patient Residence', 'Postnatal care', 'normal', 'completed', '2026-03-30 07:19:31', '2026-03-30 07:19:31'),
(71, 8, 2, 'Ruwani Perera', 30, '2024-03-29', '13:00', '14:00', 'Opathella Clinic', 'Pregnancy checkup', 'normal', 'completed', '2026-03-30 07:19:31', '2026-03-30 07:19:31'),
(72, 9, 3, 'Dinushi Fernando', 6, '2024-03-30', '10:00', '10:30', 'Ambalangoda Hospital', 'MMR vaccine', 'normal', 'completed', '2026-03-30 07:19:31', '2026-03-30 07:19:31'),
(73, 10, 21, NULL, NULL, '2024-03-29', '09:00', '11:00', 'Ambalangoda MOH Office', 'Monthly staff meeting', 'normal', 'completed', '2026-03-30 07:19:31', '2026-03-30 07:19:31');

INSERT INTO home_visits (id, midwife_id, patient_name, contact_number, address, visit_date, start_time, end_time, duration_minutes, duty_area, visit_type, priority, reason, status, notes, completed_at, created_at, updated_at) VALUES
(1, 1, 'Mrs. Nirmala Fernando', '+94 77 123 4567', 'No. 45, Galle Road, Mount Lavinia', '2026-05-01', '09:00', NULL, 45, 'uduthuththiripitiya', 'postnatal', 'high', 'Postnatal Visit - Day 3', 'scheduled', 'Follow-up on breastfeeding issues and jaundice monitoring', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27'),
(2, 1, 'Mrs. Kamani Wickramasinghe', '+94 77 234 5678', 'No. 78, Temple Road, Dehiwala', '2026-05-01', '10:30', NULL, 30, 'uduthuththiripitiya', 'antenatal', 'high', 'Antenatal Visit - 32 weeks', 'scheduled', 'Routine checkup, monitor blood pressure and fetal growth', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27'),
(3, 1, 'Mrs. Sandya Peris', '+94 77 345 6789', 'No. 23, Flower Road, Colombo 7', '2026-05-01', '14:00', NULL, 40, 'uduthuththiripitiya', 'postnatal', 'normal', 'Postnatal Visit - Day 14', 'scheduled', 'Check healing progress, discuss family planning', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27'),
(4, 1, 'Mrs. Priyani Silva', '+94 77 456 7890', 'No. 12, Lake Road, Uduthuththiripitiya', '2026-04-30', '08:30', '09:15', 45, 'uduthuththiripitiya', 'antenatal', 'normal', 'Antenatal Visit - 28 weeks', 'completed', 'Normal progression, all vitals stable', '2026-04-30 09:15:00', '2026-05-01 14:44:27', '2026-05-01 14:44:27'),
(5, 1, 'Mrs. Dilani Jayasinghe', '+94 77 567 8901', 'No. 19, School Lane, Uduthuththiripitiya', '2026-04-30', '11:00', '11:35', 35, 'uduthuththiripitiya', 'routine', 'normal', 'Routine mother and child wellness check', 'completed', 'Nutrition advice given, next clinic date confirmed', '2026-04-30 11:35:00', '2026-05-01 14:44:27', '2026-05-01 14:44:27'),
(6, 1, 'Mrs. Anoma Kumari', '+94 77 678 9012', 'No. 05, Hospital Road, Kahabilihena', '2026-05-01', '09:30', NULL, 40, 'kahabilihena', 'antenatal', 'normal', 'Antenatal Visit - 24 weeks', 'scheduled', 'Routine antenatal assessment', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27'),
(7, 1, 'Mrs. Ruwani Perera', '+94 77 789 0123', 'No. 34, Main Street, Kahabilihena', '2026-05-01', '13:15', NULL, 45, 'kahabilihena', 'family-planning', 'normal', 'Family planning follow-up', 'scheduled', 'Counseling and follow-up discussion', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27'),
(8, 1, 'Mrs. Tharuka Silva', '+94 77 890 1234', 'No. 11, Garden Road, Kahabilihena', '2026-04-30', '10:00', '10:40', 40, 'kahabilihena', 'postnatal', 'normal', 'Postnatal review', 'completed', 'Mother and newborn doing well', '2026-04-30 10:40:00', '2026-05-01 14:44:27', '2026-05-01 14:44:27'),
(9, 1, 'Mrs. Himali Fernando', '+94 77 901 2345', 'No. 8, River Lane, Opathella', '2026-05-01', '09:45', NULL, 60, 'opathella', 'emergency', 'urgent', 'High risk pregnancy monitoring', 'scheduled', 'Urgent follow-up for high blood pressure', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27'),
(10, 1, 'Mrs. Malini Dias', '+94 77 012 3456', 'No. 16, Market Road, Ambalangoda', '2026-05-01', '11:45', NULL, 35, 'ambalangoda', 'routine', 'normal', 'Child wellness home visit', 'scheduled', 'Growth monitoring and nutrition guidance', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27');

INSERT INTO notifications (notification_id, recipient_type, recipient_id, sender_type, sender_id, title, message, notification_type, is_read, action_required, action_url, priority, created_at, read_at, expires_at) VALUES
(1, 'admin', 1, 'system', NULL, 'Admin Login Successful', 'Welcome back to MidConnect admin panel. Session type: Standard', 'success', 0, 0, NULL, 'normal', '2026-02-11', NULL, NULL),
(2, 'midwife', 1, 'system', NULL, 'Welcome Back!', 'You have successfully logged into MidConnect.', 'success', 0, 0, NULL, 'normal', '2026-02-11', NULL, NULL),
(3, 'midwife', 1, 'system', NULL, 'Urgent Notice', '[SEED] Monthly reports are due tomorrow at 5PM.', 'warning', 0, 0, NULL, 'normal', '2026-03-29 06:12:12', NULL, NULL),
(4, 'midwife', 1, 'admin', NULL, 'Schedule Updated', '[SEED] Dr. Sarah has updated your clinic roster for next week.', 'info', 0, 0, NULL, 'normal', '2026-03-29 06:12:12', NULL, NULL);

INSERT INTO system_settings (setting_id, setting_key, setting_value, setting_type, description, is_editable, updated_by, updated_at) VALUES
(1, 'system_name', 'MidConnect', 'string', 'System name displayed in the application', 1, NULL, '2026-02-11 07:15:53'),
(2, 'max_login_attempts', '5', 'number', 'Maximum number of login attempts before account lockout', 1, NULL, '2026-02-11 07:15:53'),
(3, 'session_timeout', '3600', 'number', 'Session timeout in seconds', 1, NULL, '2026-02-11 07:15:53'),
(4, 'report_retention_days', '365', 'number', 'Number of days to retain reports', 1, NULL, '2026-02-11 07:15:53'),
(5, 'backup_frequency', 'daily', 'string', 'Database backup frequency', 1, NULL, '2026-02-11 07:15:53'),
(6, 'notification_retention_days', '30', 'number', 'Number of days to keep read notifications', 1, NULL, '2026-02-11 07:15:53'),
(7, 'working_hours_start', '08:00', 'string', 'Standard working hours start time', 1, NULL, '2026-02-11 07:15:53'),
(8, 'working_hours_end', '17:00', 'string', 'Standard working hours end time', 1, NULL, '2026-02-11 07:15:53'),
(9, 'emergency_contact', '+94 11 2 691 757', 'string', 'Emergency contact number for system issues', 1, NULL, '2026-02-11 07:15:53');

INSERT INTO audit_logs (log_id, user_type, user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at) VALUES
(1, 'admin', 1, 'successful_login', NULL, NULL, NULL, NULL, '::1', 'curl/8.16.0', '2026-02-11'),
(2, 'midwife', 1, 'successful_login', NULL, NULL, NULL, NULL, '::1', 'curl/8.16.0', '2026-02-11');

-- New module seed data
INSERT INTO vaccine_inventory (vaccine_id, vaccine_name, vaccine_code, category, stock_quantity, minimum_stock_level, batch_number, expiry_date, storage_temperature, supplier, status) VALUES
(1, 'DPT Vaccine', 'DPT', 'pediatric', 25, 10, 'DPT-2026-001', '2026-06-30', '2-8°C', 'MOH Central Store', 'available'),
(2, 'OPV (Oral Polio)', 'OPV', 'pediatric', 8, 10, 'OPV-2026-001', '2026-03-31', '2-8°C', 'MOH Central Store', 'available'),
(3, 'Hepatitis B', 'HEPB', 'pediatric', 18, 10, 'HEPB-2026-001', '2026-08-31', '2-8°C', 'MOH Central Store', 'available'),
(4, 'Tetanus Toxoid', 'TT', 'maternal', 3, 10, 'TT-2026-001', '2026-04-30', '2-8°C', 'MOH Central Store', 'available'),
(5, 'MMR Vaccine', 'MMR', 'pediatric', 12, 10, 'MMR-2026-001', '2026-07-31', '2-8°C', 'MOH Central Store', 'available'),
(6, 'Influenza Vaccine', 'FLU', 'adult', 6, 10, 'FLU-2026-001', '2026-02-28', '2-8°C', 'MOH Central Store', 'available'),
(7, 'Pneumococcal Vaccine', 'PCV', 'adult', 15, 10, 'PCV-2026-001', '2026-12-31', '2-8°C', 'MOH Central Store', 'available');

INSERT INTO vaccination_records (vaccination_id, patient_id, midwife_id, vaccine_id, duty_area, patient_name, patient_age, mother_name, pregnancy_status, condition_note, contact_number, address, vaccination_date, vaccination_time, duration_minutes, location, dose_number, batch_number, next_due_date, priority, status, notes) VALUES
(1, NULL, 1, 1, 'uduthuththiripitiya', 'Baby Kamal Silva', '2 months', 'Mrs. Nayani Silva', NULL, NULL, '+94 77 555 0123', 'Uduthuththiripitiya', '2026-05-24', '09:00:00', 15, 'Uduthuththiripitiya CHC', 'DPT-1', 'DPT-2026-001', '2026-06-24', 'high', 'scheduled', 'First dose of routine pediatric series. Check weight and temperature.'),
(2, NULL, 1, 4, 'uduthuththiripitiya', 'Mrs. Priyanka Fernando', '28 years', NULL, '28 weeks pregnant', NULL, '+94 71 444 5678', 'Uduthuththiripitiya', '2026-05-24', '10:30:00', 10, 'Uduthuththiripitiya CHC', 'TT-2', 'TT-2026-001', NULL, 'normal', 'scheduled', 'Second TT dose for pregnancy. Check previous reaction history.'),
(3, NULL, 1, 6, 'uduthuththiripitiya', 'Mrs. Kumari Wickramasinghe', '35 years', NULL, NULL, 'Diabetic patient', '+94 76 333 9876', 'Uduthuththiripitiya', '2026-05-24', '14:00:00', 20, 'Uduthuththiripitiya CHC', 'Annual', 'FLU-2026-001', NULL, 'normal', 'scheduled', 'Annual flu vaccine for high-risk patient.'),
(4, NULL, 1, 1, 'kahabilihena', 'Baby Sithum Perera', '4 months', 'Mrs. Dilini Perera', NULL, NULL, NULL, 'Kahabilihena South', '2026-05-24', '10:00:00', 15, 'Kahabilihena RH', 'DPT-2', 'DPT-2026-001', '2026-06-24', 'normal', 'scheduled', 'DPT-2 and OPV-2 due.'),
(5, NULL, 1, 4, 'opathella', 'Mrs. Kumari Jayawardena', '30 years', NULL, '24 weeks pregnant', NULL, NULL, 'Opathella', '2026-05-24', '11:00:00', 10, 'Opathella PHC', 'TT-1', 'TT-2026-001', NULL, 'normal', 'scheduled', 'Maternal TT first dose.'),
(6, NULL, 1, 1, 'ambalangoda', 'Baby Tharusha Bandara', '6 months', 'Mrs. Chamari Bandara', NULL, NULL, NULL, 'Ambalangoda Village', '2026-05-24', '09:30:00', 15, 'Ambalangoda DH', 'DPT-3', 'DPT-2026-001', NULL, 'high', 'scheduled', 'DPT-3, OPV-3, Hep B-3.'),
(7, NULL, 1, 4, 'ambalangoda', 'Mrs. Nimalika Silva', '26 years', NULL, '32 weeks pregnant', NULL, NULL, 'Ambalangoda South', '2026-05-24', '14:00:00', 10, 'Ambalangoda DH', 'TT-2', 'TT-2026-001', NULL, 'normal', 'scheduled', 'Second maternal TT dose.');

INSERT INTO vaccination_overdue_followups (overdue_id, vaccination_id, midwife_id, duty_area, patient_name, mother_name, contact_number, vaccine_details, due_date, days_overdue, priority, status, notes) VALUES
(1, NULL, 1, 'uduthuththiripitiya', 'Baby Nimal Fernando', 'Mrs. Sandya Fernando', '+94 77 123 4567', 'DPT-2, OPV-2, Hep B-2', '2026-05-09', 15, 'urgent', 'pending', 'Call and schedule overdue vaccination.'),
(2, NULL, 1, 'kahabilihena', 'Baby Hasini Perera', 'Mrs. Dilini Perera', '+94 77 555 1111', 'MMR', '2026-05-15', 9, 'high', 'pending', 'Overdue MMR follow-up.'),
(3, NULL, 1, 'ambalangoda', 'Baby Kavindu Silva', 'Mrs. Nimalika Silva', '+94 77 222 3333', 'OPV-3', '2026-05-10', 14, 'urgent', 'pending', 'Needs immediate follow-up.');

INSERT INTO triposha_inventory (inventory_id, midwife_id, duty_area, inventory_month, packets_received, packets_left_previous, packets_distributed, pregnant_packets, lactating_packets, children_packets, notes) VALUES
(1, 1, 'uduthuththiripitiya', '2026-05-01', 150, 25, 98, 35, 42, 21, 'Monthly Triposha stock for Uduthuththiripitiya'),
(2, 1, 'kahabilihena', '2026-05-01', 120, 20, 75, 28, 30, 17, 'Monthly Triposha stock for Kahabilihena'),
(3, 1, 'opathella', '2026-05-01', 90, 15, 56, 20, 22, 14, 'Monthly Triposha stock for Opathella'),
(4, 1, 'ambalangoda', '2026-05-01', 170, 30, 105, 40, 45, 20, 'Monthly Triposha stock for Ambalangoda');

INSERT INTO triposha_distribution_records (distribution_id, midwife_id, duty_area, distribution_date, beneficiary_name, contact_number, address, packets, category, status, notes) VALUES
(1, 1, 'uduthuththiripitiya', '2026-02-05', 'Mrs. K. Silva', NULL, '45, Main Street, Uduthuththiripitiya', 2, 'pregnant', 'completed', NULL),
(2, 1, 'uduthuththiripitiya', '2026-02-05', 'Mrs. A. Fernando', NULL, '12, Temple Road, Uduthuththiripitiya', 3, 'lactating', 'completed', NULL),
(3, 1, 'uduthuththiripitiya', '2026-02-04', 'Mrs. D. Jayawardene', NULL, '8, School Lane, Uduthuththiripitiya', 2, 'children', 'completed', NULL),
(4, 1, 'uduthuththiripitiya', '2026-02-04', 'Mrs. P. Perera', NULL, '23, Station Road, Uduthuththiripitiya', 1, 'pregnant', 'pending', NULL);

INSERT INTO counseling_sessions (counseling_id, midwife_id, session_datetime, duration_mins, client_ref, focus, location_type, notes, followup, followup_date, referral_details, status) VALUES
(1, 1, '2026-03-26 10:15:00', 45, 'CLN-001', 'breastfeeding', 'clinic', 'Breastfeeding and nutrition support provided.', 'yes', '2026-04-02', NULL, 'completed'),
(2, 1, '2026-03-24 14:00:00', 30, 'CLN-002', 'antenatal', 'home', 'Antenatal care and birth planning discussion.', 'no', NULL, NULL, 'completed'),
(3, 1, '2026-03-22 09:30:00', 60, 'CLN-003', 'mental_health', 'clinic', 'Mental health support session completed.', 'referral', NULL, 'Referral to specialist recommended.', 'completed');

INSERT INTO health_education_sessions (health_education_id, midwife_id, session_date, venue, topic, audience, attendees, duration_mins, materials, outcomes, status) VALUES
(1, 1, '2026-03-28', 'MOH waiting area', 'immunization', 'mixed', 32, 45, 'Flip chart', 'Immunization schedule explained.', 'upcoming'),
(2, 1, '2026-03-18', 'Community centre', 'danger_signs', 'antenatal', 28, 45, 'Leaflets', 'Pregnancy danger signs explained.', 'completed'),
(3, 1, '2026-03-05', 'Clinic hall', 'newborn', 'postnatal', 41, 60, 'Flip chart, leaflets', 'Newborn care and breastfeeding education completed.', 'completed');

INSERT INTO maternal_care_records (mother_id, midwife_id, duty_area, mother_name, age, category, weeks_pregnant, baby_age, breastfeeding_status, delivery_date, delivery_type, recovery_status, last_visit, next_appointment, risk_level, support_level, health_status, status) VALUES
(1, 1, 'uduthuththiripitiya', 'Mrs. Kumari Silva', 28, 'pregnant', 32, NULL, NULL, NULL, NULL, NULL, '2026-02-03', '2026-02-10', 'Low Risk', NULL, NULL, 'active'),
(2, 1, 'uduthuththiripitiya', 'Mrs. Priyanka Perera', 26, 'pregnant', 24, NULL, NULL, NULL, NULL, NULL, '2026-01-30', '2026-02-13', 'Low Risk', NULL, NULL, 'active'),
(3, 1, 'uduthuththiripitiya', 'Mrs. Sanduni Wickramasinghe', 35, 'pregnant', 38, NULL, NULL, NULL, NULL, NULL, '2026-02-01', '2026-02-08', 'High Risk', NULL, NULL, 'active'),
(4, 1, 'uduthuththiripitiya', 'Mrs. Anura Fernando', 32, 'lactating', NULL, '2 weeks', 'Exclusive breastfeeding', NULL, NULL, NULL, '2026-01-28', NULL, NULL, 'Good Support', 'Healthy', 'active'),
(5, 1, 'uduthuththiripitiya', 'Mrs. Dilani Perera', 31, 'postnatal', NULL, NULL, NULL, '2026-01-15', 'Normal Delivery', 'Good Recovery', '2026-02-01', NULL, NULL, NULL, 'Good Recovery', 'active'),
(6, 1, 'kahabilihena', 'Mrs. Nishani Rajapaksha', 27, 'pregnant', 28, NULL, NULL, NULL, NULL, NULL, '2026-02-08', '2026-02-15', 'Low Risk', NULL, NULL, 'active'),
(7, 1, 'opathella', 'Mrs. Chamari Silva', 24, 'pregnant', 20, NULL, NULL, NULL, NULL, NULL, '2026-02-09', '2026-02-16', 'Low Risk', NULL, NULL, 'active'),
(8, 1, 'ambalangoda', 'Mrs. Sandya Fernando', 26, 'pregnant', 36, NULL, NULL, NULL, NULL, NULL, '2026-02-11', '2026-02-14', 'High Risk', NULL, NULL, 'active');

INSERT INTO child_care_records (child_id, midwife_id, duty_area, child_name, mother_name, child_category, date_of_birth, age_label, birth_weight, current_weight, height_cm, school, last_checkup, development_status, health_status, status) VALUES
(1, 1, 'uduthuththiripitiya', 'Baby Fernando', 'Mrs. Anura Fernando', 'newborn', '2026-01-22', NULL, 3.20, NULL, NULL, NULL, '2026-02-01', NULL, 'Healthy', 'active'),
(2, 1, 'uduthuththiripitiya', 'Sahan Perera', 'Mrs. Priyanka Perera', 'young', NULL, '2 years', NULL, 12.50, 85.00, NULL, '2026-02-03', 'Normal', 'Healthy', 'active'),
(3, 1, 'uduthuththiripitiya', 'Amal Wickramasinghe', 'Mrs. Sanduni Wickramasinghe', 'child', NULL, '5 years', NULL, NULL, NULL, 'Sunshine Pre-School', '2026-01-25', NULL, 'Healthy', 'active'),
(4, 1, 'kahabilihena', 'Baby Perera', 'Mrs. Darshika Perera', 'newborn', '2025-11-15', NULL, 3.10, NULL, NULL, NULL, '2026-02-10', NULL, 'Healthy', 'active'),
(5, 1, 'opathella', 'Baby Jayasinghe', 'Mrs. Kumari Jayasinghe', 'newborn', '2025-12-10', NULL, 3.30, NULL, NULL, NULL, '2026-02-08', NULL, 'Healthy', 'active'),
(6, 1, 'ambalangoda', 'Baby Tharusha Bandara', 'Mrs. Ruvini Bandara', 'newborn', '2025-08-15', NULL, 3.40, NULL, NULL, NULL, '2026-02-09', NULL, 'Healthy', 'active');

INSERT INTO midwife_qualifications (qualification_id, midwife_id, qualification_title, institution, completed_year, grade, valid_until) VALUES
(1, 1, 'Diploma in Midwifery', 'University of Colombo - Faculty of Medicine', '2019', 'First Class', NULL),
(2, 1, 'Certificate in Maternal & Child Health', 'Ministry of Health, Sri Lanka', '2020', 'Distinction', NULL),
(3, 1, 'Basic Life Support (BLS) Certification', 'Sri Lankan Heart Association', NULL, NULL, '2025-12-31');

INSERT INTO midwife_trainings (training_id, midwife_id, training_title, provider, completed_date, duration_hours) VALUES
(1, 1, 'Advanced Neonatal Resuscitation', 'Perinatal Society of Sri Lanka', '2024-11-15', 16),
(2, 1, 'Digital Health Records Management', 'Ministry of Health', '2024-09-10', 8),
(3, 1, 'Mental Health First Aid', 'National Institute of Mental Health', '2024-07-20', 12);

INSERT INTO midwife_professional_memberships (membership_id, midwife_id, organization, license_number, member_since, valid_until, status) VALUES
(1, 1, 'Sri Lanka Nursing Council', 'MW-2020-001234', NULL, '2026-01-31', 'active'),
(2, 1, 'Midwives Association of Sri Lanka', NULL, '2020', NULL, 'active');

INSERT INTO midwife_system_preferences (preference_id, midwife_id, email_notifications, sms_alerts, push_notifications, weekly_report, language_code, date_format, time_format, dashboard_theme, session_timeout_minutes) VALUES
(1, 1, 1, 1, 0, 1, 'en', 'dd/mm/yyyy', '24h', 'light', 60);

CREATE OR REPLACE VIEW view_midwife_activity_summary AS
SELECT 
    m.midwife_id,
    m.full_name,
    m.employee_id,
    m.assigned_area,
    COUNT(a.activity_id) AS total_activities,
    COUNT(CASE WHEN DATE(a.activity_date) = CURDATE() THEN 1 END) AS activities_today,
    COUNT(CASE WHEN DATE(a.activity_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) AS activities_this_week,
    AVG(a.duration_minutes) AS avg_duration_minutes,
    MAX(a.activity_date) AS last_activity_date,
    m.status AS midwife_status
FROM midwives m
LEFT JOIN activities a ON m.midwife_id = a.midwife_id
GROUP BY m.midwife_id, m.full_name, m.employee_id, m.assigned_area, m.status;

CREATE OR REPLACE VIEW view_daily_activities AS
SELECT 
    a.activity_date,
    m.full_name AS midwife_name,
    m.assigned_area,
    at.type_name AS activity_type,
    a.patient_name,
    a.location,
    a.duration_minutes,
    a.status,
    a.priority_level
FROM activities a
JOIN midwives m ON a.midwife_id = m.midwife_id
JOIN activity_types at ON a.activity_type_id = at.type_id
ORDER BY a.activity_date DESC, a.start_time ASC;

CREATE OR REPLACE VIEW view_performance_dashboard AS
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
    END AS performance_rating
FROM midwives m
LEFT JOIN performance_metrics pm ON m.midwife_id = pm.midwife_id
WHERE pm.metric_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) OR pm.metric_date IS NULL;

ALTER TABLE moh_admins AUTO_INCREMENT = 3;
ALTER TABLE midwives AUTO_INCREMENT = 11;
ALTER TABLE activity_types AUTO_INCREMENT = 22;
ALTER TABLE activities AUTO_INCREMENT = 74;
ALTER TABLE schedules AUTO_INCREMENT = 1;
ALTER TABLE patients AUTO_INCREMENT = 26;
ALTER TABLE vaccine_inventory AUTO_INCREMENT = 8;
ALTER TABLE vaccination_records AUTO_INCREMENT = 8;
ALTER TABLE vaccination_overdue_followups AUTO_INCREMENT = 4;
ALTER TABLE triposha_inventory AUTO_INCREMENT = 5;
ALTER TABLE triposha_distribution_records AUTO_INCREMENT = 5;
ALTER TABLE counseling_sessions AUTO_INCREMENT = 4;
ALTER TABLE health_education_sessions AUTO_INCREMENT = 4;
ALTER TABLE maternal_care_records AUTO_INCREMENT = 9;
ALTER TABLE child_care_records AUTO_INCREMENT = 7;
ALTER TABLE midwife_qualifications AUTO_INCREMENT = 4;
ALTER TABLE midwife_trainings AUTO_INCREMENT = 4;
ALTER TABLE midwife_professional_memberships AUTO_INCREMENT = 3;
ALTER TABLE midwife_system_preferences AUTO_INCREMENT = 2;
ALTER TABLE notifications AUTO_INCREMENT = 5;
ALTER TABLE system_settings AUTO_INCREMENT = 10;
ALTER TABLE audit_logs AUTO_INCREMENT = 3;
ALTER TABLE home_visits AUTO_INCREMENT = 11;











-- -- MidConnect MySQL initialization
-- -- Generated from current SQLite database.
-- -- This script is safe for first Docker startup. MySQL runs files in /docker-entrypoint-initdb.d only when the mysql_data volume is empty.

-- CREATE DATABASE IF NOT EXISTS midconnect_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE midconnect_db;

-- SET FOREIGN_KEY_CHECKS = 0;

-- DROP VIEW IF EXISTS view_performance_dashboard;
-- DROP VIEW IF EXISTS view_daily_activities;
-- DROP VIEW IF EXISTS view_midwife_activity_summary;

-- DROP TABLE IF EXISTS home_visits;
-- DROP TABLE IF EXISTS audit_logs;
-- DROP TABLE IF EXISTS system_settings;
-- DROP TABLE IF EXISTS notifications;
-- DROP TABLE IF EXISTS performance_goals;
-- DROP TABLE IF EXISTS performance_metrics;
-- DROP TABLE IF EXISTS reports;
-- DROP TABLE IF EXISTS patient_visits;
-- DROP TABLE IF EXISTS patients;
-- DROP TABLE IF EXISTS schedules;
-- DROP TABLE IF EXISTS activities;
-- DROP TABLE IF EXISTS activity_types;
-- DROP TABLE IF EXISTS midwives;
-- DROP TABLE IF EXISTS moh_admins;

-- SET FOREIGN_KEY_CHECKS = 1;

-- CREATE TABLE moh_admins (
--     admin_id INT AUTO_INCREMENT PRIMARY KEY,
--     username VARCHAR(50) UNIQUE NOT NULL,
--     password_hash VARCHAR(255) NOT NULL,
--     full_name VARCHAR(100) NOT NULL,
--     email VARCHAR(100) UNIQUE NOT NULL,
--     phone VARCHAR(20),
--     moh_office VARCHAR(100) NOT NULL,
--     position VARCHAR(50) DEFAULT 'MOH Officer',
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     last_login DATETIME NULL,
--     is_active TINYINT(1) DEFAULT 1,
--     login_attempts INT DEFAULT 0,
--     locked_until DATETIME NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE midwives (
--     midwife_id INT AUTO_INCREMENT PRIMARY KEY,
--     employee_id VARCHAR(20) UNIQUE NOT NULL,
--     password_hash VARCHAR(255) NOT NULL,
--     full_name VARCHAR(100) NOT NULL,
--     email VARCHAR(100) UNIQUE NOT NULL,
--     phone VARCHAR(20),
--     assigned_area VARCHAR(100) NOT NULL,
--     moh_office VARCHAR(100) NOT NULL,
--     supervisor_id INT NULL,
--     hire_date DATE NOT NULL,
--     birth_date DATE NULL,
--     address TEXT,
--     qualification VARCHAR(255),
--     experience_years INT DEFAULT 0,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     last_login DATETIME NULL,
--     status VARCHAR(20) DEFAULT 'active',
--     profile_image VARCHAR(255),
--     login_attempts INT DEFAULT 0,
--     locked_until DATETIME NULL,
--     CONSTRAINT fk_midwives_supervisor FOREIGN KEY (supervisor_id) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE activity_types (
--     type_id INT AUTO_INCREMENT PRIMARY KEY,
--     type_code VARCHAR(30) UNIQUE NOT NULL,
--     type_name VARCHAR(100) NOT NULL,
--     description TEXT,
--     default_duration INT DEFAULT 60,
--     requires_patient TINYINT(1) DEFAULT 1,
--     is_active TINYINT(1) DEFAULT 1,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE activities (
--     activity_id INT AUTO_INCREMENT PRIMARY KEY,
--     midwife_id INT NOT NULL,
--     activity_type_id INT NOT NULL,
--     patient_name VARCHAR(100),
--     patient_age INT,
--     patient_contact VARCHAR(20),
--     activity_date DATE NOT NULL,
--     start_time TIME NOT NULL,
--     end_time TIME NULL,
--     duration_minutes INT,
--     location VARCHAR(200) NOT NULL,
--     description TEXT,
--     observations TEXT,
--     recommendations TEXT,
--     follow_up_required TINYINT(1) DEFAULT 0,
--     follow_up_date DATE NULL,
--     priority_level VARCHAR(20) DEFAULT 'normal',
--     status VARCHAR(30) DEFAULT 'completed',
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     gps_latitude DECIMAL(10,8) NULL,
--     gps_longitude DECIMAL(11,8) NULL,
--     weather_conditions VARCHAR(50),
--     transport_method VARCHAR(50),
--     CONSTRAINT fk_activities_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
--     CONSTRAINT fk_activities_type FOREIGN KEY (activity_type_id) REFERENCES activity_types(type_id),
--     INDEX idx_midwife_date (midwife_id, activity_date),
--     INDEX idx_activity_date (activity_date),
--     INDEX idx_status (status)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE schedules (
--     schedule_id INT AUTO_INCREMENT PRIMARY KEY,
--     midwife_id INT NOT NULL,
--     activity_type_id INT NOT NULL,
--     scheduled_date DATE NOT NULL,
--     start_time TIME NOT NULL,
--     estimated_end_time TIME NOT NULL,
--     patient_name VARCHAR(100),
--     location VARCHAR(200) NOT NULL,
--     description TEXT,
--     priority_level VARCHAR(20) DEFAULT 'normal',
--     status VARCHAR(30) DEFAULT 'scheduled',
--     created_by INT NULL,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     completed_activity_id INT NULL,
--     cancellation_reason TEXT,
--     notes TEXT,
--     CONSTRAINT fk_schedules_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
--     CONSTRAINT fk_schedules_type FOREIGN KEY (activity_type_id) REFERENCES activity_types(type_id),
--     CONSTRAINT fk_schedules_created_by FOREIGN KEY (created_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL,
--     CONSTRAINT fk_schedules_completed_activity FOREIGN KEY (completed_activity_id) REFERENCES activities(activity_id) ON DELETE SET NULL,
--     INDEX idx_midwife_schedule_date (midwife_id, scheduled_date),
--     INDEX idx_schedule_status (status)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE patients (
--     patient_id INT AUTO_INCREMENT PRIMARY KEY,
--     patient_name VARCHAR(100) NOT NULL,
--     birth_date DATE NULL,
--     gender VARCHAR(20) NOT NULL,
--     contact_number VARCHAR(20),
--     address TEXT,
--     emergency_contact VARCHAR(100),
--     emergency_phone VARCHAR(20),
--     medical_history TEXT,
--     allergies TEXT,
--     current_medications TEXT,
--     assigned_midwife_id INT NULL,
--     registration_date DATE DEFAULT (CURRENT_DATE),
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     is_active TINYINT(1) DEFAULT 1,
--     notes TEXT,
--     CONSTRAINT fk_patients_midwife FOREIGN KEY (assigned_midwife_id) REFERENCES midwives(midwife_id) ON DELETE SET NULL,
--     INDEX idx_patient_name (patient_name),
--     INDEX idx_assigned_midwife (assigned_midwife_id)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -- Vaccination inventory table
-- CREATE TABLE vaccine_inventory (
--     vaccine_id INT AUTO_INCREMENT PRIMARY KEY,
--     vaccine_name VARCHAR(100) NOT NULL,
--     vaccine_code VARCHAR(50) UNIQUE NOT NULL,
--     category VARCHAR(50) DEFAULT 'general',
--     stock_quantity INT DEFAULT 0,
--     minimum_stock_level INT DEFAULT 10,
--     batch_number VARCHAR(100),
--     expiry_date DATE NULL,
--     storage_temperature VARCHAR(50),
--     supplier VARCHAR(150),
--     status VARCHAR(30) DEFAULT 'available',
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

--     INDEX idx_vaccine_code (vaccine_code),
--     INDEX idx_expiry_date (expiry_date),
--     INDEX idx_status (status)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -- Vaccination schedule / record table
-- CREATE TABLE vaccination_records (
--     vaccination_id INT AUTO_INCREMENT PRIMARY KEY,
--     patient_id INT NULL,
--     midwife_id INT NOT NULL,
--     vaccine_id INT NOT NULL,

--     patient_name VARCHAR(100) NOT NULL,
--     patient_age INT NULL,
--     contact_number VARCHAR(20),
--     address TEXT,

--     vaccination_date DATE NOT NULL,
--     vaccination_time TIME NOT NULL,
--     location VARCHAR(200) NOT NULL,

--     dose_number VARCHAR(50),
--     batch_number VARCHAR(100),
--     next_due_date DATE NULL,

--     status VARCHAR(30) DEFAULT 'scheduled',
--     notes TEXT,

--     completed_at DATETIME NULL,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

--     CONSTRAINT fk_vaccination_patient
--         FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
--         ON DELETE SET NULL,

--     CONSTRAINT fk_vaccination_midwife
--         FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id)
--         ON DELETE CASCADE,

--     CONSTRAINT fk_vaccination_vaccine
--         FOREIGN KEY (vaccine_id) REFERENCES vaccine_inventory(vaccine_id),

--     INDEX idx_vaccination_midwife (midwife_id),
--     INDEX idx_vaccination_date (vaccination_date),
--     INDEX idx_vaccination_status (status),
--     INDEX idx_vaccination_patient (patient_id)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- CREATE TABLE patient_visits (
--     visit_id INT AUTO_INCREMENT PRIMARY KEY,
--     patient_id INT NOT NULL,
--     activity_id INT NOT NULL,
--     visit_type VARCHAR(50),
--     vital_signs JSON NULL,
--     diagnosis TEXT,
--     treatment_given TEXT,
--     medications_prescribed TEXT,
--     next_visit_date DATE NULL,
--     visit_notes TEXT,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     CONSTRAINT fk_patient_visits_patient FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
--     CONSTRAINT fk_patient_visits_activity FOREIGN KEY (activity_id) REFERENCES activities(activity_id) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE reports (
--     report_id INT AUTO_INCREMENT PRIMARY KEY,
--     report_type VARCHAR(30) NOT NULL,
--     generated_by_midwife_id INT NULL,
--     generated_by_admin_id INT NULL,
--     report_period_start DATE NOT NULL,
--     report_period_end DATE NOT NULL,
--     title VARCHAR(200) NOT NULL,
--     summary TEXT,
--     total_activities INT DEFAULT 0,
--     total_patients_served INT DEFAULT 0,
--     total_duration_hours DECIMAL(6,2) DEFAULT 0,
--     report_data JSON NULL,
--     status VARCHAR(30) DEFAULT 'draft',
--     submitted_at DATETIME NULL,
--     reviewed_by INT NULL,
--     reviewed_at DATETIME NULL,
--     reviewer_comments TEXT,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     CONSTRAINT fk_reports_midwife FOREIGN KEY (generated_by_midwife_id) REFERENCES midwives(midwife_id) ON DELETE SET NULL,
--     CONSTRAINT fk_reports_admin FOREIGN KEY (generated_by_admin_id) REFERENCES moh_admins(admin_id) ON DELETE SET NULL,
--     CONSTRAINT fk_reports_reviewer FOREIGN KEY (reviewed_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE performance_metrics (
--     metric_id INT AUTO_INCREMENT PRIMARY KEY,
--     midwife_id INT NOT NULL,
--     metric_date DATE NOT NULL,
--     total_activities INT DEFAULT 0,
--     home_visits INT DEFAULT 0,
--     clinic_visits INT DEFAULT 0,
--     vaccinations INT DEFAULT 0,
--     counseling_sessions INT DEFAULT 0,
--     emergency_responses INT DEFAULT 0,
--     patients_served INT DEFAULT 0,
--     total_duration_hours DECIMAL(6,2) DEFAULT 0,
--     completion_rate DECIMAL(5,2) DEFAULT 0,
--     punctuality_score DECIMAL(5,2) DEFAULT 0,
--     patient_satisfaction DECIMAL(3,2) DEFAULT 0,
--     goals_met INT DEFAULT 0,
--     goals_total INT DEFAULT 0,
--     calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     CONSTRAINT fk_perf_metrics_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
--     UNIQUE KEY unique_midwife_date (midwife_id, metric_date),
--     INDEX idx_metric_date (metric_date)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE performance_goals (
--     goal_id INT AUTO_INCREMENT PRIMARY KEY,
--     midwife_id INT NULL,
--     moh_office VARCHAR(100) NULL,
--     goal_type VARCHAR(30) NOT NULL,
--     metric_name VARCHAR(100) NOT NULL,
--     target_value DECIMAL(10,2) NOT NULL,
--     current_value DECIMAL(10,2) DEFAULT 0,
--     period_start DATE NOT NULL,
--     period_end DATE NOT NULL,
--     description TEXT,
--     created_by INT NULL,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     is_active TINYINT(1) DEFAULT 1,
--     CONSTRAINT fk_perf_goals_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
--     CONSTRAINT fk_perf_goals_created_by FOREIGN KEY (created_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE notifications (
--     notification_id INT AUTO_INCREMENT PRIMARY KEY,
--     recipient_type VARCHAR(20) NOT NULL,
--     recipient_id INT NOT NULL,
--     sender_type VARCHAR(20) NULL,
--     sender_id INT NULL,
--     title VARCHAR(200) NOT NULL,
--     message TEXT NOT NULL,
--     notification_type VARCHAR(30) DEFAULT 'info',
--     is_read TINYINT(1) DEFAULT 0,
--     action_required TINYINT(1) DEFAULT 0,
--     action_url VARCHAR(255),
--     priority VARCHAR(20) DEFAULT 'normal',
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     read_at DATETIME NULL,
--     expires_at DATETIME NULL,
--     INDEX idx_recipient (recipient_type, recipient_id),
--     INDEX idx_unread (is_read, created_at),
--     INDEX idx_notification_type (notification_type)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE system_settings (
--     setting_id INT AUTO_INCREMENT PRIMARY KEY,
--     setting_key VARCHAR(100) UNIQUE NOT NULL,
--     setting_value TEXT,
--     setting_type VARCHAR(20) DEFAULT 'string',
--     description TEXT,
--     is_editable TINYINT(1) DEFAULT 1,
--     updated_by INT NULL,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     CONSTRAINT fk_settings_updated_by FOREIGN KEY (updated_by) REFERENCES moh_admins(admin_id) ON DELETE SET NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE audit_logs (
--     log_id INT AUTO_INCREMENT PRIMARY KEY,
--     user_type VARCHAR(20) NOT NULL,
--     user_id INT NULL,
--     action VARCHAR(100) NOT NULL,
--     table_name VARCHAR(100),
--     record_id INT NULL,
--     old_values JSON NULL,
--     new_values JSON NULL,
--     ip_address VARCHAR(45),
--     user_agent TEXT,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     INDEX idx_user (user_type, user_id),
--     INDEX idx_action_date (action, created_at),
--     INDEX idx_table_record (table_name, record_id)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE home_visits (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     midwife_id INT NOT NULL,
--     patient_name VARCHAR(100) NOT NULL,
--     contact_number VARCHAR(20),
--     address TEXT,
--     visit_date DATE NOT NULL,
--     start_time TIME NOT NULL,
--     end_time TIME NULL,
--     duration_minutes INT,
--     duty_area VARCHAR(100) NOT NULL,
--     visit_type VARCHAR(30) DEFAULT 'routine',
--     priority VARCHAR(20) DEFAULT 'normal',
--     reason TEXT,
--     status VARCHAR(30) DEFAULT 'scheduled',
--     notes TEXT,
--     completed_at DATETIME NULL,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     CONSTRAINT fk_home_visits_midwife FOREIGN KEY (midwife_id) REFERENCES midwives(midwife_id) ON DELETE CASCADE,
--     INDEX idx_home_visit_midwife (midwife_id),
--     INDEX idx_home_visit_date (visit_date),
--     INDEX idx_home_visit_status (status),
--     INDEX idx_home_visit_area (duty_area)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -- Seed data for moh_admins
-- INSERT INTO `moh_admins` (`admin_id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `moh_office`, `position`, `created_at`, `updated_at`, `last_login`, `is_active`, `login_attempts`, `locked_until`) VALUES (1, 'admin001', '$2y$10$4cmkYFt/eElLt/T2aK65meoXLgCQGIUaGO5XMlyK3cN5jWBHs8d.G', 'Dr. Sarah Johnson', 'sarah.johnson@health.gov.lk', NULL, 'MOH Colombo 01', 'MOH Officer', '2026-02-11 07:15:53', '2026-02-11 07:15:53', '2026-02-11', 1, 0, NULL);
-- INSERT INTO `moh_admins` (`admin_id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `moh_office`, `position`, `created_at`, `updated_at`, `last_login`, `is_active`, `login_attempts`, `locked_until`) VALUES (2, 'admin002', '$2y$10$4cmkYFt/eElLt/T2aK65meoXLgCQGIUaGO5XMlyK3cN5jWBHs8d.G', 'Dr. Kumara Silva', 'kumara.silva@health.gov.lk', NULL, 'MOH Gampaha', 'MOH Officer', '2026-02-11 07:15:53', '2026-02-11 07:15:53', NULL, 1, 0, NULL);

-- -- Seed data for midwives
-- INSERT INTO `midwives` (`midwife_id`, `employee_id`, `password_hash`, `full_name`, `email`, `phone`, `assigned_area`, `moh_office`, `supervisor_id`, `hire_date`, `birth_date`, `address`, `qualification`, `experience_years`, `created_at`, `updated_at`, `last_login`, `status`, `profile_image`, `login_attempts`, `locked_until`) VALUES (1, 'MW001', '$2y$10$oKgGJdcx8NmEAwzqOBYREeeO/7sEAqiVMzfsa1lDIBW50Wj1pc./C', 'Madhavi Jayawardene', 'madhavi.j@midconnect.lk', '+94712345678', 'Uduthuththiripitiya', 'MOH Uduthuththiripitiya', NULL, '2020-01-15', NULL, NULL, NULL, 0, '2026-04-30 17:55:45', '2026-04-30 17:55:45', NULL, 'active', NULL, 0, NULL);
-- INSERT INTO `midwives` (`midwife_id`, `employee_id`, `password_hash`, `full_name`, `email`, `phone`, `assigned_area`, `moh_office`, `supervisor_id`, `hire_date`, `birth_date`, `address`, `qualification`, `experience_years`, `created_at`, `updated_at`, `last_login`, `status`, `profile_image`, `login_attempts`, `locked_until`) VALUES (2, 'MW002', '$2y$10$OJojUc5WkzbVYQuMatJN8.YJ72EWKDyt0BX1JvnYW7ZPmKIc8guI6', 'Sandya Thennakoon', 'sandya.t@midconnect.lk', '+94712345679', 'Kahabilihena', 'MOH Kahabilihena', NULL, '2020-02-20', NULL, NULL, NULL, 0, '2026-04-30 17:55:45', '2026-04-30 17:55:45', NULL, 'active', NULL, 0, NULL);
-- INSERT INTO `midwives` (`midwife_id`, `employee_id`, `password_hash`, `full_name`, `email`, `phone`, `assigned_area`, `moh_office`, `supervisor_id`, `hire_date`, `birth_date`, `address`, `qualification`, `experience_years`, `created_at`, `updated_at`, `last_login`, `status`, `profile_image`, `login_attempts`, `locked_until`) VALUES (3, 'MW003', '$2y$10$tCgy3xfk0/i4uMvhEDTzgOGJs76EJ.euER/V.mC8JER3u.kqJ/n/K', 'Kumari Hathurusingha', 'kumari.h@midconnect.lk', '+94712345680', 'Opathella', 'MOH Opathella', NULL, '2020-03-10', NULL, NULL, NULL, 0, '2026-04-30 17:55:45', '2026-04-30 17:55:45', NULL, 'active', NULL, 0, NULL);
-- INSERT INTO `midwives` (`midwife_id`, `employee_id`, `password_hash`, `full_name`, `email`, `phone`, `assigned_area`, `moh_office`, `supervisor_id`, `hire_date`, `birth_date`, `address`, `qualification`, `experience_years`, `created_at`, `updated_at`, `last_login`, `status`, `profile_image`, `login_attempts`, `locked_until`) VALUES (4, 'MW004', '$2y$10$11aLkQOYlwcnuS4JX5bOlOpPJWWfzMMLGGZErmoRiSXvvASwJzzX.', 'Anusha Fernando', 'anusha.f@midconnect.lk', '+94712345681', 'Ambalangoda', 'MOH Ambalangoda', NULL, '2020-04-05', NULL, NULL, NULL, 0, '2026-04-30 17:55:45', '2026-04-30 17:55:45', NULL, 'active', NULL, 0, NULL);
-- INSERT INTO `midwives` (`midwife_id`, `employee_id`, `password_hash`, `full_name`, `email`, `phone`, `assigned_area`, `moh_office`, `supervisor_id`, `hire_date`, `birth_date`, `address`, `qualification`, `experience_years`, `created_at`, `updated_at`, `last_login`, `status`, `profile_image`, `login_attempts`, `locked_until`) VALUES (7, 'MW005', '$2y$10$TZw91Tkdfecud01Hpy3bSu2uKhJnKmAJDQ1sUZXM4JvESOP2R.FlO', 'Sunil Fernando', 'sunil@midconnect.com', '0771234569', 'Opathella', 'MOH Colombo 01', NULL, '2026-03-30', NULL, NULL, NULL, 0, '2026-03-29 22:30:59', '2026-03-29 22:30:59', NULL, 'active', NULL, 0, NULL);
-- INSERT INTO `midwives` (`midwife_id`, `employee_id`, `password_hash`, `full_name`, `email`, `phone`, `assigned_area`, `moh_office`, `supervisor_id`, `hire_date`, `birth_date`, `address`, `qualification`, `experience_years`, `created_at`, `updated_at`, `last_login`, `status`, `profile_image`, `login_attempts`, `locked_until`) VALUES (8, 'MW006', '$2y$10$tWNvGrNmM45YFWMzSkRvcuFMsWblcbkpwSNY0xFO0iHjpZ0UTgv1a', 'Chandra Jayawardene', 'chandra@midconnect.com', '0771234570', 'Ambalangoda', 'MOH Colombo 01', NULL, '2026-03-30', NULL, NULL, NULL, 0, '2026-03-29 22:30:59', '2026-03-29 22:30:59', NULL, 'active', NULL, 0, NULL);
-- INSERT INTO `midwives` (`midwife_id`, `employee_id`, `password_hash`, `full_name`, `email`, `phone`, `assigned_area`, `moh_office`, `supervisor_id`, `hire_date`, `birth_date`, `address`, `qualification`, `experience_years`, `created_at`, `updated_at`, `last_login`, `status`, `profile_image`, `login_attempts`, `locked_until`) VALUES (9, 'MW007', '$2y$10$6xEmXYaPCbbmp0JwA72c6./08c2g/UmXv5XwcE0YAaERXqhzfnKdG', 'Rashmi Bandara', 'rashmi.bandara@midconnect.gov.lk', '0773525192', 'Ambalangoda', 'MOH Colombo 01', NULL, '2023-01-01', NULL, NULL, NULL, 0, '2026-03-30 07:19:31', '2026-03-30 07:19:31', NULL, 'active', NULL, 0, NULL);
-- INSERT INTO `midwives` (`midwife_id`, `employee_id`, `password_hash`, `full_name`, `email`, `phone`, `assigned_area`, `moh_office`, `supervisor_id`, `hire_date`, `birth_date`, `address`, `qualification`, `experience_years`, `created_at`, `updated_at`, `last_login`, `status`, `profile_image`, `login_attempts`, `locked_until`) VALUES (10, 'MW008', '$2y$10$8jAI.MdVqZhr7cJa9hatce1Zz3ZKeusWKboAPXxtxDxURKLWarVWS', 'Dilani Wijeratne', 'dilani.wijeratne@midconnect.gov.lk', '0777325138', 'Ambalangoda', 'MOH Colombo 01', NULL, '2023-01-01', NULL, NULL, NULL, 0, '2026-03-30 07:19:31', '2026-03-30 07:19:31', NULL, 'active', NULL, 0, NULL);

-- -- Seed data for activity_types
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (1, 'HOME_VISIT', 'Home Visit', 'Routine home visit for maternal or child health checkup', 45, 1, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (2, 'CLINIC_VISIT', 'Clinic Visit', 'Clinic-based consultation and examination', 30, 1, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (3, 'VACCINATION', 'Vaccination', 'Immunization services for children and adults', 15, 1, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (4, 'COUNSELING', 'Counseling Session', 'Health education and counseling services', 60, 1, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (5, 'HEALTH_EDUCATION', 'Health Education', 'Community health education programs', 120, 0, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (6, 'EMERGENCY_RESPONSE', 'Emergency Response', 'Emergency medical response and care', 90, 1, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (7, 'PRENATAL_CARE', 'Prenatal Care', 'Antenatal care and monitoring', 45, 1, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (8, 'POSTNATAL_CARE', 'Postnatal Care', 'Postpartum care and follow-up', 45, 1, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (9, 'FAMILY_PLANNING', 'Family Planning', 'Family planning counseling and services', 60, 1, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (10, 'NUTRITION_COUNSELING', 'Nutrition Counseling', 'Nutritional guidance and support', 45, 1, 1, '2026-02-11 07:15:52');
-- INSERT INTO `activity_types` (`type_id`, `type_code`, `type_name`, `description`, `default_duration`, `requires_patient`, `is_active`, `created_at`) VALUES (21, 'MEETING', 'Meeting', 'Professional meetings and conferences', 60, 0, 1, '2026-03-29 22:24:25');

-- -- Seed data for activities
-- INSERT INTO `activities` (`activity_id`, `midwife_id`, `activity_type_id`, `patient_name`, `patient_age`, `patient_contact`, `activity_date`, `start_time`, `end_time`, `duration_minutes`, `location`, `description`, `observations`, `recommendations`, `follow_up_required`, `follow_up_date`, `priority_level`, `status`, `created_at`, `updated_at`, `gps_latitude`, `gps_longitude`, `weather_conditions`, `transport_method`) VALUES (70, 7, 1, 'Priyanka Jayasinghe', 25, NULL, '2024-03-30', '08:30', '10:00', NULL, 'Patient Residence', 'Postnatal care', NULL, NULL, 0, NULL, 'normal', 'completed', '2026-03-30 07:19:31', '2026-03-30 07:19:31', NULL, NULL, NULL, NULL);
-- INSERT INTO `activities` (`activity_id`, `midwife_id`, `activity_type_id`, `patient_name`, `patient_age`, `patient_contact`, `activity_date`, `start_time`, `end_time`, `duration_minutes`, `location`, `description`, `observations`, `recommendations`, `follow_up_required`, `follow_up_date`, `priority_level`, `status`, `created_at`, `updated_at`, `gps_latitude`, `gps_longitude`, `weather_conditions`, `transport_method`) VALUES (71, 8, 2, 'Ruwani Perera', 30, NULL, '2024-03-29', '13:00', '14:00', NULL, 'Opathella Clinic', 'Pregnancy checkup', NULL, NULL, 0, NULL, 'normal', 'completed', '2026-03-30 07:19:31', '2026-03-30 07:19:31', NULL, NULL, NULL, NULL);
-- INSERT INTO `activities` (`activity_id`, `midwife_id`, `activity_type_id`, `patient_name`, `patient_age`, `patient_contact`, `activity_date`, `start_time`, `end_time`, `duration_minutes`, `location`, `description`, `observations`, `recommendations`, `follow_up_required`, `follow_up_date`, `priority_level`, `status`, `created_at`, `updated_at`, `gps_latitude`, `gps_longitude`, `weather_conditions`, `transport_method`) VALUES (72, 9, 3, 'Dinushi Fernando', 6, NULL, '2024-03-30', '10:00', '10:30', NULL, 'Ambalangoda Hospital', 'MMR vaccine', NULL, NULL, 0, NULL, 'normal', 'completed', '2026-03-30 07:19:31', '2026-03-30 07:19:31', NULL, NULL, NULL, NULL);
-- INSERT INTO `activities` (`activity_id`, `midwife_id`, `activity_type_id`, `patient_name`, `patient_age`, `patient_contact`, `activity_date`, `start_time`, `end_time`, `duration_minutes`, `location`, `description`, `observations`, `recommendations`, `follow_up_required`, `follow_up_date`, `priority_level`, `status`, `created_at`, `updated_at`, `gps_latitude`, `gps_longitude`, `weather_conditions`, `transport_method`) VALUES (73, 10, 21, NULL, NULL, NULL, '2024-03-29', '09:00', '11:00', NULL, 'Ambalangoda MOH Office', 'Monthly staff meeting', NULL, NULL, 0, NULL, 'normal', 'completed', '2026-03-30 07:19:31', '2026-03-30 07:19:31', NULL, NULL, NULL, NULL);

-- -- Seed data for patients
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (1, 'Kamala Silva', '1995-05-15', 'female', '+94712345678', 'No. 45, Galle Road, Colombo 03', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-11', '2026-02-11 07:15:53', '2026-02-11 07:15:53', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (2, 'Kamala Silva', '1995-05-15', 'female', '+94712345678', 'No. 45, Galle Road, Colombo 03', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-11', '2026-02-11 07:15:53', '2026-02-11 07:15:53', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (3, 'Kamala Silva', '1995-05-15', 'female', '+94712345678', 'No. 45, Galle Road, Colombo 03', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04', '2026-04-04 15:37:20', '2026-04-04 15:37:20', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (4, 'Mrs. Nirmala Fernando', '1990-05-15', 'F', '+94711234567', 'No. 45, Galle Road, Uduthuththiripitiya', NULL, NULL, NULL, NULL, NULL, 1, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (5, 'Mrs. Kamani Wickramasinghe', '1988-08-22', 'F', '+94711234568', 'No. 78, Temple Road, Uduthuththiripitiya', NULL, NULL, NULL, NULL, NULL, 1, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (6, 'Mrs. Sandya Peris', '1992-03-10', 'F', '+94711234569', 'No. 23, Flower Road, Uduthuththiripitiya', NULL, NULL, NULL, NULL, NULL, 1, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (7, 'Mrs. Priyani Silva', '1985-11-30', 'F', '+94711234570', 'No. 12, Lake View, Uduthuththiripitiya', NULL, NULL, NULL, NULL, NULL, 1, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (8, 'Mrs. Anoma Rajapaksa', '1991-07-18', 'F', '+94711234571', 'No. 67, Hill Street, Uduthuththiripitiya', NULL, NULL, NULL, NULL, NULL, 1, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (9, 'Mrs. Nilmini Gunawardena', '1989-12-05', 'F', '+94711234572', 'No. 89, River Road, Uduthuththiripitiya', NULL, NULL, NULL, NULL, NULL, 1, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (10, 'Mrs. Lakshmi Perera', '1993-01-25', 'F', '+94711234573', 'No. 34, Main Road, Kahabilihena', NULL, NULL, NULL, NULL, NULL, 2, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (11, 'Mrs. Chamari Jayasinghe', '1987-09-14', 'F', '+94711234574', 'No. 56, Beach Road, Kahabilihena', NULL, NULL, NULL, NULL, NULL, 2, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (12, 'Mrs. Indika Fernando', '1990-04-08', 'F', '+94711234575', 'No. 78, Garden Lane, Kahabilihena', NULL, NULL, NULL, NULL, NULL, 2, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (13, 'Mrs. Thilini Rathnayake', '1986-06-20', 'F', '+94711234576', 'No. 90, Hill View, Kahabilihena', NULL, NULL, NULL, NULL, NULL, 2, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (14, 'Mrs. Nadeeka Silva', '1994-02-12', 'F', '+94711234577', 'No. 45, Valley Road, Kahabilihena', NULL, NULL, NULL, NULL, NULL, 2, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (15, 'Mrs. Kumudini Jayawardena', '1988-10-30', 'F', '+94711234578', 'No. 23, Coastal Road, Opathella', NULL, NULL, NULL, NULL, NULL, 3, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (16, 'Mrs. Rasika Peris', '1991-05-17', 'F', '+94711234579', 'No. 67, Mountain View, Opathella', NULL, NULL, NULL, NULL, NULL, 3, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (17, 'Mrs. Dilini Fernando', '1989-08-09', 'F', '+94711234580', 'No. 89, River Bank, Opathella', NULL, NULL, NULL, NULL, NULL, 3, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (18, 'Mrs. Chamika Gunasekara', '1992-11-22', 'F', '+94711234581', 'No. 12, Forest Lane, Opathella', NULL, NULL, NULL, NULL, NULL, 3, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (19, 'Mrs. Nirosha Wickramasinghe', '1987-03-14', 'F', '+94711234582', 'No. 45, Sea View, Opathella', NULL, NULL, NULL, NULL, NULL, 3, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (20, 'Mrs. Sanduni Rathnayake', '1993-07-26', 'F', '+94711234583', 'No. 78, Hill Road, Opathella', NULL, NULL, NULL, NULL, NULL, 3, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (21, 'Mrs. Anura Silva', '1985-12-03', 'F', '+94711234584', 'No. 34, Port Road, Ambalangoda', NULL, NULL, NULL, NULL, NULL, 4, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (22, 'Mrs. Deepika Jayasinghe', '1990-09-15', 'F', '+94711234585', 'No. 56, Harbor View, Ambalangoda', NULL, NULL, NULL, NULL, NULL, 4, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (23, 'Mrs. Ishani Perera', '1988-04-27', 'F', '+94711234586', 'No. 78, Fishery Lane, Ambalangoda', NULL, NULL, NULL, NULL, NULL, 4, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (24, 'Mrs. Kavindi Fernando', '1991-01-08', 'F', '+94711234587', 'No. 90, Beach Front, Ambalangoda', NULL, NULL, NULL, NULL, NULL, 4, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);
-- INSERT INTO `patients` (`patient_id`, `patient_name`, `birth_date`, `gender`, `contact_number`, `address`, `emergency_contact`, `emergency_phone`, `medical_history`, `allergies`, `current_medications`, `assigned_midwife_id`, `registration_date`, `created_at`, `updated_at`, `is_active`, `notes`) VALUES (25, 'Mrs. Maheshi Gunawardena', '1986-06-19', 'F', '+94711234588', 'No. 23, Coconut Grove, Ambalangoda', NULL, NULL, NULL, NULL, NULL, 4, '2026-04-30', '2026-04-30 17:55:45', '2026-04-30 17:55:45', 1, NULL);

-- -- Seed data for notifications
-- INSERT INTO `notifications` (`notification_id`, `recipient_type`, `recipient_id`, `sender_type`, `sender_id`, `title`, `message`, `notification_type`, `is_read`, `action_required`, `action_url`, `priority`, `created_at`, `read_at`, `expires_at`) VALUES (1, 'admin', 1, 'system', NULL, 'Admin Login Successful', 'Welcome back to MidConnect admin panel. Session type: Standard', 'success', 0, 0, NULL, 'normal', '2026-02-11', NULL, NULL);
-- INSERT INTO `notifications` (`notification_id`, `recipient_type`, `recipient_id`, `sender_type`, `sender_id`, `title`, `message`, `notification_type`, `is_read`, `action_required`, `action_url`, `priority`, `created_at`, `read_at`, `expires_at`) VALUES (2, 'midwife', 1, 'system', NULL, 'Welcome Back!', 'You have successfully logged into MidConnect.', 'success', 0, 0, NULL, 'normal', '2026-02-11', NULL, NULL);
-- INSERT INTO `notifications` (`notification_id`, `recipient_type`, `recipient_id`, `sender_type`, `sender_id`, `title`, `message`, `notification_type`, `is_read`, `action_required`, `action_url`, `priority`, `created_at`, `read_at`, `expires_at`) VALUES (3, 'midwife', 1, 'system', NULL, 'Urgent Notice', '[SEED] Monthly reports are due tomorrow at 5PM.', 'warning', 0, 0, NULL, 'normal', '2026-03-29 06:12:12', NULL, NULL);
-- INSERT INTO `notifications` (`notification_id`, `recipient_type`, `recipient_id`, `sender_type`, `sender_id`, `title`, `message`, `notification_type`, `is_read`, `action_required`, `action_url`, `priority`, `created_at`, `read_at`, `expires_at`) VALUES (4, 'midwife', 1, 'admin', NULL, 'Schedule Updated', '[SEED] Dr. Sarah has updated your clinic roster for next week.', 'info', 0, 0, NULL, 'normal', '2026-03-29 06:12:12', NULL, NULL);

-- -- Seed data for system_settings
-- INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_by`, `updated_at`) VALUES (1, 'system_name', 'MidConnect', 'string', 'System name displayed in the application', 1, NULL, '2026-02-11 07:15:53');
-- INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_by`, `updated_at`) VALUES (2, 'max_login_attempts', '5', 'number', 'Maximum number of login attempts before account lockout', 1, NULL, '2026-02-11 07:15:53');
-- INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_by`, `updated_at`) VALUES (3, 'session_timeout', '3600', 'number', 'Session timeout in seconds', 1, NULL, '2026-02-11 07:15:53');
-- INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_by`, `updated_at`) VALUES (4, 'report_retention_days', '365', 'number', 'Number of days to retain reports', 1, NULL, '2026-02-11 07:15:53');
-- INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_by`, `updated_at`) VALUES (5, 'backup_frequency', 'daily', 'string', 'Database backup frequency', 1, NULL, '2026-02-11 07:15:53');
-- INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_by`, `updated_at`) VALUES (6, 'notification_retention_days', '30', 'number', 'Number of days to keep read notifications', 1, NULL, '2026-02-11 07:15:53');
-- INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_by`, `updated_at`) VALUES (7, 'working_hours_start', '08:00', 'string', 'Standard working hours start time', 1, NULL, '2026-02-11 07:15:53');
-- INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_by`, `updated_at`) VALUES (8, 'working_hours_end', '17:00', 'string', 'Standard working hours end time', 1, NULL, '2026-02-11 07:15:53');
-- INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_editable`, `updated_by`, `updated_at`) VALUES (9, 'emergency_contact', '+94 11 2 691 757', 'string', 'Emergency contact number for system issues', 1, NULL, '2026-02-11 07:15:53');

-- -- Seed data for audit_logs
-- INSERT INTO `audit_logs` (`log_id`, `user_type`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (1, 'admin', 1, 'successful_login', NULL, NULL, NULL, NULL, '::1', 'curl/8.16.0', '2026-02-11');
-- INSERT INTO `audit_logs` (`log_id`, `user_type`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES (2, 'midwife', 1, 'successful_login', NULL, NULL, NULL, NULL, '::1', 'curl/8.16.0', '2026-02-11');

-- -- Seed data for home_visits
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (1, 1, 'Mrs. Nirmala Fernando', '+94 77 123 4567', 'No. 45, Galle Road, Mount Lavinia', '2026-05-01', '09:00', NULL, 45, 'uduthuththiripitiya', 'postnatal', 'high', 'Postnatal Visit - Day 3', 'scheduled', 'Follow-up on breastfeeding issues and jaundice monitoring', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27');
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (2, 1, 'Mrs. Kamani Wickramasinghe', '+94 77 234 5678', 'No. 78, Temple Road, Dehiwala', '2026-05-01', '10:30', NULL, 30, 'uduthuththiripitiya', 'antenatal', 'high', 'Antenatal Visit - 32 weeks', 'scheduled', 'Routine checkup, monitor blood pressure and fetal growth', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27');
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (3, 1, 'Mrs. Sandya Peris', '+94 77 345 6789', 'No. 23, Flower Road, Colombo 7', '2026-05-01', '14:00', NULL, 40, 'uduthuththiripitiya', 'postnatal', 'normal', 'Postnatal Visit - Day 14', 'scheduled', 'Check healing progress, discuss family planning', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27');
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (4, 1, 'Mrs. Priyani Silva', '+94 77 456 7890', 'No. 12, Lake Road, Uduthuththiripitiya', '2026-04-30', '08:30', '09:15', 45, 'uduthuththiripitiya', 'antenatal', 'normal', 'Antenatal Visit - 28 weeks', 'completed', 'Normal progression, all vitals stable', '2026-04-30 09:15:00', '2026-05-01 14:44:27', '2026-05-01 14:44:27');
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (5, 1, 'Mrs. Dilani Jayasinghe', '+94 77 567 8901', 'No. 19, School Lane, Uduthuththiripitiya', '2026-04-30', '11:00', '11:35', 35, 'uduthuththiripitiya', 'routine', 'normal', 'Routine mother and child wellness check', 'completed', 'Nutrition advice given, next clinic date confirmed', '2026-04-30 11:35:00', '2026-05-01 14:44:27', '2026-05-01 14:44:27');
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (6, 1, 'Mrs. Anoma Kumari', '+94 77 678 9012', 'No. 05, Hospital Road, Kahabilihena', '2026-05-01', '09:30', NULL, 40, 'kahabilihena', 'antenatal', 'normal', 'Antenatal Visit - 24 weeks', 'scheduled', 'Routine antenatal assessment', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27');
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (7, 1, 'Mrs. Ruwani Perera', '+94 77 789 0123', 'No. 34, Main Street, Kahabilihena', '2026-05-01', '13:15', NULL, 45, 'kahabilihena', 'family-planning', 'normal', 'Family planning follow-up', 'scheduled', 'Counseling and follow-up discussion', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27');
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (8, 1, 'Mrs. Tharuka Silva', '+94 77 890 1234', 'No. 11, Garden Road, Kahabilihena', '2026-04-30', '10:00', '10:40', 40, 'kahabilihena', 'postnatal', 'normal', 'Postnatal review', 'completed', 'Mother and newborn doing well', '2026-04-30 10:40:00', '2026-05-01 14:44:27', '2026-05-01 14:44:27');
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (9, 1, 'Mrs. Himali Fernando', '+94 77 901 2345', 'No. 8, River Lane, Opathella', '2026-05-01', '09:45', NULL, 60, 'opathella', 'emergency', 'urgent', 'High risk pregnancy monitoring', 'scheduled', 'Urgent follow-up for high blood pressure', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27');
-- INSERT INTO `home_visits` (`id`, `midwife_id`, `patient_name`, `contact_number`, `address`, `visit_date`, `start_time`, `end_time`, `duration_minutes`, `duty_area`, `visit_type`, `priority`, `reason`, `status`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES (10, 1, 'Mrs. Malini Dias', '+94 77 012 3456', 'No. 16, Market Road, Ambalangoda', '2026-05-01', '11:45', NULL, 35, 'ambalangoda', 'routine', 'normal', 'Child wellness home visit', 'scheduled', 'Growth monitoring and nutrition guidance', NULL, '2026-05-01 14:44:27', '2026-05-01 14:44:27');

-- -- Seed data for vaccine_inventory
-- INSERT INTO vaccine_inventory 
-- (vaccine_id, vaccine_name, vaccine_code, category, stock_quantity, minimum_stock_level, batch_number, expiry_date, storage_temperature, supplier, status)
-- VALUES
-- (1, 'BCG Vaccine', 'BCG', 'pediatric', 120, 20, 'BCG-2026-001', '2026-12-31', '2-8°C', 'MOH Central Store', 'available'),
-- (2, 'MMR Vaccine', 'MMR', 'pediatric', 85, 20, 'MMR-2026-001', '2026-11-30', '2-8°C', 'MOH Central Store', 'available'),
-- (3, 'Pentavalent Vaccine', 'PENTA', 'pediatric', 60, 15, 'PENTA-2026-001', '2026-10-30', '2-8°C', 'MOH Central Store', 'available'),
-- (4, 'Tetanus Toxoid', 'TT', 'maternal', 100, 20, 'TT-2026-001', '2027-01-15', '2-8°C', 'MOH Central Store', 'available'),
-- (5, 'COVID-19 Vaccine', 'COVID19', 'adult', 40, 10, 'COV-2026-001', '2026-09-30', '2-8°C', 'MOH Central Store', 'available');


-- -- Seed data for vaccination_records
-- INSERT INTO vaccination_records
-- (vaccination_id, patient_id, midwife_id, vaccine_id, patient_name, patient_age, contact_number, address, vaccination_date, vaccination_time, location, dose_number, batch_number, next_due_date, status, notes)
-- VALUES
-- (1, 4, 1, 2, 'Mrs. Nirmala Fernando', 1, '+94711234567', 'No. 45, Galle Road, Uduthuththiripitiya', '2026-05-24', '09:00:00', 'Uduthuththiripitiya Clinic', 'Dose 1', 'MMR-2026-001', '2026-06-24', 'scheduled', 'Routine child vaccination'),
-- (2, 5, 1, 4, 'Mrs. Kamani Wickramasinghe', 32, '+94711234568', 'No. 78, Temple Road, Uduthuththiripitiya', '2026-05-24', '10:30:00', 'Uduthuththiripitiya Clinic', 'Dose 2', 'TT-2026-001', NULL, 'scheduled', 'Maternal tetanus vaccination');



-- CREATE OR REPLACE VIEW view_midwife_activity_summary AS
-- SELECT 
--     m.midwife_id,
--     m.full_name,
--     m.employee_id,
--     m.assigned_area,
--     COUNT(a.activity_id) AS total_activities,
--     COUNT(CASE WHEN DATE(a.activity_date) = CURDATE() THEN 1 END) AS activities_today,
--     COUNT(CASE WHEN DATE(a.activity_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) AS activities_this_week,
--     AVG(a.duration_minutes) AS avg_duration_minutes,
--     MAX(a.activity_date) AS last_activity_date,
--     m.status AS midwife_status
-- FROM midwives m
-- LEFT JOIN activities a ON m.midwife_id = a.midwife_id
-- GROUP BY m.midwife_id, m.full_name, m.employee_id, m.assigned_area, m.status;

-- CREATE OR REPLACE VIEW view_daily_activities AS
-- SELECT 
--     a.activity_date,
--     m.full_name AS midwife_name,
--     m.assigned_area,
--     at.type_name AS activity_type,
--     a.patient_name,
--     a.location,
--     a.duration_minutes,
--     a.status,
--     a.priority_level
-- FROM activities a
-- JOIN midwives m ON a.midwife_id = m.midwife_id
-- JOIN activity_types at ON a.activity_type_id = at.type_id
-- ORDER BY a.activity_date DESC, a.start_time ASC;

-- CREATE OR REPLACE VIEW view_performance_dashboard AS
-- SELECT 
--     m.midwife_id,
--     m.full_name,
--     m.assigned_area,
--     pm.metric_date,
--     pm.total_activities,
--     pm.completion_rate,
--     pm.punctuality_score,
--     pm.patient_satisfaction,
--     CASE 
--         WHEN pm.completion_rate >= 95 THEN 'Excellent'
--         WHEN pm.completion_rate >= 85 THEN 'Good'
--         WHEN pm.completion_rate >= 75 THEN 'Satisfactory'
--         ELSE 'Needs Improvement'
--     END AS performance_rating
-- FROM midwives m
-- LEFT JOIN performance_metrics pm ON m.midwife_id = pm.midwife_id
-- WHERE pm.metric_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) OR pm.metric_date IS NULL;

-- ALTER TABLE `moh_admins` AUTO_INCREMENT = 3;
-- ALTER TABLE `midwives` AUTO_INCREMENT = 11;
-- ALTER TABLE `activity_types` AUTO_INCREMENT = 22;
-- ALTER TABLE `activities` AUTO_INCREMENT = 74;
-- ALTER TABLE `patients` AUTO_INCREMENT = 26;
-- ALTER TABLE `notifications` AUTO_INCREMENT = 5;
-- ALTER TABLE `system_settings` AUTO_INCREMENT = 10;
-- ALTER TABLE `audit_logs` AUTO_INCREMENT = 3;
-- ALTER TABLE `home_visits` AUTO_INCREMENT = 11;
-- ALTER TABLE vaccine_inventory AUTO_INCREMENT = 6;
-- ALTER TABLE vaccination_records AUTO_INCREMENT = 3;

