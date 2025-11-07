<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'wCvTiLwLrN6QefFvKHghIivSyma');
define('DB_NAME', 'token_system');

// Application settings
define('TOKEN_EXPIRY_MINUTES', 15); // Changed from 5 to 15 minutes
define('MAX_ATTEMPTS', 10);
define('COOLDOWN_MINUTES', 10);
define('REDIRECT_URL', 'https://pribadi.smpn3ppu.sch.id/heisiswasmpn3kamuharusjujurdalamujian/public/login/index.php');

// Connect to database
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Generate a new token
function generateToken() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $token = '';
    
    for ($i = 0; $i < 5; $i++) {
        $token .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    return $token;
}

// Create a new token in the database
function createNewToken() {
    $conn = getDbConnection();
    
    // Deactivate all existing tokens
    $stmt = $conn->prepare("UPDATE tokens SET is_active = FALSE WHERE is_active = TRUE");
    $stmt->execute();
    
    // Create new token
    $token = generateToken();
    $expiryTime = date('Y-m-d H:i:s', time() + (TOKEN_EXPIRY_MINUTES * 60));
    
    $stmt = $conn->prepare("INSERT INTO tokens (token_value, expires_at) VALUES (?, ?)");
    $stmt->bind_param("ss", $token, $expiryTime);
    $stmt->execute();
    
    $tokenId = $conn->insert_id;
    $stmt->close();
    $conn->close();
    
    return [
        'id' => $tokenId,
        'token' => $token,
        'expires_at' => $expiryTime
    ];
}

// Validate a token
function validateToken($tokenValue) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT id, expires_at FROM tokens WHERE token_value = ? AND is_active = TRUE AND expires_at > NOW()");
    $stmt->bind_param("s", $tokenValue);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tokenData = $result->fetch_assoc();
        
        // Log successful access
        logAccess($tokenData['id'], 'success');
        
        $stmt->close();
        $conn->close();
        
        return [
            'valid' => true,
            'expires_at' => $tokenData['expires_at']
        ];
    }
    
    // Log failed access
    logAccess(null, 'failed');
    
    $stmt->close();
    $conn->close();
    
    return ['valid' => false];
}

// Log access attempts
function logAccess($tokenId, $status) {
    $conn = getDbConnection();
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO access_logs (token_id, ip_address, user_agent, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $tokenId, $ip, $userAgent, $status);
    $stmt->execute();
    
    $stmt->close();
    $conn->close();
}

// Check rate limiting
function checkRateLimiting($ip) {
    $conn = getDbConnection();
    
    $timeWindow = date('Y-m-d H:i:s', time() - (COOLDOWN_MINUTES * 60));
    
    $stmt = $conn->prepare("SELECT COUNT(*) as attempt_count FROM access_logs WHERE ip_address = ? AND status = 'failed' AND access_time > ?");
    $stmt->bind_param("ss", $ip, $timeWindow);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $data['attempt_count'] >= MAX_ATTEMPTS;
}

// Reset rate limiting for an IP
function resetRateLimiting($ip) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("DELETE FROM access_logs WHERE ip_address = ? AND status = 'failed'");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return true;
}
?>
