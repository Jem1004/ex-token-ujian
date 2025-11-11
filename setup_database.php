<?php
/**
 * Database Setup Script for Token Ujian System
 * SMK Mutu PPU - Automated Database Setup
 *
 * This script will automatically set up the database structure
 * and populate it with sample data for testing.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration - update these values
$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'token_system';

// Colors for terminal output
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

function echoSuccess($message) {
    echo COLOR_GREEN . "[✓] " . $message . COLOR_RESET . "\n";
}

function echoError($message) {
    echo COLOR_RED . "[✗] " . $message . COLOR_RESET . "\n";
}

function echoInfo($message) {
    echo COLOR_BLUE . "[ℹ] " . $message . COLOR_RESET . "\n";
}

function echoWarning($message) {
    echo COLOR_YELLOW . "[⚠] " . $message . COLOR_RESET . "\n";
}

function executeSQLFile($filename, $connection) {
    if (!file_exists($filename)) {
        echoError("File not found: $filename");
        return false;
    }

    $sql = file_get_contents($filename);

    // Remove comments and split into statements
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $connection->query($statement);
            } catch (Exception $e) {
                echoError("SQL Error: " . $e->getMessage());
                echoError("Statement: " . substr($statement, 0, 100) . "...");
                return false;
            }
        }
    }

    return true;
}

echo COLOR_BLUE . "========================================\n";
echo "    Token Ujian System - Database Setup\n";
echo "             SMK Mutu PPU\n";
echo "========================================" . COLOR_RESET . "\n\n";

// Step 1: Test MySQL connection
echoInfo("Testing MySQL connection...");
try {
    $test_conn = new mysqli($db_host, $db_user, $db_pass);
    if ($test_conn->connect_error) {
        throw new Exception($test_conn->connect_error);
    }
    echoSuccess("MySQL connection successful");
    $test_conn->close();
} catch (Exception $e) {
    echoError("MySQL connection failed: " . $e->getMessage());
    echoInfo("Please check your database credentials in setup_database.php");
    exit(1);
}

// Step 2: Create database if not exists
echoInfo("Creating database '$db_name' if not exists...");
try {
    $conn = new mysqli($db_host, $db_user, $db_pass);
    $conn->query("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echoSuccess("Database '$db_name' is ready");
    $conn->close();
} catch (Exception $e) {
    echoError("Failed to create database: " . $e->getMessage());
    exit(1);
}

// Step 3: Connect to the target database
echoInfo("Connecting to database '$db_name'...");
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    echoSuccess("Connected to database '$db_name'");
} catch (Exception $e) {
    echoError("Failed to connect to database: " . $e->getMessage());
    exit(1);
}

// Step 4: Import database structure
echoInfo("Importing database structure...");
if (executeSQLFile('database.sql', $conn)) {
    echoSuccess("Database structure imported successfully");
} else {
    echoError("Failed to import database structure");
    exit(1);
}

// Step 5: Import sample data (optional)
echoInfo("Do you want to import sample data for testing? (y/n): ");
$handle = fopen("php://stdin", "r");
$import_sample = trim(fgets($handle));
fclose($handle);

if (strtolower($import_sample) === 'y' || strtolower($import_sample) === 'yes') {
    echoInfo("Importing sample data...");
    if (executeSQLFile('sample_data.sql', $conn)) {
        echoSuccess("Sample data imported successfully");
    } else {
        echoWarning("Failed to import sample data (you can import it manually later)");
    }
} else {
    echoInfo("Skipping sample data import");
}

// Step 6: Verify setup
echoInfo("\nVerifying database setup...");
$tables = ['tokens', 'access_logs', 'admin_users', 'system_settings'];
$all_tables_exist = true;

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echoSuccess("Table '$table' exists");
    } else {
        echoError("Table '$table' is missing");
        $all_tables_exist = false;
    }
}

if ($all_tables_exist) {
    echoSuccess("All required tables are present");
} else {
    echoError("Some tables are missing");
}

// Step 7: Display sample data information
echoInfo("\nDatabase Statistics:");
$result = $conn->query("SELECT COUNT(*) as count FROM tokens WHERE is_active = TRUE");
$row = $result->fetch_assoc();
echoInfo("Active tokens: " . $row['count']);

$result = $conn->query("SELECT COUNT(*) as count FROM access_logs");
$row = $result->fetch_assoc();
echoInfo("Total access logs: " . $row['count']);

$result = $conn->query("SELECT COUNT(*) as count FROM admin_users");
$row = $result->fetch_assoc();
echoInfo("Admin users: " . $row['count']);

// Step 8: Create config.php if not exists
if (!file_exists('config.php')) {
    echoWarning("config.php not found. Creating a template...");
    $config_content = '<?php
// Database configuration
define(\'DB_HOST\', \'' . $db_host . '\');
define(\'DB_USER\', \'' . $db_user . '\');
define(\'DB_PASS\', \'' . $db_pass . '\');
define(\'DB_NAME\', \'' . $db_name . '\');

// Application settings
define(\'TOKEN_EXPIRY_MINUTES\', 15);
define(\'MAX_ATTEMPTS\', 10);
define(\'COOLDOWN_MINUTES\', 10);
define(\'REDIRECT_URL\', \'https://exujian.smkmutuppu.com/jemtheking/akses/aksesujian/\');

// Include functions from config.php
// Copy the rest of the functions from the original config.php file
?>';

    file_put_contents('config.template.php', $config_content);
    echoSuccess("Template config.template.php created");
    echoInfo("Please rename to config.php and add the functions from your original config.php");
}

// Step 9: Completion message
echo COLOR_BLUE . "\n========================================\n";
echo "         Setup Complete!\n";
echo "========================================" . COLOR_RESET . "\n";

echoSuccess("Database setup completed successfully!");
echoInfo("Next steps:");
echoInfo("1. Update config.php with your database credentials");
echoInfo("2. Test the system by accessing index.php");
echoInfo("3. Login to admin.php with default admin password");
echoWarning("SECURITY: Change default admin password immediately!");
echoInfo("\nAdmin credentials:");
echoInfo("Username: admin");
echoInfo("Password: Smkmutu@30407288");

// Cleanup
$conn->close();

echo COLOR_BLUE . "\nFor support, check README_DATABASE.md\n";
echo "========================================" . COLOR_RESET . "\n";
?>