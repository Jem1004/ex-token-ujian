#!/usr/bin/env node

const http = require('http');
const fs = require('fs');
const path = require('path');
const url = require('url');

const PORT = 8000;

// MIME types
const mimeTypes = {
  '.html': 'text/html',
  '.js': 'text/javascript',
  '.css': 'text/css',
  '.json': 'application/json',
  '.png': 'image/png',
  '.jpg': 'image/jpg',
  '.gif': 'image/gif',
  '.svg': 'image/svg+xml',
  '.wav': 'audio/wav',
  '.mp4': 'video/mp4',
  '.woff': 'application/font-woff',
  '.ttf': 'application/font-ttf',
  '.eot': 'application/vnd.ms-fontobject',
  '.otf': 'application/font-otf',
  '.wasm': 'application/wasm'
};

const server = http.createServer((req, res) => {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  const parsedUrl = url.parse(req.url);
  let pathname = parsedUrl.pathname;

  // Default to index.php
  if (pathname === '/') {
    pathname = '/index.php';
  }

  const ext = path.parse(pathname).ext;
  const contentType = mimeTypes[ext] || 'text/html';

  // Handle PHP files
  if (pathname.endsWith('.php')) {
    handlePhpFile(pathname, req, res);
  } else {
    // Handle static files
    const filePath = path.join(__dirname, pathname);

    fs.readFile(filePath, (err, data) => {
      if (err) {
        res.writeHead(404, { 'Content-Type': 'text/html' });
        res.end('<h1>404 Not Found</h1><p>File not found: ' + pathname + '</p>');
        return;
      }

      res.writeHead(200, { 'Content-Type': contentType });
      res.end(data);
    });
  }
});

function handlePhpFile(phpFile, req, res) {
  const filePath = path.join(__dirname, phpFile);

  fs.readFile(filePath, 'utf8', (err, data) => {
    if (err) {
      res.writeHead(404, { 'Content-Type': 'text/html' });
      res.end('<h1>404 Not Found</h1><p>PHP file not found: ' + phpFile + '</p>');
      return;
    }

    // Simple PHP parser for this specific project
    let htmlContent = processPhpCode(data, req);

    res.writeHead(200, { 'Content-Type': 'text/html' });
    res.end(htmlContent);
  });
}

function processPhpCode(phpCode, req) {
  // Remove PHP tags and process basic PHP logic
  let output = phpCode;

  // Simple include simulation
  output = output.replace(/require_once\s+['"]([^'"]+)['"];?/g, (match, file) => {
    const includedFile = path.join(__dirname, file);
    if (fs.existsSync(includedFile)) {
      return `<!-- Included: ${file} -->\n` + fs.readFileSync(includedFile, 'utf8');
    }
    return `<!-- Could not include: ${file} -->`;
  });

  // Remove other PHP code for simplicity
  output = output.replace(/<\?php[\s\S]*?\?>/g, (match) => {
    // Handle basic echo statements
    if (match.includes('echo')) {
      return match.match(/echo\s+['"]([^'"]+)['"];?/) ? match.match(/echo\s+['"]([^'"]+)['"];?/)[1] : '';
    }
    return '';
  });

  // Handle the main page logic
  if (output.includes('index.php')) {
    output = generateIndexPage(req);
  }

  return output;
}

function generateIndexPage(req) {
  // Read the actual index.php and process it
  const indexPath = path.join(__dirname, 'index.php');
  if (!fs.existsSync(indexPath)) {
    return generateDemoIndex();
  }

  let phpContent = fs.readFileSync(indexPath, 'utf8');

  // Extract HTML part from PHP
  let htmlContent = phpContent.split('?>').pop() || phpContent;

  // Process variables
  htmlContent = htmlContent.replace(/\$errorMessage/g, '');
  htmlContent = htmlContent.replace(/\$isRateLimited/g, 'false');

  return htmlContent;
}

function generateDemoIndex() {
  return `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Ujian Daring - SMP Negeri 3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <header class="header">
            <div class="logo-section">
                <img src="smpn3.png" alt="Logo SMPN3" class="school-logo" onerror="this.style.display='none'">
                <div class="logo-text">
                    <h1 class="school-name">SMP NEGERI 3</h1>
                    <p class="location">Penajam Paser Utara</p>
                </div>
            </div>
        </header>

        <section class="exam-section">
            <div class="exam-card">
                <div class="exam-header">
                    <h2 class="exam-title">Portal Ujian Daring</h2>
                    <p class="exam-subtitle">Masukkan token Anda untuk mengakses ujian</p>
                </div>

                <form class="token-form" method="POST">
                    <div class="form-group">
                        <label for="token" class="form-label">Token Ujian</label>
                        <div class="input-wrapper">
                            <input
                                type="text"
                                id="token"
                                name="token"
                                class="token-input"
                                placeholder="Masukkan 5 karakter token"
                                maxlength="5"
                                required
                                pattern="[A-Z0-9]{5}"
                                style="text-transform: uppercase;"
                            >
                            <div class="input-icon">🔑</div>
                        </div>
                        <small class="input-hint">Token berisi 5 karakter huruf dan angka</small>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span class="btn-text">Masuk Ujian</span>
                        <span class="btn-icon">📝</span>
                    </button>
                </form>
            </div>
        </section>

        <footer class="footer">
            <p class="footer-text">© 2025 SMP Negeri 3 Penajam Paser Utara</p>
        </footer>
    </main>

    <script src="script.js"></script>
</body>
</html>`;
}

server.listen(PORT, () => {
  console.log(`🚀 Development server running at http://localhost:${PORT}`);
  console.log(`📁 Serving files from: ${__dirname}`);
  console.log(`\n📝 Notes:`);
  console.log(`- This is a PHP simulator for development`);
  console.log(`- For production, install real PHP server`);
  console.log(`- Static files (CSS, JS, images) will work normally`);
  console.log(`- PHP logic is simulated`);
});

// Handle graceful shutdown
process.on('SIGINT', () => {
  console.log('\n👋 Development server stopped');
  process.exit(0);
});