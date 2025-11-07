# Database Setup Guide - Token Ujian System

## 📋 Overview
Dokumen ini menjelaskan cara setup database untuk sistem token ujian SMK Mutu PPU.

## 🔧 Requirements
- MySQL/MariaDB versi 5.7 atau lebih tinggi
- Akses root atau user dengan privileges untuk membuat database
- PHP MySQL extension (mysqli)

## 🚀 Quick Setup

### 1. Import Database Structure
```bash
# Via MySQL CLI
mysql -u root -p < database.sql

# Via PHPMyAdmin
# 1. Buka PHPMyAdmin
# 2. Pilih "Import"
# 3. Pilih file database.sql
# 4. Klik "Go"
```

### 2. Update Configuration
Edit file `config.php` dan sesuaikan database credentials:

```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Ganti dengan database user
define('DB_PASS', 'your_password'); // Ganti dengan database password
define('DB_NAME', 'token_system');
```

## 📊 Database Structure

### Tables Overview:

#### `tokens`
Menyimpan informasi token ujian
- `id` - Primary key
- `token_value` - Token 5 karakter (unik)
- `is_active` - Status aktif token
- `expires_at` - Waktu kadaluarsa
- `created_at` - Waktu pembuatan
- `updated_at` - Waktu update terakhir

#### `access_logs`
Mencatat semua percobaan akses
- `id` - Primary key
- `token_id` - Foreign key ke tokens
- `ip_address` - IP address pengguna
- `user_agent` - Browser information
- `status` - Status akses (success/failed)
- `access_time` - Waktu akses

#### `admin_users` (Enhancement)
Manajemen user admin
- `id` - Primary key
- `username` - Username admin
- `password_hash` - Password hash
- `email` - Email admin
- `is_active` - Status aktif
- `last_login` - Login terakhir

#### `system_settings` (Enhancement)
Konfigurasi sistem
- `id` - Primary key
- `setting_key` - Key konfigurasi
- `setting_value` - Value konfigurasi
- `description` - Deskripsi setting

## 🔐 Security Configuration

### Database User Creation (Recommended)
```sql
-- Create dedicated database user
CREATE USER 'token_user'@'localhost' IDENTIFIED BY 'secure_password_here';

-- Grant necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON token_system.* TO 'token_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;
```

### Update config.php dengan dedicated user:
```php
define('DB_USER', 'token_user');
define('DB_PASS', 'secure_password_here');
```

## 📈 Advanced Features

### Views untuk Reporting
1. **active_tokens_stats** - Statistik token aktif
2. **recent_access_logs** - Log akses terkini

### Stored Procedures
1. **CleanupOldLogs(days)** - Bersihkan log lama
2. **GetTokenStatistics(token_id)** - Dapatkan statistik token

### Automated Events
1. **cleanup_old_logs_event** - Auto cleanup setiap hari
2. **deactivate_expired_tokens_event** - Auto deactivate token kadaluarsa

## 🛠️ Maintenance Commands

### Manual Cleanup
```sql
-- Cleanup logs older than 30 days
CALL CleanupOldLogs(30);

-- Get statistics for specific token
CALL GetTokenStatistics(1);
```

### Check Database Status
```sql
-- Check active tokens
SELECT COUNT(*) as active_tokens FROM tokens WHERE is_active = TRUE;

-- Check recent failed attempts
SELECT COUNT(*) as failed_attempts
FROM access_logs
WHERE status = 'failed'
AND access_time > DATE_SUB(NOW(), INTERVAL 1 HOUR);

-- Check token usage statistics
SELECT
    token_value,
    expires_at,
    COUNT(CASE WHEN al.status = 'success' THEN 1 END) as successful_uses
FROM tokens t
LEFT JOIN access_logs al ON t.id = al.token_id
WHERE t.is_active = TRUE
GROUP BY t.id, t.token_value, t.expires_at;
```

## 🔍 Testing Setup

### 1. Test Database Connection
Buat file `test_db.php`:
```php
<?php
require_once 'config.php';

try {
    $conn = getDbConnection();
    echo "Database connection successful!";

    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM tokens");
    $row = $result->fetch_assoc();
    echo "Total tokens: " . $row['count'];

    $conn->close();
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
```

### 2. Test Token Generation
```php
<?php
require_once 'config.php';

// Generate test token
$token = createNewToken();
echo "Generated token: " . $token['token'];
echo "Expires at: " . $token['expires_at'];
?>
```

## 📝 Important Notes

### Security Best Practices:
1. **Change default passwords** immediately after setup
2. **Use strong database passwords**
3. **Limit database user permissions**
4. **Regular database backups**
5. **Monitor access logs**

### Performance Optimization:
1. **Database indexes** sudah di-setup untuk performa optimal
2. **Regular cleanup** prevents table bloat
3. **Stored procedures** reduce query overhead

### Backup Strategy:
```bash
# Full database backup
mysqldump -u root -p token_system > backup_$(date +%Y%m%d).sql

# Compressed backup
mysqldump -u root -p token_system | gzip > backup_$(date +%Y%m%d).sql.gz
```

## 🆘 Troubleshooting

### Common Issues:

#### 1. Connection Failed
```bash
# Check MySQL service
sudo systemctl status mysql

# Restart MySQL
sudo systemctl restart mysql
```

#### 2. Permission Denied
```sql
-- Check user permissions
SHOW GRANTS FOR 'token_user'@'localhost';

-- Re-grant permissions
GRANT ALL PRIVILEGES ON token_system.* TO 'token_user'@'localhost';
```

#### 3. Table Not Found
```sql
-- Check if database exists
SHOW DATABASES LIKE 'token_system';

-- Check tables
USE token_system;
SHOW TABLES;
```

## 📞 Support

Jika mengalami masalah dengan setup database:
1. Periksa error logs MySQL
2. Verifikasi database credentials
3. Pastikan MySQL service running
4. Check firewall settings

---

**Version**: 1.0
**Last Updated**: 2025-11-07
**Compatible**: MySQL 5.7+, MariaDB 10.2+