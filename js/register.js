// Register Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    const submitButton = document.querySelector('.btn-submit');

    // Role radios and sections
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const etuFields = document.getElementById('etudiant-fields');
    const loueurFields = document.getElementById('loueur-fields');

    function updateRoleDisplay() {
        const role = document.querySelector('input[name="role"]:checked');
        if (!role) return;
        if (role.value === 'etudiant') {
            etuFields.style.display = 'block';
            loueurFields.style.display = 'none';
        } else {
            etuFields.style.display = 'none';
            loueurFields.style.display = 'block';
        }
    }

    roleRadios.forEach(r => r.addEventListener('change', updateRoleDisplay));

    // Toggle password visibility
    const passwordToggle = document.querySelector('.password-toggle');
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            const input = document.getElementById('password');
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = <i class="fa-regular fa-eye"></i>;
            } else {
                input.type = 'password';
                this.textContent = <i class="fa-regular fa-eye-slash"></i>;
            }
        });
    }

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.5s ease-out';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Form submission validation
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Basic email check
            if (emailInput) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!re.test(emailInput.value.trim())) {
                    isValid = false;
                    showError(emailInput, 'Format d\'email invalide');
                } else {
                    clearError(emailInput);
                }
            }

            // Password length and match
            if (passwordInput) {
                if (passwordInput.value.length < 8) {
                    isValid = false;
                    showError(passwordInput, 'Le mot de passe doit contenir au moins 8 caractères');
                } else {
                    clearError(passwordInput);
                }
            }

            if (passwordConfirm) {
                if (passwordConfirm.value !== passwordInput.value) {
                    isValid = false;
                    showError(passwordConfirm, 'Les mots de passe ne correspondent pas');
                } else {
                    clearError(passwordConfirm);
                }
            }

            // Role specific checks
            const role = document.querySelector('input[name="role"]:checked');
            if (role && role.value === 'etudiant') {
                const ville = document.getElementById('ville_recherche');
                const budget = document.getElementById('budget');
                if (ville && ville.value.trim() === '') {
                    isValid = false;
                    showError(ville, 'La ville de recherche est obligatoire');
                }
                if (budget && (budget.value === '' || Number(budget.value) <= 0)) {
                    isValid = false;
                    showError(budget, 'Le budget doit être un nombre positif');
                }
            }

            if (role && role.value === 'loueur') {
                const tel = document.getElementById('telephone');
                const type = document.getElementById('type_loueur');
                if (type && type.value === '') {
                    isValid = false;
                    showError(type, 'Le type de loueur est obligatoire');
                }
                if (tel && !/^\d{10}$/.test(tel.value.replace(/\s+/g, ''))) {
                    isValid = false;
                    showError(tel, 'Le numéro de téléphone doit contenir 10 chiffres');
                }
            }

            if (!isValid) {
                e.preventDefault();
                return false;
            }

            // Button loading state
            if (submitButton) {
                submitButton.classList.add('loading');
                submitButton.disabled = true;
                submitButton.textContent = 'Inscription en cours...';
            }
        });
    }

    // helper functions
    function showError(input, message) {
        if (!input) return;
        input.classList.add('error');
        const existing = input.parentElement.querySelector('.error-message');
        if (existing) existing.remove();
        const d = document.createElement('div');
        d.className = 'error-message';
        d.style.color = '#dc3545';
        d.style.fontSize = '12px';
        d.style.marginTop = '4px';
        d.textContent = message;
        input.parentElement.appendChild(d);
    }

    function clearError(input) {
        if (!input) return;
        input.classList.remove('error');
        const existing = input.parentElement.querySelector('.error-message');
        if (existing) existing.remove();
    }

    // initialize role display
    updateRoleDisplay();
});
