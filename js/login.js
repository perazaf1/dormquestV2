// Login Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitButton = document.querySelector('.btn-submit');

    // Email validation en temps réel
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            validateEmail(this);
        });

        emailInput.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateEmail(this);
            }
        });
    }

    // Password validation
    if (passwordInput) {
        passwordInput.addEventListener('blur', function() {
            validatePassword(this);
        });
    }

    // Toggle password visibility
    const passwordToggle = document.querySelector('.password-toggle');
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '👁️';
            } else {
                input.type = 'password';
                this.textContent = '👁️‍🗨️';
            }
        });
    }

    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Validate all fields
            if (emailInput && !validateEmail(emailInput)) {
                isValid = false;
            }

            if (passwordInput && !validatePassword(passwordInput)) {
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            submitButton.textContent = 'Connexion en cours...';
        });
    }

    // Auto-dismiss success/error messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.5s ease-out';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Add fade out animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
    `;
    document.head.appendChild(style);
});

// Email validation function
function validateEmail(input) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const value = input.value.trim();

    if (value === '') {
        showError(input, 'L\'email est requis');
        return false;
    }

    if (!emailRegex.test(value)) {
        showError(input, 'Format d\'email invalide');
        return false;
    }

    clearError(input);
    return true;
}

// Password validation function
function validatePassword(input) {
    const value = input.value;

    if (value === '') {
        showError(input, 'Le mot de passe est requis');
        return false;
    }

    if (value.length < 6) {
        showError(input, 'Le mot de passe doit contenir au moins 6 caractères');
        return false;
    }

    clearError(input);
    return true;
}

// Show error message
function showError(input, message) {
    input.classList.add('error');

    // Remove existing error message
    const existingError = input.parentElement.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    // Create new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '4px';
    errorDiv.textContent = message;

    input.parentElement.appendChild(errorDiv);
}

// Clear error message
function clearError(input) {
    input.classList.remove('error');

    const errorMessage = input.parentElement.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

// Smooth scroll for alerts
function smoothScrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Remember me localStorage handling
const rememberCheckbox = document.querySelector('input[name="remember_me"]');
const emailInputField = document.getElementById('email');

if (rememberCheckbox && emailInputField) {
    // Load saved email on page load
    const savedEmail = localStorage.getItem('rememberedEmail');
    if (savedEmail) {
        emailInputField.value = savedEmail;
        rememberCheckbox.checked = true;
    }

    // Save email when form is submitted
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            if (rememberCheckbox.checked) {
                localStorage.setItem('rememberedEmail', emailInputField.value);
            } else {
                localStorage.removeItem('rememberedEmail');
            }
        });
    }
}
