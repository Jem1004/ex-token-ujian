<?php
require_once 'config.php';

$errorMessage = '';
$isRateLimited = false;

// Hapus pengecekan cookie dan gunakan session sebagai gantinya
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Check rate limiting
    if (checkRateLimiting($ip)) {
        $errorMessage = 'Terlalu banyak percobaan. Silakan coba lagi nanti.';
        $isRateLimited = true;
    } else {
        $tokenValue = strtoupper(trim($_POST['token']));
        $result = validateToken($tokenValue);
        
        if ($result['valid']) {
            // Simpan token dalam session, bukan cookie
            $_SESSION['valid_token'] = $tokenValue;
            $_SESSION['token_expires'] = strtotime($result['expires_at']);
            
            // Redirect ke target URL
            header('Location: ' . REDIRECT_URL);
            exit;
        } else {
            $errorMessage = 'Token tidak valid. Silakan coba lagi.';
        }
    }
}

// Hapus session saat mengakses halaman input token
// Ini memastikan pengguna harus memasukkan token setiap kali
if ($_SERVER['PHP_SELF'] === '/TOKEN/index.php') {
    // Hapus session token jika ada
    unset($_SESSION['valid_token']);
    unset($_SESSION['token_expires']);
}

// Sisa kode halaman HTML tetap sama
?>
<!DOCTYPE html>
<!-- HTML content remains unchanged -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Access</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="header-icon-container">
                    <i class="fas fa-lock header-icon"></i>
                    <div class="icon-pulse"></div>
                </div>
                <h1>UJIAN DARING SMK MUTU PPU</h1>
            </div>
            <p class="info-text">Token terdiri dari 5 karakter | Token minta kepada pengawas ujian atau panitia.</p>
            
            <form id="tokenForm" method="post" action="">
                <div class="form-group">
                    <label for="tokenInput" class="input-label">Input Token Ujian</label>
                    <div class="input-container">
                        <input type="text" id="tokenInput" name="token" placeholder="Input Token" maxlength="5" required>
                        <div class="input-icon">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <?php if (!empty($errorMessage)): ?>
                        <div class="error-message show <?php echo $isRateLimited ? 'rate-limit' : ''; ?>">
                            <?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-shield-alt btn-icon"></i>
                    Verify Access
                </button>
            </form>
            
            <div class="token-help">
                <button id="helpBtn" class="btn-help">
                    <i class="fas fa-question-circle"></i>
                </button>
                <div class="help-tooltip">Hubungi administrator untuk mendapatkan token akses terbaru</div>
            </div>
            
            <div class="security-note">
                <i class="fas fa-info-circle"></i>
                <p><strong>Security Note:</strong> Token 5 karakter. Gunakan untuk masuk ke halaman ujian.</p>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tokenInput = document.getElementById('tokenInput');
            const helpBtn = document.getElementById('helpBtn');
            const helpTooltip = document.querySelector('.help-tooltip');
            
            // Auto focus pada input saat halaman dimuat
            tokenInput.focus();
            
            // Format input saat diketik (opsional)
            tokenInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
            
            // Tampilkan tooltip bantuan
            helpBtn.addEventListener('mouseenter', function() {
                helpTooltip.style.opacity = '1';
                helpTooltip.style.visibility = 'visible';
            });
            
            helpBtn.addEventListener('mouseleave', function() {
                helpTooltip.style.opacity = '0';
                helpTooltip.style.visibility = 'hidden';
            });
            
            // Untuk perangkat sentuh
            helpBtn.addEventListener('click', function() {
                if (helpTooltip.style.visibility === 'visible') {
                    helpTooltip.style.opacity = '0';
                    helpTooltip.style.visibility = 'hidden';
                } else {
                    helpTooltip.style.opacity = '1';
                    helpTooltip.style.visibility = 'visible';
                    
                    // Sembunyikan tooltip setelah 3 detik pada perangkat sentuh
                    setTimeout(() => {
                        helpTooltip.style.opacity = '0';
                        helpTooltip.style.visibility = 'hidden';
                    }, 3000);
                }
            });
        });
    </script>
</body>
</html>
