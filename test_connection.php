<?php
// Simple database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Load config
try {
    require_once 'config.php';
    echo "<p style='color: green;'>✅ Config loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Config failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test connection
echo "<h2>Testing Connection...</h2>";
try {
    $conn = getDbConnection();
    echo "<p style='color: green;'>✅ Connected to MySQL successfully!</p>";
    echo "<p>Host: " . DB_HOST . "</p>";
    echo "<p>Database: " . DB_NAME . "</p>";
    echo "<p>User: " . DB_USER . "</p>";

    // Test tables
    $result = $conn->query("SHOW TABLES");
    if ($result && $result->num_rows > 0) {
        echo "<h3>Tables Found:</h3>";
        while ($row = $result->fetch_array()) {
            echo "<p>- " . $row[0] . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No tables found</p>";
    }

    // Test token functionality
    $result = $conn->query("SELECT COUNT(*) as count FROM tokens");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>Current tokens: $count</p>";
    }

    $conn->close();
    echo "<p style='color: green;'>✅ Connection closed successfully</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Check your MySQL server and credentials</p>";
}

echo "<hr>";
echo "<p><a href='admin-debug.php'>← Back to Admin Debug</a></p>";
echo "<p><a href='index.php'>← Back to Index</a></p>";
?>