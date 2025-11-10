# Token Ujian System - Troubleshooting Guide

## 🚨 Halaman Tidak Berfungsi ("This page isn't working")

### Langkah 1: Diagnosa dengan Debug Script

Buka `debug.php` di browser Anda:
```
http://your-domain.com/debug.php
```

Script ini akan memeriksa:
- ✅ Versi PHP dan extensions
- ✅ File permissions
- ✅ Koneksi database
- ✅ Struktur tabel
- ✅ Error logs

### Langkah 2: Setup Database (Jika Diperlukan)

Jika tabel tidak ada, jalankan:
```
http://your-domain.com/setup_database.php
```

### Masalah Umum dan Solusi

#### 1. Error 500 - Internal Server Error
**Kemungkinan penyebab:**
- Syntax error di PHP
- File permissions salah
- Memory limit terlampaui

**Solusi:**
```bash
# Cek error log
tail -f /var/log/apache2/error.log
# atau
tail -f /var/log/nginx/error.log

# Fix permissions
chmod 644 *.php
chmod 755 .
```

#### 2. Database Connection Failed
**Kemungkinan penyebab:**
- Kredensial database salah
- MySQL tidak running
- Database tidak ada

**Solusi:**
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection manually
mysql -h mysql -u root -p token_system
```

Update `config.php`:
```php
define('DB_HOST', 'localhost'); // atau '127.0.0.1'
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'token_system');
```

#### 3. Table Not Found
**Jalankan setup:**
```bash
# Via browser
http://your-domain.com/setup_database.php

# Via command line
php setup_database.php
```

#### 4. Session Issues
**Pastikan:**
- Session path writable
- Cookie domain benar
- HTTPS untuk production

#### 5. Permission Issues
**Set permissions yang benar:**
```bash
# Directories
find . -type d -exec chmod 755 {} \;

# Files
find . -type f -exec chmod 644 {} \;

# Owner (sesuaikan dengan web server)
chown -R www-data:www-data .
# atau
chown -R apache:apache .
```

### Checklist Debugging

- [ ] **PHP Version**: 7.4+ (recommended 8.0+)
- [ ] **Extensions**: mysqli, json, session
- [ ] **File Permissions**: 644 untuk PHP, 755 untuk directories
- [ ] **Database**: MySQL/MariaDB running
- [ ] **Tables**: tokens, access_logs exist
- [ ] **Config**: Kredensial database benar
- [ ] **Error Log**: Tidak ada error fatal

### Test Manual Components

#### Test Config Loading:
```php
<?php
include_once 'config.php';
echo "Config loaded successfully";
echo "DB_HOST: " . DB_HOST;
?>
```

#### Test Database Connection:
```php
<?php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Database connected successfully";
?>
```

#### Test Token Generation:
```php
<?php
include_once 'config.php';
$token = generateToken();
echo "Generated token: " . $token;
?>
```

### Common Error Messages

#### "Parse error: syntax error"
- Check PHP syntax
- Ensure no BOM (Byte Order Mark)
- Check matching brackets and quotes

#### "Connection failed: Access denied"
- Verify database credentials
- Check user privileges
- Ensure database exists

#### "Table 'tokens' doesn't exist"
- Run setup_database.php
- Check database name in config

#### "Cannot modify header information"
- Check for whitespace before `<?php`
- Ensure no output before headers
- Use output buffering

### Contact Support

Jika masalah berlanjut, sediakan:
1. Screenshot error message
2. Output dari `debug.php`
3. Server environment info
4. Recent changes made

---

## 📁 File Structure

```
token-exujian/
├── index.php              # Halaman utama (user)
├── admin-smkmutu.php      # Halaman admin
├── config.php             # Konfigurasi database
├── debug.php              # Debug script
├── setup_database.php     # Database setup
├── .htaccess              # Apache configuration
├── logo.png               # Logo SMK MUTU PPU
└── README_TROUBLESHOOTING.md
```

## 🔐 Security Notes

- **Change admin password**: Default password di admin.php
- **Use HTTPS**: Untuk production
- **Backup database**: Regular backups
- **Monitor logs**: Check access_logs regularly
- **Update dependencies**: Keep PHP and MySQL updated