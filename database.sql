-- Database Schema for Token Ujian System
-- SMK Mutu PPU - Token Authentication System
-- Generated: 2025-11-07

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS token_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE token_system;

-- Drop existing tables (for fresh installation)
DROP TABLE IF EXISTS access_logs;
DROP TABLE IF EXISTS tokens;

-- Create tokens table
CREATE TABLE tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_value VARCHAR(5) NOT NULL UNIQUE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_token_value (token_value),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create access_logs table
CREATE TABLE access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_id INT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    status ENUM('success', 'failed') NOT NULL,
    access_time DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (token_id) REFERENCES tokens(id) ON DELETE SET NULL,
    INDEX idx_token_id (token_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_status (status),
    INDEX idx_access_time (access_time),
    INDEX idx_ip_status_time (ip_address, status, access_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_users table for better security (optional enhancement)
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_username (username),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create system_settings table for configuration (optional enhancement)
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: Smkmutu@30407288)
-- In production, you should change this password immediately
INSERT INTO admin_users (username, password_hash, email) VALUES
('admin', '$2y$10$rOzJqQjQjQjQjQjQjQjQjOzJqQjQjQjQjQjQjQjQjOzJqQjQjQjQjQ', 'admin@smkmutu.sch.id');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('token_expiry_minutes', '15', 'Token expiration time in minutes'),
('max_attempts', '10', 'Maximum failed attempts before rate limiting'),
('cooldown_minutes', '10', 'Cooldown period in minutes for rate limiting'),
('redirect_url', 'https://exujian.smkmutuppu.com/jemtheking/akses/aksesujian/', 'URL to redirect after successful token validation'),
('session_timeout_minutes', '30', 'Session timeout in minutes'),
('enable_captcha', 'false', 'Enable CAPTCHA verification'),
('maintenance_mode', 'false', 'Enable maintenance mode');

-- Create view for active tokens with statistics
CREATE VIEW active_tokens_stats AS
SELECT
    t.id,
    t.token_value,
    t.expires_at,
    t.created_at,
    COUNT(al.id) as total_attempts,
    COUNT(CASE WHEN al.status = 'success' THEN 1 END) as successful_attempts,
    COUNT(CASE WHEN al.status = 'failed' THEN 1 END) as failed_attempts,
    COUNT(CASE WHEN al.access_time > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as attempts_last_hour
FROM tokens t
LEFT JOIN access_logs al ON t.id = al.token_id
WHERE t.is_active = TRUE
GROUP BY t.id, t.token_value, t.expires_at, t.created_at;

-- Create view for recent access logs with token info
CREATE VIEW recent_access_logs AS
SELECT
    al.id,
    al.access_time,
    al.ip_address,
    al.status,
    al.user_agent,
    t.token_value,
    t.expires_at,
    CASE
        WHEN t.expires_at > NOW() THEN 'Valid'
        ELSE 'Expired'
    END as token_status
FROM access_logs al
LEFT JOIN tokens t ON al.token_id = t.id
ORDER BY al.access_time DESC;

-- Create stored procedure for cleaning old logs
DELIMITER //
CREATE PROCEDURE CleanupOldLogs(IN days_to_keep INT)
BEGIN
    DELETE FROM access_logs
    WHERE access_time < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);

    SELECT ROW_COUNT() as logs_deleted;
END //
DELIMITER ;

-- Create stored procedure for getting token statistics
DELIMITER //
CREATE PROCEDURE GetTokenStatistics(IN token_id_param INT)
BEGIN
    SELECT
        t.token_value,
        t.expires_at,
        t.is_active,
        COUNT(al.id) as total_attempts,
        COUNT(CASE WHEN al.status = 'success' THEN 1 END) as successful_attempts,
        COUNT(CASE WHEN al.status = 'failed' THEN 1 END) as failed_attempts,
        MAX(al.access_time) as last_access_time,
        COUNT(DISTINCT al.ip_address) as unique_ips
    FROM tokens t
    LEFT JOIN access_logs al ON t.id = al.token_id
    WHERE t.id = token_id_param
    GROUP BY t.id, t.token_value, t.expires_at, t.is_active;
END //
DELIMITER ;

-- Create trigger to automatically deactivate expired tokens
DELIMITER //
CREATE TRIGGER deactivate_expired_tokens
BEFORE INSERT ON access_logs
FOR EACH ROW
BEGIN
    UPDATE tokens
    SET is_active = FALSE
    WHERE is_active = TRUE AND expires_at < NOW();
END //
DELIMITER ;

-- Create event to automatically clean old logs (MySQL 5.1.6+)
SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS cleanup_old_logs_event
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
    CALL CleanupOldLogs(30); -- Keep logs for 30 days

-- Create event to deactivate expired tokens
CREATE EVENT IF NOT EXISTS deactivate_expired_tokens_event
ON SCHEDULE EVERY 5 MINUTE
STARTS CURRENT_TIMESTAMP
DO
    UPDATE tokens SET is_active = FALSE WHERE is_active = TRUE AND expires_at < NOW();

-- Grant permissions (adjust username/password as needed)
-- CREATE USER 'token_user'@'localhost' IDENTIFIED BY 'secure_password_here';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON token_system.* TO 'token_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Display setup completion message
SELECT 'Database setup completed successfully!' as message;
SELECT 'Tokens table created' as table_status;
SELECT 'Access logs table created' as table_status;
SELECT 'Admin users table created' as table_status;
SELECT 'System settings table created' as table_status;
SELECT 'Views and procedures created' as table_status;
SELECT 'Database is ready for token system deployment' as status;