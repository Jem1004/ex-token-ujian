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
    <title>Portal Ujian Daring - SMK MUTU PPU</title>

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">

    <!-- Meta tags for SEO and social sharing -->
    <meta name="description" content="Portal ujian daring SMK MUTU PPU - Masukkan token Anda untuk mengakses sistem ujian">
    <meta name="robots" content="noindex, nofollow">
    <meta name="author" content="SMK MUTU PPU">

    <!-- Open Graph -->
    <meta property="og:title" content="Portal Ujian Daring - SMK MUTU PPU">
    <meta property="og:description" content="Masukkan token ujian Anda untuk mengakses sistem ujian daring">
    <meta property="og:type" content="website">

    <!-- Theme color for mobile browsers -->
    <meta name="theme-color" content="#2563eb">

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
            --primary-blue: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --accent-blue: #1e3a8a;
            --success-blue: #0ea5e9;
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
            --shadow-xl: 0 20px 25px -5px rgba(37, 99, 235, 0.1), 0 10px 10px -5px rgba(37, 99, 235, 0.04);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
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
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            line-height: 1.5;
            margin: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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
            min-height: 100vh;
            padding: 0.5rem;
        }

        /* Card Design */
        .card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-xl);
            padding: 2rem;
            position: relative;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow-lg);
        }

        /* Logo Section */
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .logo-container {
            display: inline-block;
            position: relative;
            margin-bottom: 1.5rem;
        }

        .logo-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: var(--radius-lg);
            border: 2px solid var(--primary-light);
            box-shadow: var(--shadow-md);
        }

        .logo-fallback {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: var(--shadow-md);
        }

        .logo-fallback i {
            font-size: 2.2rem;
            color: white;
        }

        .school-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .exam-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-light);
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
            color: var(--accent-blue);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.5;
            padding: 1rem 1.25rem;
            background: var(--primary-light);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .info-text i {
            color: var(--primary-blue);
            margin-right: 8px;
            font-size: 1rem;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.875rem;
            letter-spacing: 0.025em;
        }

        .input-label i {
            margin-right: 8px;
            color: var(--primary-blue);
            font-size: 0.875rem;
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-container input[type="text"] {
            width: 100%;
            padding: 1.125rem 1.25rem 1.125rem 3.25rem;
            border: 2px solid var(--medium-gray);
            border-radius: var(--radius-lg);
            font-size: 1.125rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            background: var(--pure-white);
            color: var(--text-dark);
            letter-spacing: 0.15em;
            text-align: center;
            text-transform: uppercase;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-container input[type="text"]:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .input-container input[type="text"]::placeholder {
            color: var(--text-light);
            font-weight: 500;
            letter-spacing: 0.05em;
            opacity: 0.7;
        }

        .input-icon {
            position: absolute;
            left: 1.25rem;
            color: var(--primary-blue);
            font-size: 1.25rem;
            pointer-events: none;
            z-index: 2;
            opacity: 0.8;
        }

        .input-container input[type="text"]:focus + .input-icon {
            color: var(--primary-dark);
            opacity: 1;
        }

        /* Enhanced focus states for accessibility */
        .input-container input[type="text"]:focus-visible {
            outline: 2px solid var(--primary-blue);
            outline-offset: 2px;
        }

        .btn:focus-visible {
            outline: 2px solid var(--primary-blue);
            outline-offset: 2px;
        }

        /* Success state for form */
        .form-success .input-container input[type="text"] {
            border-color: var(--success-blue);
        }

        /* Button Styling */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            width: 100%;
        }

        .btn-icon {
            margin-right: 0.75rem;
            font-size: 1rem;
            opacity: 0.9;
        }

        .btn-submit {
            background: var(--primary-blue);
            color: #ffffff;
            border: none;
            font-weight: 600;
            letter-spacing: 0.025em;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .btn.loading {
            pointer-events: none;
            opacity: 0.7;
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
            color: var(--primary-blue);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .btn-help:hover {
            color: var(--primary-dark);
        }

        .help-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1e3a8a;
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
            border-color: #1e3a8a transparent transparent transparent;
        }

        /* Security Note */
        .security-note {
            display: flex;
            align-items: flex-start;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(37, 99, 235, 0.1));
            padding: 1.25rem;
            border-radius: var(--radius-lg);
            margin-top: 1.5rem;
            margin-bottom: 0;
            border: 1px solid var(--primary-light);
            box-shadow: var(--shadow-sm);
        }

        .security-note i {
            color: var(--primary-blue);
            font-size: 1.25rem;
            margin-right: 1rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .security-note p {
            font-size: 0.875rem;
            color: var(--dark-gray);
            margin: 0;
            font-weight: 500;
            line-height: 1.5;
        }

        .security-note strong {
            color: var(--primary-dark);
            font-weight: 700;
        }

        /* Footer */
        .card-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--medium-gray);
            margin-bottom: 0;
        }

        .footer-content {
            text-align: center;
        }

        .copyright {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-bottom: 0.75rem;
            font-weight: 500;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
        }

        .footer-link {
            background: none;
            border: none;
            color: var(--primary-blue);
            font-size: 0.75rem;
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .footer-link:hover {
            background: var(--primary-light);
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
            color: #2563eb;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
        }

        .modal-close:hover {
            background: rgba(37, 99, 235, 0.1);
            color: #1e40af;
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
                padding: 0.75rem;
                min-height: 100vh;
                overflow-x: hidden;
            }

            .container {
                padding: 0;
                min-height: 100vh;
            }

            .card {
                padding: 1.75rem 1.25rem;
                margin: 0.5rem;
                border-radius: var(--radius-lg);
                box-shadow: var(--shadow-lg);
            }

            .logo-img,
            .logo-fallback {
                width: 70px;
                height: 70px;
            }

            .school-name {
                font-size: 1.25rem;
            }

            .exam-title {
                font-size: 0.875rem;
            }

            .info-text {
                font-size: 0.8rem;
                padding: 0.875rem 1rem;
                margin-bottom: 1.25rem;
            }

            .input-label {
                font-size: 0.8rem;
                margin-bottom: 0.5rem;
            }

            .input-container input[type="text"] {
                padding: 0.875rem 1rem 0.875rem 2.5rem;
                font-size: 1rem;
                border-radius: var(--radius-md);
            }

            .input-icon {
                left: 1rem;
                font-size: 1rem;
            }

            .btn {
                padding: 0.875rem 1.25rem;
                font-size: 0.9rem;
                border-radius: var(--radius-md);
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
                min-height: 100vh;
            }

            .container {
                padding: 0;
            }

            .card {
                padding: 1.5rem 1rem;
                margin: 0.25rem;
                border-radius: var(--radius-md);
                box-shadow: var(--shadow-md);
            }

            .logo-img,
            .logo-fallback {
                width: 60px;
                height: 60px;
            }

            .logo-fallback i {
                font-size: 1.5rem;
            }

            .school-name {
                font-size: 1.125rem;
                margin-bottom: 0.5rem;
            }

            .exam-title {
                font-size: 0.8rem;
            }

            .logo-section {
                margin-bottom: 1.5rem;
            }

            .logo-container {
                margin-bottom: 1rem;
            }

            .info-text {
                font-size: 0.75rem;
                padding: 0.75rem 0.875rem;
                margin-bottom: 1rem;
            }

            .card-content {
                margin-bottom: 1.25rem;
            }

            .form-group {
                margin-bottom: 1.25rem;
            }

            .input-label {
                font-size: 0.75rem;
                margin-bottom: 0.5rem;
            }

            .input-container input[type="text"] {
                padding: 0.75rem 0.875rem 0.75rem 2.25rem;
                font-size: 0.9rem;
                letter-spacing: 0.05em;
                border-radius: var(--radius-sm);
            }

            .input-icon {
                left: 0.875rem;
                font-size: 0.9rem;
            }

            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
                border-radius: var(--radius-sm);
            }

            .btn-icon {
                font-size: 0.875rem;
                margin-right: 0.5rem;
            }

            .security-note {
                padding: 0.75rem;
                margin-top: 1rem;
            }

            .security-note p {
                font-size: 0.75rem;
            }

            .help-text {
                font-size: 0.75rem;
                margin-top: 0.5rem;
            }
        }

        /* Extra small mobile optimization */
        @media (max-width: 360px) {
            body {
                padding: 0.25rem;
            }

            .card {
                padding: 1.25rem 0.875rem;
                margin: 0.125rem;
                border-radius: var(--radius-sm);
            }

            .logo-img,
            .logo-fallback {
                width: 50px;
                height: 50px;
            }

            .logo-fallback i {
                font-size: 1.25rem;
            }

            .school-name {
                font-size: 1rem;
                line-height: 1.2;
            }

            .exam-title {
                font-size: 0.75rem;
            }

            .info-text {
                font-size: 0.7rem;
                padding: 0.75rem;
                margin-bottom: 0.875rem;
                line-height: 1.4;
            }

            .input-label {
                font-size: 0.75rem;
                margin-bottom: 0.5rem;
            }

            .input-container input[type="text"] {
                padding: 0.75rem 0.75rem 0.75rem 2rem;
                font-size: 0.85rem;
                letter-spacing: 0.025em;
                min-height: 44px;
            }

            .input-icon {
                left: 0.75rem;
                font-size: 0.9rem;
            }

            .btn {
                padding: 0.75rem 0.875rem;
                font-size: 0.8rem;
                min-height: 44px;
            }

            .security-note {
                padding: 0.75rem;
                margin-top: 0.875rem;
            }

            .security-note p {
                font-size: 0.75rem;
                line-height: 1.4;
            }

            .help-text {
                font-size: 0.7rem;
            }

            .copyright {
                font-size: 0.7rem;
            }

            .footer-link {
                font-size: 0.7rem;
                padding: 0.375rem 0.75rem;
            }
        }

        /* Mobile-specific optimizations */
        @media (hover: none) and (pointer: coarse) {
            .btn {
                min-height: 48px;
                min-width: 48px;
            }

            .btn-help {
                min-height: 44px;
                min-width: 44px;
            }

            .footer-link {
                min-height: 44px;
                min-width: 44px;
            }

            .input-container input[type="text"] {
                min-height: 48px;
            }

            /* Remove hover effects on touch devices */
            .btn:hover,
            .input-container input[type="text"]:hover,
            .footer-link:hover {
                transform: none;
            }
        }

        /* Mobile keyboard viewport fix */
        @media (max-height: 600px) and (orientation: landscape) {
            .container {
                min-height: auto;
                padding: 0.5rem 0;
            }

            .card {
                padding: 1rem;
                margin: 0.5rem;
            }

            .logo-section {
                margin-bottom: 1rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .info-text {
                margin-bottom: 1rem;
                padding: 0.75rem;
            }

            .security-note {
                margin-top: 1rem;
                padding: 0.75rem;
            }
        }

        /* iPhone notch and safe area support */
        @supports (padding: max(0px)) {
            .container {
                padding-left: max(0.5rem, env(safe-area-inset-left));
                padding-right: max(0.5rem, env(safe-area-inset-right));
                padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                --light-gray: #1f2937;
                --medium-gray: #374151;
                --dark-gray: #9ca3af;
                --text-dark: #f9fafb;
                --text-light: #d1d5db;
            }

            body {
                background: linear-gradient(135deg, #1e3a8a 0%, #111827 100%);
            }

            .card {
                background: rgba(31, 41, 55, 0.95);
                border-color: rgba(75, 85, 99, 0.3);
            }
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            :root {
                --primary-blue: #0066cc;
                --primary-dark: #004d99;
                --text-dark: #000000;
                --pure-white: #ffffff;
            }

            .card {
                border: 2px solid var(--primary-blue);
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
                    <img src="logo.png" alt="Logo SMK MUTU PPU" class="logo-img"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="logo-fallback" style="display: none;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
                <h1 class="school-name">SMK MUTU PPU</h1>
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
                        Token akan diberikan oleh pengawas ujian, dan akan berubah setiap beberapa menit
                    </p>
                </div>
            </section>

            <!-- Footer -->
            <footer class="card-footer">
                <div class="footer-content">
                    <p class="copyright">
                        <i class="fas fa-copyright"></i>
                        2024 SMK MUTU PPU - Sistem Ujian Daring
                    </p>
                    <div class="footer-links">
                        <button type="button" class="footer-link" id="helpBtnFooter">
                            <i class="fas fa-life-ring"></i>
                            Bantuan
                        </button>
                    </div>
                </div>
            </footer>
        </main>
    </div>

  
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const tokenForm = document.getElementById('tokenForm');
            const tokenInput = document.getElementById('tokenInput');
            const submitBtn = document.getElementById('submitBtn');
            const helpBtn = document.getElementById('helpBtn');
            const helpTooltip = document.querySelector('.help-tooltip');
            const helpBtnFooter = document.getElementById('helpBtnFooter');

            // Check if touch device
            const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

            // Auto focus on input (with delay for mobile keyboards)
            if (!isTouchDevice) {
                setTimeout(() => tokenInput.focus(), 100);
            }

            // Enhanced input handling for mobile
            tokenInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5);
                }
                e.target.value = value;

                // Add visual feedback for valid input
                if (value.length === 5) {
                    e.target.style.borderColor = 'var(--success-blue)';
                    tokenForm.classList.add('form-success');
                } else {
                    e.target.style.borderColor = '';
                    tokenForm.classList.remove('form-success');
                }

                // Remove error state when user starts typing
                if (value.length > 0) {
                    const errorMsg = document.getElementById('tokenError');
                    if (errorMsg) {
                        errorMsg.classList.remove('show');
                    }
                }
            });

            // Prevent zoom on input focus for mobile
            tokenInput.addEventListener('touchstart', function(e) {
                if (isTouchDevice) {
                    e.target.style.fontSize = '16px'; // Prevent zoom
                }
            });

            // Enhanced form submission with loading state
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

                // Add loading state
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
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

            // Mouse events for desktop
            if (!isTouchDevice) {
                helpBtn.addEventListener('mouseenter', showTooltip);
                helpBtn.addEventListener('mouseleave', hideTooltip);
                helpBtn.addEventListener('focus', showTooltip);
                helpBtn.addEventListener('blur', hideTooltip);
            }

            // Touch events for mobile
            helpBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (helpTooltip.style.visibility === 'visible') {
                    hideTooltip();
                } else {
                    showTooltip();
                    setTimeout(hideTooltip, 4000);
                }
            });

            // Help footer button
            helpBtnFooter.addEventListener('click', function() {
                showTooltip();
                setTimeout(hideTooltip, 4000);
            });

            // Enhanced keyboard navigation
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + Enter to submit form
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    if (document.activeElement === tokenInput) {
                        tokenForm.dispatchEvent(new Event('submit'));
                    }
                }

                // Escape to clear input
                if (e.key === 'Escape' && document.activeElement === tokenInput) {
                    tokenInput.value = '';
                    tokenInput.style.borderColor = '';
                    tokenForm.classList.remove('form-success');
                }
            });

            // Prevent zoom on input focus for mobile
            if (isTouchDevice) {
                const viewport = document.querySelector('meta[name="viewport"]');
                if (viewport) {
                    viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0');
                }
            }
        });
    </script>
</body>
</html>