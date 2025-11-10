<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

// Test different hosts
$possible_hosts = ['localhost', '127.0.0.1', 'mysql', 'localhost:3306'];

foreach ($possible_hosts as $host) {
    echo "<h3>Testing host: {$host}</h3>";

    try {
        $conn = new mysqli($host, 'root', 'token_exujian_2025_dev', 'token_system');

        if ($conn->connect_error) {
            echo "<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>✅ Connection successful!</p>";

            // Test if tables exist
            $tables_result = $conn->query("SHOW TABLES");
            $tables = [];
            while ($row = $tables_result->fetch_array()) {
                $tables[] = $row[0];
            }

            echo "<p>Tables found: " . implode(', ', $tables) . "</p>";

            // Test token generation
            if (function_exists('generateToken')) {
                $token = generateToken();
                echo "<p>Token generation test: {$token}</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Token generation function not found</p>";
            }

            $conn->close();
            break; // Stop testing other hosts if we found a working one
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
    }

    echo "<hr>";
}

// Test current config
echo "<h2>Current Config Test</h2>";
try {
    include_once 'config.php';

    $conn = getDbConnection();
    echo "<p style='color: green;'>✅ Config-based connection successful!</p>";
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Config-based connection failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Server Info</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>MySQL Client: " . (function_exists('mysqli_get_client_info') ? mysqli_get_client_info() : 'Not available') . "</p>";
echo "<p>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
?>