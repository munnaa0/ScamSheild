
CREATE DATABASE IF NOT EXISTS scamshield_db;
USE scamshield_db;


CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('user', 'moderator', 'admin') DEFAULT 'user',
    last_login TIMESTAMP NULL,
    is_banned BOOLEAN DEFAULT FALSE,
    banned_at TIMESTAMP NULL,
    banned_by INT NULL,
    ban_reason TEXT NULL,
    
    FOREIGN KEY (banned_by) REFERENCES users(user_id) ON DELETE SET NULL
);


CREATE TABLE scam_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) UNIQUE NOT NULL,
    category_slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE scam_reports (
    report_id INT PRIMARY KEY AUTO_INCREMENT,
    reporter_user_id INT NULL,
    reporter_name VARCHAR(100),
    reporter_email VARCHAR(100),
    scam_title VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    scam_description TEXT NOT NULL,
    date_occurred DATE,
    amount_lost DECIMAL(12, 2) DEFAULT 0.00,
    scammer_email VARCHAR(100),
    scammer_phone VARCHAR(20),
    scammer_website VARCHAR(255),
    additional_contacts TEXT,
    evidence_description TEXT,
    reporter_location VARCHAR(100),
    scammer_location VARCHAR(100),
    reported_elsewhere ENUM('police', 'fbi', 'ftc', 'bank', 'platform', 'multiple', 'none'),
    additional_notes TEXT,
    consent_given BOOLEAN NOT NULL DEFAULT FALSE,
    make_anonymous BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'verified', 'investigating', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    admin_notes TEXT NULL,
    
    FOREIGN KEY (reporter_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES scam_categories(category_id),
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL
);


CREATE TABLE report_evidence (
    evidence_id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (report_id) REFERENCES scam_reports(report_id) ON DELETE CASCADE
);


CREATE TABLE contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject ENUM('general-inquiry', 'report-issue', 'verification-question', 'media-inquiry', 'partnership', 'technical-support', 'feedback', 'other') NOT NULL,
    priority ENUM('normal', 'high', 'urgent') DEFAULT 'normal',
    message TEXT NOT NULL,
    newsletter_subscription BOOLEAN DEFAULT FALSE,
    status ENUM('new', 'in-progress', 'resolved', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_to INT NULL,
    resolved_by INT NULL,
    resolved_at TIMESTAMP NULL,
    admin_reply TEXT NULL,
    
    FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(user_id) ON DELETE SET NULL
);


CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100),
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);


CREATE TABLE blog_posts (
    post_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE admin_activity_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_description TEXT NOT NULL,
    target_type VARCHAR(50) NULL,
    target_id INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
);


CREATE TABLE report_status_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    old_status ENUM('pending', 'verified', 'investigating', 'rejected') NULL,
    new_status ENUM('pending', 'verified', 'investigating', 'rejected') NOT NULL,
    changed_by_user_id INT NOT NULL,
    change_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (report_id) REFERENCES scam_reports(report_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_report_id (report_id),
    INDEX idx_changed_by (changed_by_user_id)
);


CREATE TABLE admin_notes (
    note_id INT PRIMARY KEY AUTO_INCREMENT,
    note_type ENUM('report', 'user', 'message', 'general') NOT NULL,
    target_id INT NOT NULL,
    admin_user_id INT NOT NULL,
    note_content TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (admin_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_note_type_target (note_type, target_id),
    INDEX idx_admin_user (admin_user_id)
);
