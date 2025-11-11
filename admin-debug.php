<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session BEFORE any output
session_start();

echo "<h1>Debug Mode</h1>";

// Test if PHP is working
echo "<p>✅ PHP is working</p>";

// Check if config.php exists
if (file_exists('config.php')) {
    echo "<p>✅ config.php exists</p>";
} else {
    echo "<p>❌ config.php NOT found</p>";
    die("config.php is missing");
}

// Try to include config.php
try {
    require_once 'config.php';
    echo "<p>✅ config.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p>❌ Error loading config.php: " . $e->getMessage() . "</p>";
    die("config.php load failed");
}

// Test database connection
try {
    $conn = getDbConnection();
    echo "<p>✅ Database connection successful</p>";

    // Test if tables exist and have data
    $tables_result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $tables_result->fetch_array()) {
        $tables[] = $row[0];
    }
    echo "<p>Tables found: " . implode(', ', $tables) . "</p>";

    // Test token count
    $token_result = $conn->query("SELECT COUNT(*) as count FROM tokens");
    $token_count = $token_result->fetch_assoc()['count'];
    echo "<p>Active tokens: $token_count</p>";

    $conn->close();
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test session (already started above)
echo "<p>✅ Session started successfully</p>";

// Check login
if (isset($_POST['admin_password'])) {
    echo "<p>🔑 Login attempt detected</p>";
    echo "<p>Password entered: " . $_POST['admin_password'] . "</p>";

    if ($_POST['admin_password'] === 'indonesia2025') {
        $_SESSION['admin_logged_in'] = true;
        echo "<p>✅ Login successful!</p>";
        echo "<p><a href='admin.php'>Go to admin panel</a></p>";
    } else {
        echo "<p>❌ Invalid password!</p>";
    }
} else {
    echo "<p>ℹ️ No login attempt detected</p>";
}

// Show current login status
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
echo "<p>Login status: " . ($isLoggedIn ? "Logged in" : "Not logged in") . "</p>";

// Show form if not logged in
if (!$isLoggedIn):
?>
<form method="post" action="">
    <input type="password" name="admin_password" placeholder="Enter password" required>
    <button type="submit">Login</button>
</form>
<?php else: ?>
    <p><a href="admin.php">Proceed to Admin Panel</a></p>
    <p><a href="logout.php">Logout</a></p>
<?php endif; ?>

<p><a href="admin.php">Test admin.php</a></p>
<p><a href="admin-smkmutu.php">Test admin-smkmutu.php</a></p>

<?php
// Check for syntax issues in admin files
echo "<h2>Checking admin file syntax...</h2>";

$admin_files = ['admin.php', 'admin-smkmutu.php'];
foreach ($admin_files as $file) {
    echo "<h3>Checking $file...</h3>";

    if (file_exists($file)) {
        $content = file_get_contents($file);

        // Check for special characters that cause issues
        if (strpos($content, 'ß') !== false) {
            echo "<p style='color: red;'>❌ $file contains problematic character 'ß'</p>";
        } else {
            echo "<p style='color: green;'>✅ No problematic characters found</p>";
        }

        // Check for proper PHP structure
        if (strpos($content, '<?php') === 0) {
            echo "<p style='color: green;'>✅ Valid PHP opening tag</p>";
        } else {
            echo "<p style='color: red;'>❌ Invalid PHP opening</p>";
        }

        // Check for session_start
        if (strpos($content, 'session_start') !== false) {
            echo "<p style='color: green;'>✅ session_start found</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ session_start not found</p>";
        }

        // Check for config inclusion
        if (strpos($content, 'config.php') !== false) {
            echo "<p style='color: green;'>✅ config.php included</p>";
        } else {
            echo "<p style='color: red;'>❌ config.php not included</p>";
        }

    } else {
        echo "<p style='color: red;'>❌ $file not found</p>";
    }
}
?>

</body>
</html>