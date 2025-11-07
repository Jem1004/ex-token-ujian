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
            background: #ffffff;
            color: var(--text-dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            line-height: 1.6;
            margin: 0;
            overflow: hidden;
        }

        /* Container and Layout */
        .container {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            max-height: 100vh;
        }

        /* Card Design */
        .card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 20px;
            position: relative;
            width: 100%;
            max-width: 420px;
            flex: 1;
            display: flex;
            flex-direction: column;
            max-height: 95vh;
            overflow-y: auto;
        }

        /* Logo Section */
        .logo-section {
            text-align: center;
            margin-bottom: 16px;
            position: relative;
        }

        .logo-container {
            display: inline-block;
            position: relative;
            margin-bottom: 12px;
        }

        .logo-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .logo-fallback {
            width: 80px;
            height: 80px;
            background: #6c757d;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border: 1px solid #495057;
        }

        .logo-fallback i {
            font-size: 2.2rem;
            color: white;
        }

        .school-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #495057;
            margin: 0 0 6px 0;
        }

        .exam-title {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--dark-gray);
            margin: 0;
        }

        /* Content Section */
        .card-content {
            margin-bottom: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .info-text {
            text-align: center;
            color: #155724;
            margin-bottom: 16px;
            font-size: 0.85rem;
            font-weight: 500;
            line-height: 1.4;
            padding: 10px 12px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 4px;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .info-text i {
            color: #28a745;
            margin-right: 8px;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 16px;
            position: relative;
        }

        .input-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #495057;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .input-label i {
            margin-right: 8px;
            color: #28a745;
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-container input[type="text"] {
            width: 100%;
            padding: 14px 16px 14px 42px;
            border: 2px solid var(--medium-gray);
            border-radius: 6px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            background: var(--pure-white);
            color: var(--text-dark);
            letter-spacing: 2px;
            text-align: center;
            text-transform: uppercase;
        }

        .input-container input[type="text"]:focus {
            border-color: #28a745;
            outline: none;
        }

        .input-container input[type="text"]::placeholder {
            color: var(--dark-gray);
            font-weight: 400;
            letter-spacing: 0;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: #28a745;
            font-size: 1.1rem;
            pointer-events: none;
            z-index: 2;
        }

        .input-container input[type="text"]:focus + .input-icon {
            color: #28a745;
        }

        /* Button Styling */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
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
            background: #28a745;
            color: #ffffff;
            border: 1px solid #28a745;
        }

        .btn-submit:hover {
            background: #218838;
            border-color: #1e7e34;
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
            border-top: 3px solid #ffffff;
            border-radius: 50%;
            margin-left: 10px;
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
            margin: 12px 0;
            position: relative;
        }

        .btn-help {
            background: none;
            border: none;
            color: #28a745;
            font-size: 1.4rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-help:hover {
            color: #218838;
            background: rgba(40, 167, 69, 0.1);
        }

        .help-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #155724;
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            z-index: 10;
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
            border-color: #155724 transparent transparent transparent;
        }

        /* Security Note */
        .security-note {
            display: flex;
            align-items: center;
            background: rgba(40, 167, 69, 0.1);
            padding: 12px;
            border-radius: 4px;
            margin-top: 16px;
            margin-bottom: 0;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .security-note i {
            color: #28a745;
            font-size: 1.2rem;
            margin-right: 12px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .security-note p {
            font-size: 0.9rem;
            color: #495057;
            margin: 0;
            font-weight: 500;
            line-height: 1.6;
        }

        /* Footer */
        .card-footer {
            margin-top: 12px;
            padding-top: 8px;
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
            color: #28a745;
            font-size: 0.8rem;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .footer-link:hover {
            background: rgba(40, 167, 69, 0.1);
            color: #218838;
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
            border-radius: 4px;
            margin-top: 16px;
            font-weight: 500;
            display: none;
            align-items: center;
            font-size: 0.95rem;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .error-message.rate-limit {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
        }

        .modal-content {
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            border: 1px solid #dee2e6;
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
            color: #28a745;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
        }

        .modal-close:hover {
            background: rgba(40, 167, 69, 0.1);
            color: #218838;
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
            color: #495057;
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            body {
                padding: 4px;
                height: 100vh;
                overflow: hidden;
                background: #ffffff;
            }

            .container {
                max-width: 100%;
                max-height: 100vh;
                align-items: center;
            }

            .card {
                padding: 16px 14px;
                max-height: 98vh;
                background: #ffffff;
                border: 1px solid #dee2e6;
                width: 100%;
                max-width: 400px;
            }

            .logo-img,
            .logo-fallback {
                width: 70px;
                height: 70px;
            }

            .school-name {
                font-size: 1.2rem;
            }

            .exam-title {
                font-size: 0.85rem;
            }

            .info-text {
                font-size: 0.8rem;
                padding: 8px 10px;
                margin-bottom: 12px;
            }

            .input-label {
                font-size: 0.8rem;
            }

            .input-container input[type="text"] {
                padding: 12px 14px 12px 38px;
                font-size: 0.95rem;
                text-align: center;
            }

            .input-icon {
                left: 14px;
                font-size: 1rem;
            }

            .btn {
                padding: 12px 16px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 2px;
                height: 100vh;
                overflow: hidden;
                background: #ffffff;
            }

            .container {
                max-width: 100%;
                max-height: 100vh;
                align-items: center;
            }

            .card {
                padding: 12px 10px;
                max-height: 99vh;
                border-radius: 4px;
                background: #ffffff;
                border: 1px solid #dee2e6;
                width: 100%;
                max-width: 380px;
            }

            .logo-img,
            .logo-fallback {
                width: 60px;
                height: 60px;
            }

            .logo-fallback i {
                font-size: 1.8rem;
            }

            .school-name {
                font-size: 1.1rem;
                margin-bottom: 4px;
            }

            .exam-title {
                font-size: 0.8rem;
            }

            .logo-section {
                margin-bottom: 12px;
            }

            .logo-container {
                margin-bottom: 8px;
            }

            .info-text {
                font-size: 0.75rem;
                padding: 6px 8px;
                margin-bottom: 10px;
            }

            .card-content {
                margin-bottom: 12px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .input-label {
                font-size: 0.75rem;
                margin-bottom: 4px;
            }

            .input-container input[type="text"] {
                padding: 10px 12px 10px 34px;
                font-size: 0.9rem;
                letter-spacing: 1.5px;
                text-align: center;
            }

            .input-icon {
                left: 12px;
                font-size: 0.9rem;
            }

            .btn {
                padding: 10px 14px;
                font-size: 0.85rem;
            }

            .btn-icon {
                font-size: 0.9rem;
                margin-right: 8px;
            }

            .security-note {
                padding: 8px;
                margin-top: 12px;
            }

            .security-note p {
                font-size: 0.75rem;
            }

            .help-text {
                font-size: 0.75rem;
                margin-top: 6px;
            }
        }

        /* Extra small mobile optimization */
        @media (max-width: 360px) {
            body {
                padding: 1px;
                background: #ffffff;
            }

            .card {
                padding: 10px 8px;
                max-height: 99vh;
                background: #ffffff;
                border: 1px solid #dee2e6;
                width: 100%;
                max-width: 360px;
            }

            .logo-img,
            .logo-fallback {
                width: 50px;
                height: 50px;
            }

            .logo-fallback i {
                font-size: 1.5rem;
            }

            .school-name {
                font-size: 1rem;
            }

            .exam-title {
                font-size: 0.75rem;
            }

            .info-text {
                font-size: 0.7rem;
                padding: 5px 6px;
                margin-bottom: 8px;
            }

            .input-label {
                font-size: 0.7rem;
            }

            .input-container input[type="text"] {
                padding: 8px 10px 8px 30px;
                font-size: 0.85rem;
                letter-spacing: 1px;
                text-align: center;
            }

            .input-icon {
                left: 10px;
                font-size: 0.8rem;
            }

            .btn {
                padding: 8px 12px;
                font-size: 0.8rem;
            }

            .security-note {
                padding: 6px;
                margin-top: 8px;
            }

            .security-note p {
                font-size: 0.7rem;
            }

            .help-text {
                font-size: 0.7rem;
            }

            .copyright {
                font-size: 0.7rem;
            }

            .footer-link {
                font-size: 0.7rem;
                padding: 2px 4px;
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