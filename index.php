<?php
require_once 'config.php';

$errorMessage = '';
$isRateLimited = false;

// Session management
session_start();

// Handle token submission
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
            // Save token in session
            $_SESSION['valid_token'] = $tokenValue;
            $_SESSION['token_expires'] = strtotime($result['expires_at']);

            // Redirect to target URL
            header('Location: ' . REDIRECT_URL);
            exit;
        } else {
            $errorMessage = 'Token tidak valid. Silakan coba lagi.';
        }
    }
}

// Clear session when accessing token input page
if ($_SERVER['PHP_SELF'] === '/TOKEN/index.php' || basename($_SERVER['PHP_SELF']) === 'index.php') {
    unset($_SESSION['valid_token']);
    unset($_SESSION['token_expires']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Ujian Daring - SMP Negeri 3</title>

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- External CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">

    <!-- Meta tags for SEO and social sharing -->
    <meta name="description" content="Portal ujian daring SMP Negeri 3 - Masukkan token Anda untuk mengakses sistem ujian">
    <meta name="robots" content="noindex, nofollow">
    <meta name="author" content="SMP Negeri 3">

    <!-- Open Graph -->
    <meta property="og:title" content="Portal Ujian Daring - SMP Negeri 3">
    <meta property="og:description" content="Masukkan token ujian Anda untuk mengakses sistem ujian daring">
    <meta property="og:type" content="website">

    <!-- Theme color for mobile browsers -->
    <meta name="theme-color" content="#2ecc71">

    <!-- Cache control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Apple touch icon -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Complete Inline CSS for Reliable Display -->
    <style>
        /* CSS Variables */
        :root {
            --primary-green: #2ecc71;
            --primary-dark: #27ae60;
            --primary-light: #a8e6cf;
            --accent-green: #16a085;
            --success-green: #00b894;
            --warning-amber: #fdcb6e;
            --danger-red: #e17055;
            --pure-white: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #495057;
            --text-dark: #2c3e50;
            --shadow-sm: 0 2px 8px rgba(46, 204, 113, 0.08);
            --shadow-md: 0 4px 16px rgba(46, 204, 113, 0.12);
            --shadow-lg: 0 8px 32px rgba(46, 204, 113, 0.16);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0fff4;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
            margin: 0;
        }

        /* Container and Layout */
        .container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Card Design */
        .card {
            background: var(--pure-white);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(46, 204, 113, 0.15);
            overflow: hidden;
            padding: 32px;
            position: relative;
            border: 2px solid var(--primary-green);
            width: 100%;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Logo Section */
        .logo-section {
            text-align: center;
            margin-bottom: 24px;
            position: relative;
        }

        .logo-container {
            display: inline-block;
            position: relative;
            margin-bottom: 16px;
        }

        .logo-img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border-radius: 12px;
            border: 2px solid var(--primary-green);
        }

        .logo-fallback {
            width: 100px;
            height: 100px;
            background: var(--primary-green);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border: 2px solid var(--primary-dark);
        }

        .logo-fallback i {
            font-size: 3rem;
            color: white;
        }

        .school-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 8px 0;
            background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .exam-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--dark-gray);
            margin: 0;
        }

        /* Content Section */
        .card-content {
            margin-bottom: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .info-text {
            text-align: center;
            color: var(--text-dark);
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.5;
            padding: 12px 16px;
            background: rgba(46, 204, 113, 0.1);
            border-radius: 8px;
            border: 1px solid var(--primary-green);
        }

        .info-text i {
            color: var(--primary-green);
            margin-right: 8px;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .input-label i {
            margin-right: 8px;
            color: var(--primary-green);
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-container input[type="text"] {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid var(--medium-gray);
            border-radius: 8px;
            font-size: 1.1rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            background: var(--pure-white);
            color: var(--text-dark);
            letter-spacing: 2px;
            text-align: center;
            text-transform: uppercase;
        }

        .input-container input[type="text"]:focus {
            border-color: var(--primary-green);
            outline: none;
            box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
        }

        .input-container input[type="text"]::placeholder {
            color: var(--dark-gray);
            font-weight: 400;
            letter-spacing: 0;
        }

        .input-icon {
            position: absolute;
            left: 20px;
            color: var(--primary-green);
            font-size: 1.3rem;
            pointer-events: none;
            transition: var(--transition);
            z-index: 2;
        }

        .input-container input[type="text"]:focus + .input-icon {
            transform: scale(1.1);
            color: var(--primary-dark);
        }

        /* Button Styling */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 16px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            width: 100%;
        }

        .btn-icon {
            margin-right: 10px;
            font-size: 1rem;
        }

        .btn-submit {
            background: var(--primary-green);
            color: var(--pure-white);
            border: 2px solid var(--primary-green);
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid var(--pure-white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn.loading .loading-spinner {
            display: inline-block;
        }

        .btn.loading .btn-icon {
            display: none;
        }

        /* Help Section */
        .token-help {
            text-align: center;
            margin: 16px 0;
            position: relative;
        }

        .btn-help {
            background: none;
            border: none;
            color: var(--primary-green);
            font-size: 1.4rem;
            cursor: pointer;
            transition: var(--transition);
            padding: 8px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-help:hover {
            color: var(--primary-dark);
            background: rgba(46, 204, 113, 0.1);
            transform: scale(1.1);
        }

        .help-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--text-dark);
            color: var(--pure-white);
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            z-index: 10;
            box-shadow: var(--shadow-md);
            margin-bottom: 8px;
        }

        .help-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: var(--text-dark) transparent transparent transparent;
        }

        /* Security Note */
        .security-note {
            display: flex;
            align-items: center;
            background: rgba(46, 204, 113, 0.05);
            padding: 12px;
            border-radius: 8px;
            margin-top: 16px;
            margin-bottom: 0;
            border: 1px solid var(--primary-green);
        }

        .security-note i {
            color: var(--primary-green);
            font-size: 1.2rem;
            margin-right: 12px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .security-note p {
            font-size: 0.9rem;
            color: var(--text-dark);
            margin: 0;
            font-weight: 500;
            line-height: 1.6;
        }

        /* Footer */
        .card-footer {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid var(--medium-gray);
            margin-bottom: 0;
        }

        .footer-content {
            text-align: center;
        }

        .copyright {
            font-size: 0.8rem;
            color: var(--dark-gray);
            margin-bottom: 12px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .footer-link {
            background: none;
            border: none;
            color: var(--primary-green);
            font-size: 0.8rem;
            cursor: pointer;
            transition: var(--transition);
            padding: 4px 8px;
            border-radius: 4px;
        }

        .footer-link:hover {
            background: rgba(46, 204, 113, 0.1);
            color: var(--primary-dark);
        }

        /* Help text */
        .help-text {
            font-size: 0.85rem;
            color: var(--dark-gray);
            margin-top: 8px;
            text-align: center;
        }

        /* Error and Success Messages */
        .error-message,
        .success-message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-top: 16px;
            font-weight: 500;
            display: none;
            align-items: center;
            font-size: 0.95rem;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            background: linear-gradient(135deg, #ffeaea, #fff5f5);
            color: var(--danger-red);
            border-left: 4px solid var(--danger-red);
            box-shadow: 0 4px 12px rgba(225, 112, 85, 0.15);
        }

        .error-message.rate-limit {
            background: linear-gradient(135deg, #fff3e0, #fffbf0);
            color: #e67e22;
            border-left: 4px solid #e67e22;
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.15);
        }

        .success-message {
            background: linear-gradient(135deg, #e8f8f5, #f0fff4);
            color: var(--success-green);
            border-left: 4px solid var(--success-green);
            box-shadow: 0 4px 12px rgba(0, 184, 148, 0.15);
        }

        .error-message.show,
        .success-message.show {
            display: flex;
        }

        .error-message i,
        .success-message i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        
        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal.show {
            display: flex;
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            background: var(--pure-white);
            padding: 32px;
            border-radius: 16px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.3s ease-out;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--text-dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--dark-gray);
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: var(--transition);
        }

        .modal-close:hover {
            background: var(--light-gray);
        }

        .modal-body {
            text-align: left;
        }

        .modal-body ul {
            margin: 16px 0;
            padding-left: 20px;
        }

        .modal-body li {
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            body {
                padding: 16px;
            }

            .container {
                max-width: 100%;
            }

            .card {
                padding: 24px 20px;
            }

            .logo-img,
            .logo-fallback {
                width: 90px;
                height: 90px;
            }

            .school-name {
                font-size: 1.4rem;
            }

            .input-container input[type="text"] {
                padding: 14px 18px 14px 44px;
                font-size: 1rem;
            }

            .btn {
                padding: 14px 20px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px;
            }

            .card {
                padding: 20px 16px;
            }

            .logo-img,
            .logo-fallback {
                width: 80px;
                height: 80px;
            }

            .school-name {
                font-size: 1.2rem;
            }

            .input-container input[type="text"] {
                padding: 12px 16px 12px 40px;
                font-size: 0.95rem;
            }

            .btn {
                padding: 12px 16px;
                font-size: 0.95rem;
            }
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            :root {
                --primary-green: #006600;
                --primary-dark: #004d00;
                --text-dark: #000000;
                --pure-white: #ffffff;
            }

            .card {
                border: 2px solid var(--primary-green);
            }

            .input-container input[type="text"] {
                border-width: 2px;
            }
        }

            </style>
</head>
<body>
    <div class="container">
        <main class="card">
            <!-- Logo Section -->
            <header class="logo-section">
                <div class="logo-container">
                    <img src="smpn3.png" alt="Logo SMP Negeri 3" class="logo-img"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="logo-fallback" style="display: none;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
                <h1 class="school-name">SMP NEGERI 3</h1>
                <p class="exam-title">Portal Ujian Daring</p>
            </header>

            <!-- Main Content -->
            <section class="card-content">
                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    Masukkan token ujian 5 karakter yang diberikan oleh pengawas ujian
                </div>

                <!-- Token Form -->
                <form id="tokenForm" method="post" action="" novalidate>
                    <div class="form-group">
                        <label for="tokenInput" class="input-label">
                            <i class="fas fa-key"></i>
                            Token Ujian
                        </label>
                        <div class="input-container">
                            <input
                                type="text"
                                id="tokenInput"
                                name="token"
                                placeholder="MASUKKAN TOKEN"
                                maxlength="5"
                                required
                                autocomplete="off"
                                pattern="[A-Z0-9]{5}"
                                title="Token harus terdiri dari 5 karakter huruf dan angka"
                                aria-label="Token Ujian"
                                aria-describedby="tokenHelp tokenError"
                            >
                            <div class="input-icon">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <?php if (!empty($errorMessage)): ?>
                            <div class="error-message show <?php echo $isRateLimited ? 'rate-limit' : ''; ?>"
                                 id="tokenError" role="alert">
                                <i class="fas fa-<?php echo $isRateLimited ? 'clock' : 'exclamation-triangle'; ?>"></i>
                                <span><?php echo htmlspecialchars($errorMessage); ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Help text -->
                        <div id="tokenHelp" class="help-text">
                            Token terdiri dari 5 karakter kombinasi huruf dan angka
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-submit" id="submitBtn">
                        <i class="fas fa-sign-in-alt btn-icon"></i>
                        <span>Masuk Ujian</span>
                        <div class="loading-spinner"></div>
                    </button>
                </form>

                <!-- Help Section -->
                <div class="token-help">
                    <button
                        type="button"
                        id="helpBtn"
                        class="btn-help"
                        aria-label="Bantuan token ujian"
                        aria-expanded="false"
                    >
                        <i class="fas fa-question-circle"></i>
                    </button>
                    <div class="help-tooltip" role="tooltip">
                        Hubungi pengawas ujian untuk mendapatkan token akses terbaru
                    </div>
                </div>

                <!-- Security Note -->
                <div class="security-note">
                    <i class="fas fa-shield-alt"></i>
                    <p>
                        <strong>Penting:</strong> Token bersifat rahasia dan hanya berlaku untuk sesi ujian saat ini.
                        Jangan bagikan token kepada orang lain.
                    </p>
                </div>
            </section>

            <!-- Footer -->
            <footer class="card-footer">
                <div class="footer-content">
                    <p class="copyright">
                        <i class="fas fa-copyright"></i>
                        2024 SMP Negeri 3 - Sistem Ujian Daring
                    </p>
                    <div class="footer-links">
                        <button type="button" class="footer-link" id="privacyBtn">
                            <i class="fas fa-user-shield"></i>
                            Privasi
                        </button>
                        <button type="button" class="footer-link" id="helpBtnFooter">
                            <i class="fas fa-life-ring"></i>
                            Bantuan
                        </button>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <!-- Privacy Modal -->
    <div class="modal" id="privacyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Kebijakan Privasi</h3>
                <button type="button" class="modal-close" id="closePrivacy">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Sistem ujian daring kami menjaga privasi dan keamanan data Anda:</p>
                <ul>
                    <li>Token ujian hanya digunakan untuk sesi ujian saat ini</li>
                    <li>Data akses hanya untuk keperluan monitoring keamanan</li>
                    <li>Tidak ada data pribadi yang disimpan tanpa persetujuan</li>
                    <li>Enkripsi data end-to-end untuk keamanan maksimal</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const tokenForm = document.getElementById('tokenForm');
            const tokenInput = document.getElementById('tokenInput');
            const submitBtn = document.getElementById('submitBtn');
            const helpBtn = document.getElementById('helpBtn');
            const helpTooltip = document.querySelector('.help-tooltip');
            const privacyModal = document.getElementById('privacyModal');
            const privacyBtn = document.getElementById('privacyBtn');
            const helpBtnFooter = document.getElementById('helpBtnFooter');

            // Auto focus on input
            tokenInput.focus();

            // Format input to uppercase and limit to 5 characters
            tokenInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5);
                }
                e.target.value = value;

                // Remove error state when user starts typing
                if (value.length > 0) {
                    tokenInput.classList.remove('shake');
                    const errorMsg = document.getElementById('tokenError');
                    if (errorMsg) {
                        errorMsg.classList.remove('show');
                    }
                }
            });

            // Form submission
            tokenForm.addEventListener('submit', function(e) {
                const token = tokenInput.value.trim();

                // Validation
                if (token.length !== 5) {
                    e.preventDefault();
                    alert('Token harus terdiri dari 5 karakter');
                    return;
                }

                if (!/^[A-Z0-9]{5}$/.test(token)) {
                    e.preventDefault();
                    alert('Token hanya boleh mengandung huruf besar dan angka');
                    return;
                }
            });

            // Help tooltip functionality
            function showTooltip() {
                helpTooltip.style.opacity = '1';
                helpTooltip.style.visibility = 'visible';
                helpBtn.setAttribute('aria-expanded', 'true');
            }

            function hideTooltip() {
                helpTooltip.style.opacity = '0';
                helpTooltip.style.visibility = 'hidden';
                helpBtn.setAttribute('aria-expanded', 'false');
            }

            helpBtn.addEventListener('mouseenter', showTooltip);
            helpBtn.addEventListener('mouseleave', hideTooltip);
            helpBtn.addEventListener('focus', showTooltip);
            helpBtn.addEventListener('blur', hideTooltip);

            // Touch devices
            helpBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (helpTooltip.style.visibility === 'visible') {
                    hideTooltip();
                } else {
                    showTooltip();
                    setTimeout(hideTooltip, 4000);
                }
            });

            // Privacy modal
            privacyBtn.addEventListener('click', function() {
                privacyModal.classList.add('show');
                document.body.style.overflow = 'hidden';
            });

            document.getElementById('closePrivacy').addEventListener('click', function() {
                privacyModal.classList.remove('show');
                document.body.style.overflow = '';
            });

            // Help footer button
            helpBtnFooter.addEventListener('click', function() {
                showTooltip();
                setTimeout(hideTooltip, 4000);
            });

            // Close modals on background click
            privacyModal.addEventListener('click', function(e) {
                if (e.target === privacyModal) {
                    privacyModal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                // ESC to close modals
                if (e.key === 'Escape') {
                    if (privacyModal.classList.contains('show')) {
                        privacyModal.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                }

                // Ctrl/Cmd + Enter to submit form
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    if (document.activeElement === tokenInput) {
                        tokenForm.dispatchEvent(new Event('submit'));
                    }
                }
            });

                    });
    </script>
</body>
</html>