<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Information - Token System</h1>";

// 1. Check PHP Version
echo "<h2>PHP Version</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// 2. Check Required Extensions
echo "<h2>PHP Extensions</h2>";
$required_extensions = ['mysqli', 'json', 'session'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '✅ Available' : '❌ Missing';
    echo "<p>{$ext}: {$status}</p>";
}

// 3. Check File Permissions
echo "<h2>File Permissions</h2>";
$files_to_check = ['config.php', 'index.php', 'admin-smkmutu.php'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        $readable = is_readable($file) ? '✅' : '❌';
        echo "<p>{$file}: {$perms} {$readable}</p>";
    } else {
        echo "<p>{$file}: ❌ File not found</p>";
    }
}

// 4. Test Config File
echo "<h2>Config File Test</h2>";
try {
    include_once 'config.php';
    echo "<p>✅ Config file loaded successfully</p>";

    // Show constants (without sensitive data)
    echo "<p>DB_HOST: " . DB_HOST . "</p>";
    echo "<p>DB_USER: " . DB_USER . "</p>";
    echo "<p>DB_NAME: " . DB_NAME . "</p>";
    echo "<p>TOKEN_EXPIRY_MINUTES: " . TOKEN_EXPIRY_MINUTES . "</p>";
    echo "<p>MAX_ATTEMPTS: " . MAX_ATTEMPTS . "</p>";
    echo "<p>REDIRECT_URL: " . REDIRECT_URL . "</p>";
} catch (Error $e) {
    echo "<p>❌ Error loading config: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Exception loading config: " . $e->getMessage() . "</p>";
}

// 5. Test Database Connection
echo "<h2>Database Connection Test</h2>";
try {
    $conn = getDbConnection();
    echo "<p>✅ Database connection successful</p>";

    // Check if tables exist
    $tables = ['tokens', 'access_logs'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '{$table}'");
        if ($result && $result->num_rows > 0) {
            echo "<p>✅ Table '{$table}' exists</p>";

            // Check table structure
            $structure = $conn->query("DESCRIBE {$table}");
            echo "<details><summary>Structure of {$table}</summary>";
            echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            while ($row = $structure->fetch_assoc()) {
                echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
            }
            echo "</table></details>";
        } else {
            echo "<p>❌ Table '{$table}' does not exist</p>";
        }
    }

    $conn->close();
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// 6. Test Token Generation
echo "<h2>Token Generation Test</h2>";
try {
    $token = generateToken();
    echo "<p>✅ Token generated: {$token}</p>";
} catch (Exception $e) {
    echo "<p>❌ Token generation failed: " . $e->getMessage() . "</p>";
}

// 7. Session Test
echo "<h2>Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<p>✅ Session started</p>";
echo "<p>Session ID: " . session_id() . "</p>";

// 8. Current Directory and Files
echo "<h2>Current Directory</h2>";
echo "<p>Current working directory: " . getcwd() . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// 9. Error Log (last few lines)
echo "<h2>Recent Error Log</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $lines = file($error_log);
    $recent_lines = array_slice($lines, -10); // Last 10 lines
    foreach ($recent_lines as $line) {
        echo "<p style='font-family: monospace; font-size: 12px;'>" . htmlspecialchars($line) . "</p>";
    }
} else {
    echo "<p>No error log found or not accessible</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li>1. If any PHP extensions are missing, install them</li>";
echo "<li>2. If database connection fails, check credentials and MySQL service</li>";
echo "<li>3. If tables are missing, run the setup script</li>";
echo "<li>4. Check file permissions - should be 644 for PHP files</li>";
echo "<li>5. Review error logs for detailed information</li>";
echo "</ul>";
?>