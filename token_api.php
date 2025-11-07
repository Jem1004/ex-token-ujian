<?php
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests if needed
header('Access-Control-Allow-Methods: POST');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get action from request
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Handle different actions
switch ($action) {
    case 'generate':
        // Check if user is authorized with a more secure key
        if (!isset($_POST['admin_key']) || $_POST['admin_key'] !== 'Smkmutu@30407288') {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $newToken = createNewToken();
        echo json_encode([
            'success' => true,
            'token' => $newToken['token'],
            'expires_at' => $newToken['expires_at'],
            'expires_in_minutes' => TOKEN_EXPIRY_MINUTES
        ]);
        break;
        
    case 'validate':
        // Get token from request
        $token = isset($_POST['token']) ? strtoupper(trim($_POST['token'])) : '';
        
        if (empty($token)) {
            echo json_encode(['error' => 'Token is required']);
            exit;
        }
        
        $result = validateToken($token);
        
        if ($result['valid']) {
            echo json_encode([
                'success' => true,
                'message' => 'Token is valid',
                'expires_at' => $result['expires_at'],
                'redirect_url' => REDIRECT_URL
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]);
        }
        break;
        
    case 'reset_rate_limit':
        // Check if user is authorized
        if (!isset($_POST['admin_key']) || $_POST['admin_key'] !== 'Smkmutu@30407288') {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $ip = isset($_POST['ip']) ? $_POST['ip'] : '';
        
        if (empty($ip)) {
            echo json_encode(['error' => 'IP address is required']);
            exit;
        }
        
        $result = resetRateLimiting($ip);
        
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Rate limit reset successfully' : 'Failed to reset rate limit'
        ]);
        break;
        
    case 'get_current_token':
        // Check if user is authorized
        if (!isset($_POST['admin_key']) || $_POST['admin_key'] !== 'Smkmutu@30407288') {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $conn = getDbConnection();
        $result = $conn->query("SELECT token_value, expires_at FROM tokens WHERE is_active = TRUE ORDER BY created_at DESC LIMIT 1");
        
        if ($result && $result->num_rows > 0) {
            $currentToken = $result->fetch_assoc();
            $conn->close();
            
            echo json_encode([
                'success' => true,
                'token' => $currentToken['token_value'],
                'expires_at' => $currentToken['expires_at'],
                'expires_in_seconds' => max(0, strtotime($currentToken['expires_at']) - time())
            ]);
        } else {
            $conn->close();
            echo json_encode([
                'success' => false,
                'message' => 'No active token found'
            ]);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>