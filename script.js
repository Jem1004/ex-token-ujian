// Token system main JavaScript file

// Initialize token if not exists
function initializeToken() {
    const tokenExpiry = Date.now() + (15 * 60 * 1000); // 15 minutes from now
    const randomToken = generateRandomToken(5);
    const fullToken = `TOKEN_${randomToken}_${Date.now().toString(36).slice(-4)}`;
    
    sessionStorage.setItem('currentToken', fullToken);
    sessionStorage.setItem('tokenExpiry', tokenExpiry);
    
    // Also store in localStorage for cross-tab access
    localStorage.setItem('globalToken', fullToken);
    localStorage.setItem('globalTokenExpiry', tokenExpiry);
    
    return {
        token: randomToken,
        expiry: tokenExpiry
    };
}

// Generate a random token of specified length
function generateRandomToken(length) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let token = '';
    
    for (let i = 0; i < length; i++) {
        token += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    return token;
}

// Check if token is valid
function isTokenValid(inputToken) {
    const currentToken = sessionStorage.getItem('currentToken');
    const tokenExpiry = sessionStorage.getItem('tokenExpiry');
    
    if (!currentToken || !tokenExpiry) {
        return false;
    }
    
    // Check if token has expired
    if (Date.now() >= parseInt(tokenExpiry)) {
        return false;
    }
    
    // Extract token part from the full token
    const tokenParts = currentToken.split('_');
    if (tokenParts.length < 2) {
        return false;
    }
    
    const tokenValue = tokenParts[1];
    
    // Compare with input token
    return inputToken.toUpperCase() === tokenValue;
}

// Refresh token
function refreshToken() {
    return initializeToken();
}

// Get current token info
function getCurrentToken() {
    const currentToken = sessionStorage.getItem('currentToken');
    const tokenExpiry = sessionStorage.getItem('tokenExpiry');
    
    if (!currentToken || !tokenExpiry) {
        return null;
    }
    
    const tokenParts = currentToken.split('_');
    if (tokenParts.length < 2) {
        return null;
    }
    
    return {
        token: tokenParts[1],
        expiry: parseInt(tokenExpiry),
        isExpired: Date.now() >= parseInt(tokenExpiry)
    };
}

// Format time remaining
function formatTimeRemaining(expiryTime) {
    const remaining = expiryTime - Date.now();
    
    if (remaining <= 0) {
        return 'Expired';
    }
    
    const minutes = Math.floor(remaining / 60000);
    const seconds = Math.floor((remaining % 60000) / 1000);
    
    return `${minutes}m ${seconds}s`;
}

// Update token display in admin panel
function updateTokenDisplay() {
    const tokenInfo = getCurrentToken();
    const tokenBox = document.querySelector('.token-box');
    const tokenTimeInfo = document.querySelector('.token-info');
    
    if (!tokenBox || !tokenTimeInfo) {
        return;
    }
    
    if (!tokenInfo) {
        tokenBox.textContent = 'No active token';
        tokenTimeInfo.textContent = '';
        return;
    }
    
    tokenBox.textContent = tokenInfo.token;
    
    if (tokenInfo.isExpired) {
        tokenTimeInfo.textContent = 'Token has expired';
        tokenTimeInfo.style.color = 'var(--danger-color)';
    } else {
        const timeRemaining = formatTimeRemaining(tokenInfo.expiry);
        tokenTimeInfo.textContent = `Expires in: ${timeRemaining}`;
        tokenTimeInfo.style.color = 'var(--gray-color)';
    }
}

// Initialize admin panel
function initAdminPanel() {
    // Check if token exists, if not create one
    if (!getCurrentToken()) {
        initializeToken();
    }
    
    // Update token display
    updateTokenDisplay();
    
    // Set up refresh button
    const refreshBtn = document.querySelector('.btn-refresh');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            refreshToken();
            updateTokenDisplay();
            
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'success-message show';
            successMsg.innerHTML = '<i class="fas fa-check-circle"></i> Token refreshed successfully!';
            
            const adminActions = document.querySelector('.admin-actions');
            adminActions.prepend(successMsg);
            
            // Remove message after 3 seconds
            setTimeout(() => {
                successMsg.remove();
            }, 3000);
        });
    }
    
    // Update token display every second
    setInterval(updateTokenDisplay, 1000);
}

// Initialize user token form
function initTokenForm() {
    const tokenForm = document.getElementById('tokenForm');
    const tokenInput = document.getElementById('tokenInput');
    const errorMessage = document.getElementById('errorMessage');
    
    if (!tokenForm || !tokenInput) {
        return;
    }
    
    // Auto focus on input when page loads
    tokenInput.focus();
    
    // Format input as uppercase
    tokenInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Handle form submission
    tokenForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const inputToken = tokenInput.value.trim();
        
        if (isTokenValid(inputToken)) {
            // Success - show success message
            if (errorMessage) {
                errorMessage.textContent = '';
                errorMessage.classList.remove('show');
            }
            
            // Add success animation to input
            tokenInput.classList.add('success-input');
            
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'success-message show';
            successMsg.innerHTML = '<i class="fas fa-check-circle"></i> Token valid! Redirecting...';
            tokenForm.appendChild(successMsg);
            
            // Redirect after delay
            setTimeout(() => {
                redirectToTarget();
            }, 1500);
        } else {
            // Invalid token
            if (errorMessage) {
                const tokenInfo = getCurrentToken();
                
                if (tokenInfo && tokenInfo.isExpired) {
                    errorMessage.textContent = 'Token has expired. Please request a new one.';
                } else {
                    errorMessage.textContent = 'Invalid token. Please try again.';
                }
                
                errorMessage.classList.add('show');
            }
            
            // Add shake animation
            tokenInput.classList.add('shake');
            setTimeout(() => {
                tokenInput.classList.remove('shake');
            }, 500);
        }
    });
}

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on admin page
    if (document.querySelector('.admin-card')) {
        initAdminPanel();
    }
    
    // Check if we're on token input page
    if (document.getElementById('tokenForm')) {
        initTokenForm();
    }
    
    // Initialize help tooltip functionality
    const helpBtn = document.getElementById('helpBtn');
    const helpTooltip = document.querySelector('.help-tooltip');
    
    if (helpBtn && helpTooltip) {
        helpBtn.addEventListener('mouseenter', function() {
            helpTooltip.style.opacity = '1';
            helpTooltip.style.visibility = 'visible';
        });
        
        helpBtn.addEventListener('mouseleave', function() {
            helpTooltip.style.opacity = '0';
            helpTooltip.style.visibility = 'hidden';
        });
        
        // For touch devices
        helpBtn.addEventListener('click', function() {
            if (helpTooltip.style.visibility === 'visible') {
                helpTooltip.style.opacity = '0';
                helpTooltip.style.visibility = 'hidden';
            } else {
                helpTooltip.style.opacity = '1';
                helpTooltip.style.visibility = 'visible';
                
                // Hide tooltip after 3 seconds on touch devices
                setTimeout(() => {
                    helpTooltip.style.opacity = '0';
                    helpTooltip.style.visibility = 'hidden';
                }, 3000);
            }
        });
    }
});