-- Sample Data for Token Ujian System
-- SMK Mutu PPU - Sample Data for Testing
-- Generated: 2025-11-07

USE token_system;

-- Insert sample tokens (for testing purposes)
-- These tokens will be automatically deactivated when they expire

-- Active token (expires in 15 minutes from now)
INSERT INTO tokens (token_value, is_active, expires_at, created_at) VALUES
('ABC12', TRUE, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW()),
('XYZ98', TRUE, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW()),
('DEF45', TRUE, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW());

-- Expired tokens (for testing expired token handling)
INSERT INTO tokens (token_value, is_active, expires_at, created_at) VALUES
('OLD01', FALSE, DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('OLD02', FALSE, DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('OLD03', FALSE, DATE_SUB(NOW(), INTERVAL 3 HOUR), DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- Insert sample access logs for demonstration
-- Recent successful accesses
INSERT INTO access_logs (token_id, ip_address, user_agent, status, access_time) VALUES
(1, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
(1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', 'success', DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
(2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36', 'success', DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(3, '192.168.1.103', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X)', 'success', DATE_SUB(NOW(), INTERVAL 20 MINUTE));

-- Recent failed attempts (for testing rate limiting)
INSERT INTO access_logs (token_id, ip_address, user_agent, status, access_time) VALUES
(NULL, '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'failed', NOW()),
(NULL, '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'failed', DATE_SUB(NOW(), INTERVAL 1 MINUTE)),
(NULL, '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'failed', DATE_SUB(NOW(), INTERVAL 2 MINUTE)),
(NULL, '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'failed', DATE_SUB(NOW(), INTERVAL 3 MINUTE)),
(NULL, '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'failed', DATE_SUB(NOW(), INTERVAL 4 MINUTE)),
(NULL, '192.168.1.201', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', 'failed', DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
(NULL, '192.168.1.201', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', 'failed', DATE_SUB(NOW(), INTERVAL 6 MINUTE)),
(NULL, '192.168.1.201', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', 'failed', DATE_SUB(NOW(), INTERVAL 7 MINUTE));

-- Historical data (older logs)
INSERT INTO access_logs (token_id, ip_address, user_agent, status, access_time) VALUES
(4, '10.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'success', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, '10.0.0.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', 'failed', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, '10.0.0.3', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36', 'success', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, '10.0.0.4', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X)', 'failed', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6, '10.0.0.5', 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0', 'success', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Additional test users (for enhanced admin system)
INSERT INTO admin_users (username, password_hash, email, is_active) VALUES
('admin', '$2y$10$rOzJqQjQjQjQjQjQjQjQjOzJqQjQjQjQjQjQjQjQjOzJqQjQjQjQjQ', 'admin@smkmutu.sch.id', TRUE),
('teacher1', '$2y$10$AbCdEfGhIjKlMnOpQrStUvWxYz1234567890ABCDEFghijklmn', 'teacher1@smkmutu.sch.id', TRUE),
('teacher2', '$2y$10$XyZaBcDeFgHiJkLmNoPqRsTuVwXyZaBcDeFgHiJkLmNoPqRsTu', 'teacher2@smkmutu.sch.id', TRUE);

-- Additional system settings for testing
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('max_active_tokens', '5', 'Maximum number of active tokens allowed'),
('token_length', '5', 'Length of generated tokens'),
('allow_token_reuse', 'false', 'Allow multiple uses of same token'),
('log_retention_days', '30', 'Number of days to keep access logs'),
('admin_session_timeout', '60', 'Admin session timeout in minutes'),
('enable_ip_whitelist', 'false', 'Enable IP whitelist feature'),
('failed_attempt_threshold', '5', 'Failed attempts before temporary ban'),
('temporary_ban_duration', '15', 'Duration of temporary ban in minutes');

-- Create sample views for testing
CREATE OR REPLACE VIEW sample_token_usage AS
SELECT
    t.token_value,
    t.expires_at,
    CASE
        WHEN t.expires_at > NOW() THEN 'Active'
        ELSE 'Expired'
    END as status,
    COUNT(al.id) as total_usage,
    COUNT(CASE WHEN al.status = 'success' THEN 1 END) as successful_usage,
    COUNT(CASE WHEN al.status = 'failed' THEN 1 END) as failed_usage,
    COUNT(DISTINCT al.ip_address) as unique_users
FROM tokens t
LEFT JOIN access_logs al ON t.id = al.token_id
GROUP BY t.id, t.token_value, t.expires_at
ORDER BY t.created_at DESC;

CREATE OR REPLACE VIEW sample_recent_activity AS
SELECT
    al.access_time,
    al.ip_address,
    al.status,
    t.token_value,
    CASE
        WHEN al.status = 'success' THEN '✅'
        ELSE '❌'
    END as status_icon,
    CASE
        WHEN al.user_agent LIKE '%Windows%' THEN 'Windows'
        WHEN al.user_agent LIKE '%Mac%' THEN 'macOS'
        WHEN al.user_agent LIKE '%Linux%' THEN 'Linux'
        WHEN al.user_agent LIKE '%iPhone%' THEN 'iPhone'
        WHEN al.user_agent LIKE '%Android%' THEN 'Android'
        ELSE 'Other'
    END as device_type
FROM access_logs al
LEFT JOIN tokens t ON al.token_id = t.id
ORDER BY al.access_time DESC
LIMIT 20;

-- Sample stored procedures for testing
DELIMITER //
CREATE PROCEDURE GenerateSampleTokens(IN count INT)
BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE random_token VARCHAR(5);

    WHILE i < count DO
        SET random_token = CONCAT(
            CHAR(65 + FLOOR(RAND() * 26)),  -- Random A-Z
            CHAR(65 + FLOOR(RAND() * 26)),  -- Random A-Z
            FLOOR(RAND() * 10),             -- Random 0-9
            FLOOR(RAND() * 10),             -- Random 0-9
            CHAR(65 + FLOOR(RAND() * 26))   -- Random A-Z
        );

        INSERT INTO tokens (token_value, is_active, expires_at, created_at)
        VALUES (random_token, TRUE, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW());

        SET i = i + 1;
    END WHILE;

    SELECT CONCAT('Generated ', i, ' sample tokens') as result;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE SimulateAccessAttempts(IN token_value VARCHAR(5), IN attempts INT)
BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE random_ip VARCHAR(15);
    DECLARE success_count INT DEFAULT 0;

    WHILE i < attempts DO
        SET random_ip = CONCAT(
            FLOOR(RAND() * 256), '.',
            FLOOR(RAND() * 256), '.',
            FLOOR(RAND() * 256), '.',
            FLOOR(RAND() * 256)
        );

        INSERT INTO access_logs (token_id, ip_address, user_agent, status, access_time)
        SELECT
            t.id,
            random_ip,
            'Sample User Agent',
            CASE
                WHEN RAND() > 0.3 THEN 'success'
                ELSE 'failed'
            END,
            DATE_SUB(NOW(), INTERVAL i MINUTE)
        FROM tokens t
        WHERE t.token_value = token_value AND t.is_active = TRUE
        LIMIT 1;

        IF RAND() > 0.3 THEN
            SET success_count = success_count + 1;
        END IF;

        SET i = i + 1;
    END WHILE;

    SELECT CONCAT('Simulated ', attempts, ' access attempts with ', success_count, ' successes') as result;
END //
DELIMITER ;

-- Sample data verification queries
SELECT '=== Sample Data Verification ===' as info;

SELECT 'Active Tokens:' as info;
SELECT * FROM tokens WHERE is_active = TRUE;

SELECT 'Recent Access Logs (Last 5):' as info;
SELECT * FROM access_logs ORDER BY access_time DESC LIMIT 5;

SELECT 'Token Usage Statistics:' as info;
SELECT * FROM sample_token_usage;

SELECT 'Recent Activity (Last 10):' as info;
SELECT * FROM sample_recent_activity LIMIT 10;

SELECT 'System Settings:' as info;
SELECT setting_key, setting_value, description FROM system_settings;

SELECT 'Admin Users:' as info;
SELECT username, email, is_active, last_login FROM admin_users;

SELECT '=== Sample Data Setup Complete ===' as info;
SELECT 'You can now test the system with sample data.' as message;
SELECT 'Active tokens: ABC12, XYZ98, DEF45' as active_tokens;
SELECT 'Use these tokens for testing authentication.' as instruction;