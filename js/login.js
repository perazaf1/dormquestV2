/**
 * LOGIN PAGE JAVASCRIPT
 * Gère la validation du formulaire de connexion, l'affichage des erreurs,
 * et la fonctionnalité "Se souvenir de moi"
 */

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    // Récupération des éléments du formulaire
    const form = document.querySelector('.login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitButton = document.querySelector('.btn-submit');

    /**
     * VALIDATION EMAIL EN TEMPS RÉEL
     * Vérifie l'email lors de la perte de focus (blur) et pendant la saisie si une erreur existe
     */
    if (emailInput) {
        // Validation quand l'utilisateur quitte le champ
        emailInput.addEventListener('blur', function() {
            validateEmail(this);
        });

        // Validation en temps réel si le champ est en erreur
        emailInput.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateEmail(this);
            }
        });
    }

    /**
     * VALIDATION MOT DE PASSE
     * Vérifie le mot de passe lors de la perte de focus
     */
    if (passwordInput) {
        passwordInput.addEventListener('blur', function() {
            validatePassword(this);
        });
    }

    /**
     * BASCULER LA VISIBILITÉ DU MOT DE PASSE
     * Permet d'afficher/masquer le mot de passe en clair
     */
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

    /**
     * SOUMISSION DU FORMULAIRE
     * Valide tous les champs avant l'envoi et affiche un état de chargement
     */
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Valider tous les champs
            if (emailInput && !validateEmail(emailInput)) {
                isValid = false;
            }

            if (passwordInput && !validatePassword(passwordInput)) {
                isValid = false;
            }

            // Empêcher la soumission si des erreurs existent
            if (!isValid) {
                e.preventDefault();
                return false;
            }

            // Afficher l'état de chargement pendant la connexion
            submitButton.classList.add('loading');
            submitButton.disabled = true;
            submitButton.textContent = 'Connexion en cours...';
        });
    }

    /**
     * AUTO-SUPPRESSION DES MESSAGES D'ALERTE
     * Fait disparaître les messages de succès/erreur après 5 secondes
     */
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.5s ease-out';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000); // Délai de 5 secondes
    });

    /**
     * AJOUT DE L'ANIMATION FADEOUT
     * Crée une animation CSS pour faire disparaître les alertes en douceur
     */
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
    `;
    document.head.appendChild(style);
});

/**
 * FONCTION DE VALIDATION D'EMAIL
 * @param {HTMLInputElement} input - Le champ email à valider
 * @returns {boolean} - true si l'email est valide, false sinon
 * 
 * Vérifie que:
 * - Le champ n'est pas vide
 * - Le format correspond à un email valide (regex)
 */
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

/**
 * FONCTION DE VALIDATION DU MOT DE PASSE
 * @param {HTMLInputElement} input - Le champ mot de passe à valider
 * @returns {boolean} - true si le mot de passe est valide, false sinon
 * 
 * Vérifie que:
 * - Le champ n'est pas vide
 * - Le mot de passe contient au moins 6 caractères
 */
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

/**
 * AFFICHER UN MESSAGE D'ERREUR
 * @param {HTMLInputElement} input - Le champ en erreur
 * @param {string} message - Le message d'erreur à afficher
 * 
 * Ajoute une classe 'error' au champ et crée un div avec le message d'erreur
 */
function showError(input, message) {
    input.classList.add('error');

    // Supprimer un message d'erreur existant
    const existingError = input.parentElement.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    // Créer un nouveau message d'erreur
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '4px';
    errorDiv.textContent = message;

    input.parentElement.appendChild(errorDiv);
}

/**
 * EFFACER UN MESSAGE D'ERREUR
 * @param {HTMLInputElement} input - Le champ dont l'erreur doit être effacée
 * 
 * Retire la classe 'error' et supprime le message d'erreur associé
 */
function clearError(input) {
    input.classList.remove('error');

    const errorMessage = input.parentElement.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

/**
 * DÉFILEMENT DOUX VERS LE HAUT
 * Fait défiler la page vers le haut en douceur (utile pour afficher les alertes)
 */
function smoothScrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

/**
 * GESTION DE LA FONCTIONNALITÉ "SE SOUVENIR DE MOI"
 * Utilise localStorage pour sauvegarder l'email si l'utilisateur coche la case
 */
const rememberCheckbox = document.querySelector('input[name="remember_me"]');
const emailInputField = document.getElementById('email');

if (rememberCheckbox && emailInputField) {
    /**
     * CHARGER L'EMAIL SAUVEGARDÉ
     * Au chargement de la page, récupère l'email depuis localStorage si disponible
     */
    const savedEmail = localStorage.getItem('rememberedEmail');
    if (savedEmail) {
        emailInputField.value = savedEmail;
        rememberCheckbox.checked = true;
    }

    /**
     * SAUVEGARDER L'EMAIL À LA SOUMISSION
     * Sauvegarde l'email dans localStorage si la case est cochée,
     * sinon supprime l'email sauvegardé
     */
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