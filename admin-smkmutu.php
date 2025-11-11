<?php
require_once 'config.php';

// Check if admin is logged in
session_start();
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle login
if (isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === 'indonesia2025') {
        $_SESSION['admin_logged_in'] = true;
        $isLoggedIn = true;
    } else {
        $loginError = "Invalid password";
    }
}

// Get current token and logs only if logged in
$currentToken = null;
$logs = [];

// Add this after the existing code that gets the current token
if ($isLoggedIn) {
    // Handle token generation
    if (isset($_POST['generate_token'])) {
        $newToken = createNewToken();
        $successMessage = "New token generated successfully!";
    }

    // Handle rate limit reset
    if (isset($_POST['reset_rate_limit']) && isset($_POST['ip_address'])) {
        resetRateLimiting($_POST['ip_address']);
        $successMessage = "Rate limit reset for IP: " . $_POST['ip_address'];
    }

    // Get current token and logs with a single database connection
    try {
        $conn = getDbConnection();
    } catch (Exception $e) {
        $errorMessage = "Database connection failed: " . $e->getMessage();
        $conn = null;
    }

    // Get current token
    if ($conn) {
        try {
            $result = $conn->query("SELECT id, token_value, expires_at, created_at FROM tokens WHERE is_active = TRUE ORDER BY created_at DESC LIMIT 1");
              if ($result && $result->num_rows > 0) {
                $currentToken = $result->fetch_assoc();

                // Check if token has expired and auto-regenerate if needed
                if (strtotime($currentToken['expires_at']) <= time()) {
                    $newToken = createNewToken();
                    $successMessage = "Token has expired and been automatically regenerated!";

                    // Refresh the current token data
                    $result = $conn->query("SELECT id, token_value, expires_at, created_at FROM tokens WHERE is_active = TRUE ORDER BY created_at DESC LIMIT 1");
                    if ($result && $result->num_rows > 0) {
                        $currentToken = $result->fetch_assoc();
                    }
                }
            }

            // Get recent access logs with a more efficient query
            $logResult = $conn->query("SELECT al.access_time, al.ip_address, al.status, t.token_value
                                      FROM access_logs al
                                      LEFT JOIN tokens t ON al.token_id = t.id
                                      ORDER BY access_time DESC LIMIT 20");

            if ($logResult && $logResult->num_rows > 0) {
                while ($row = $logResult->fetch_assoc()) {
                    $logs[] = $row;
                }
            }

            $conn->close();
        } catch (Exception $e) {
            $errorMessage = "Database query failed: " . $e->getMessage();
            if ($conn) {
                $conn->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Admin - SMK MUTU PPU</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Variables */
        :root {
            --primary-blue: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --accent-blue: #1e3a8a;
            --success-blue: #0ea5e9;
            --success-green: #2ecc71;
            --warning-amber: #fbbf24;
            --danger-red: #ef4444;
            --pure-white: #ffffff;
            --light-gray: #f9fafb;
            --medium-gray: #e5e7eb;
            --dark-gray: #374151;
            --text-dark: #111827;
            --text-light: #6b7280;
            --shadow-sm: 0 1px 2px 0 rgba(37, 99, 235, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(37, 99, 235, 0.1), 0 2px 4px -1px rgba(37, 99, 235, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(37, 99, 235, 0.1), 0 4px 6px -2px rgba(37, 99, 235, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 100%;
            padding: 15px;
            margin: 0 auto;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
            color: #fff;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .header-icon {
            font-size: 2rem;
            margin-bottom: 12px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        h1 {
            font-size: 1.6rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        h2 {
            font-size: 1.3rem;
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            color: #2c3e50;
        }

        h2 i {
            margin-right: 10px;
            color: var(--primary-blue);
        }

        .admin-login-card {
            max-width: 400px;
            margin: 50px auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .input-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }

        .input-container {
            position: relative;
        }

        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="password"]:focus,
        input[type="text"]:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            outline: none;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            margin-bottom: 12px;
            width: 100%;
        }

        .btn-icon {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
            color: #fff;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, var(--primary-dark), #1e3a8a);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-refresh {
            background: linear-gradient(135deg, var(--success-blue), #0284c7);
            color: #fff;
        }

        .btn-refresh:hover {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-secondary {
            background: #f1f1f1;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e5e5e5;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        .btn-logout {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: #fff;
        }

        .btn-logout:hover {
            background: linear-gradient(135deg, #c0392b, #a33025);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .error-message,
        .success-message {
            padding: 12px;
            border-radius: 8px;
            margin: 15px;
            text-align: center;
            opacity: 0;
            transition: opacity 0.3s;
            font-weight: 500;
        }

        .error-message {
            background: #ffecec;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }

        .success-message {
            background: #dbeafe;
            color: var(--primary-blue);
            border-left: 4px solid var(--primary-blue);
        }

        .show {
            opacity: 1;
        }

        .token-display,
        .admin-actions,
        .access-logs,
        .admin-footer {
            padding: 20px;
        }

        .token-box {
            background: #f8f9fa;
            border: 2px dashed var(--primary-blue);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 5px;
            margin: 15px 0;
            color: var(--primary-blue);
            box-shadow: inset 0 0 10px rgba(37, 99, 235, 0.1);
        }

        .token-info {
            text-align: center;
            font-size: 1rem;
            color: #7f8c8d;
            margin-top: 10px;
        }

        .countdown {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-blue);
            margin-top: 15px;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            background: rgba(37, 99, 235, 0.05);
        }

        .countdown.warning {
            color: #e67e22;
            background: rgba(230, 126, 34, 0.05);
        }

        .countdown.danger {
            color: #e74c3c;
            background: rgba(231, 76, 60, 0.05);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .token-usage-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-size: 0.9rem;
            border-left: 4px solid var(--primary-blue);
        }

        .token-usage-info ul {
            padding-left: 25px;
            margin-top: 8px;
        }

        .token-usage-info li {
            margin-bottom: 8px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
        }

        .input-group input {
            margin-bottom: 10px;
        }

        .logs-container {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .success-row {
            background-color: rgba(37, 99, 235, 0.05);
        }

        .success-row:hover {
            background-color: rgba(37, 99, 235, 0.1);
        }

        .failed-row {
            background-color: rgba(231, 76, 60, 0.05);
        }

        .failed-row:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }

        .no-logs {
            text-align: center;
            color: #7f8c8d;
            padding: 30px;
        }

        /* Mobile optimizations */
        @media (min-width: 768px) {
            .container {
                padding: 25px;
                max-width: 1000px;
            }

            .btn {
                width: auto;
                margin-right: 12px;
            }

            .input-group {
                flex-direction: row;
            }

            .input-group input {
                margin-bottom: 0;
                margin-right: 12px;
                flex: 1;
            }

            .admin-actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$isLoggedIn): ?>
            <div class="card admin-login-card">
                <div class="card-header">
                    <i class="fas fa-user-shield header-icon"></i>
                    <h1>Admin Login - SMK MUTU PPU</h1>
                </div>

                <?php if (isset($loginError)): ?>
                    <div class="error-message show"><?php echo $loginError; ?></div>
                <?php endif; ?>

                <?php if (isset($errorMessage)): ?>
                    <div class="error-message show"><?php echo $errorMessage; ?></div>
                <?php endif; ?>

                <form method="post" action="" style="padding: 15px;">
                    <div class="form-group">
                        <label for="admin_password" class="input-label">Admin Password</label>
                        <div class="input-container">
                            <input type="password" id="admin_password" name="admin_password" required>
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-sign-in-alt btn-icon"></i>
                        Login
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-shield header-icon"></i>
                    <h1>Token Admin - SMK MUTU PPU</h1>
                </div>

                <?php if (isset($successMessage)): ?>
                    <div class="success-message show"><?php echo $successMessage; ?></div>
                <?php endif; ?>

                <?php if (isset($errorMessage)): ?>
                    <div class="error-message show"><?php echo $errorMessage; ?></div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="token-display">
                    <h2><i class="fas fa-key"></i> Current Token</h2>
                    <?php if ($currentToken): ?>
                        <div class="token-box"><?php echo $currentToken['token_value']; ?></div>
                        <div class="token-info">
                            Expires: <?php echo date('H:i:s', strtotime($currentToken['expires_at'])); ?>
                            (<?php echo (int)((strtotime($currentToken['expires_at']) - time()) / 60); ?> min remaining)
                        </div>

                        <div id="countdown" class="countdown" data-expires="<?php echo strtotime($currentToken['expires_at']); ?>">
                            Loading countdown...
                        </div>

                        <div class="token-usage-info">
                            <p><i class="fas fa-info-circle"></i> <strong>Informasi Token:</strong></p>
                            <ul>
                                <li>Bagikan token <strong><?php echo $currentToken['token_value']; ?></strong> kepada siswa</li>
                                <li>Siswa memasukkan token pada halaman login</li>
                                <li>Token akan mengarahkan ke halaman ujian</li>
                                <li>Token dapat digunakan pada berbagai perangkat</li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="token-box">No active token</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="admin-actions">
                    <form method="post" action="">
                        <button type="submit" name="generate_token" class="btn btn-refresh">
                            <i class="fas fa-sync-alt btn-icon"></i>
                            Generate Token
                        </button>
                    </form>

                    <a href="index.php" class="btn btn-secondary" target="_blank">
                        <i class="fas fa-eye btn-icon"></i>
                        View User Page
                    </a>

                    <form method="post" action="logout.php" style="display:inline;">
                        <button type="submit" class="btn btn-logout">
                            <i class="fas fa-sign-out-alt btn-icon"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="admin-actions">
                    <form method="post" action="" class="rate-limit-form" style="width: 100%;">
                        <div class="input-group">
                            <input type="text" name="ip_address" placeholder="IP Address" required>
                            <button type="submit" name="reset_rate_limit" class="btn btn-secondary">
                                <i class="fas fa-unlock btn-icon"></i>
                                Reset Rate Limit
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="access-logs">
                    <h2><i class="fas fa-history"></i> Recent Access Logs</h2>
                    <div class="logs-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>IP Address</th>
                                    <th>Token</th>
                                     <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($logs) > 0): ?>
                                    <?php foreach ($logs as $log): ?>
                                    <tr class="<?php echo $log['status'] === 'success' ? 'success-row' : 'failed-row'; ?>">
                                        <td><?php echo date('H:i:s', strtotime($log['access_time'])); ?></td>
                                        <td><?php echo $log['ip_address']; ?></td>
                                        <td><?php echo $log['token_value'] ?: 'N/A'; ?></td>
                                        <td><?php echo ucfirst($log['status']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="no-logs">No access logs found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Countdown timer
            const countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                const expiryTime = parseInt(countdownElement.getAttribute('data-expires')) * 1000;

                function updateCountdown() {
                    const now = new Date().getTime();
                    const timeLeft = expiryTime - now;

                    if (timeLeft <= 0) {
                        countdownElement.innerHTML = 'Token telah kedaluwarsa! Memperbaharui...';
                        countdownElement.className = 'countdown danger';

                        // Auto refresh the page to get a new token
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);

                        return;
                    }

                    // Calculate time units
                    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                    // Format display
                    countdownElement.innerHTML = `${minutes}m ${seconds}s tersisa`;

                    // Change color based on time remaining
                    if (timeLeft < 60000) { // less than 1 minute
                        countdownElement.className = 'countdown danger';
                    } else if (timeLeft < 180000) { // less than 3 minutes
                        countdownElement.className = 'countdown warning';
                    } else {
                        countdownElement.className = 'countdown';
                    }
                }

                // Update immediately and then every second
                updateCountdown();
                setInterval(updateCountdown, 1000);

                // Check every 30 seconds if we need to auto-regenerate the token
                setInterval(function() {
                    const now = new Date().getTime();
                    const timeLeft = expiryTime - now;

                    // If token has expired or is about to expire in the next 10 seconds
                    if (timeLeft <= 10000) {
                        window.location.reload();
                    }
                }, 30000);
            }

            // Auto-hide success message after 3 seconds
            const successMessage = document.querySelector('.success-message.show');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.classList.remove('show');
                }, 3000);
            }

            // Auto-hide error message after 3 seconds
            const errorMessage = document.querySelector('.error-message.show');
            if (errorMessage) {
                setTimeout(function() {
                    errorMessage.classList.remove('show');
                }, 3000);
            }
        });
    </script>
</body>
</html>