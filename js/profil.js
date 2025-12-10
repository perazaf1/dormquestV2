/**
 * profil.js - Gestion des interactions sur la page profil
 */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss des alertes après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert.classList.contains('alert--success')) {
            setTimeout(() => {
                alert.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    });

    // Confirmation de suppression de compte
    const deleteBtn = document.getElementById('delete-account-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const confirmed = confirm(
                '⚠️ ATTENTION!\n\nCette action est IRRÉVERSIBLE.\n' +
                'Êtes-vous vraiment sûr de vouloir supprimer votre compte?\n\n' +
                'Tapez "SUPPRIMER" pour confirmer.'
            );
            
            if (confirmed) {
                const input = prompt('Confirmez en tapant: SUPPRIMER');
                if (input === 'SUPPRIMER') {
                    // TODO: Implémenter suppression de compte
                    alert('Fonction de suppression à implémenter');
                }
            }
        });
    }

    // Preview de la photo avant upload
    const photoInput = document.getElementById('photo');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewImg = document.getElementById('preview-photo');
                    if (previewImg) {
                        previewImg.src = event.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Validation du formulaire
    const form = document.querySelector('.profil-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Vérifier que les mots de passe correspondent
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas!');
                return false;
            }

            // Vérifier la longueur du mot de passe
            if (newPassword && newPassword.length < 8) {
                e.preventDefault();
                alert('Le nouveau mot de passe doit contenir au moins 8 caractères!');
                return false;
            }

            // Vérifier l'email
            const email = document.getElementById('email').value;
            if (email && !isValidEmail(email)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide!');
                return false;
            }
        });
    }

    // Fonction de validation email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Validation du téléphone (format français)
    const phoneInput = document.getElementById('telephone');
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            const phone = this.value.replace(/\s/g, '');
            if (this.value && !/^[0-9]{10}$/.test(phone)) {
                this.style.borderColor = '#ef4444';
                const hint = document.createElement('small');
                hint.style.color = '#ef4444';
                hint.style.display = 'block';
                hint.textContent = 'Le téléphone doit contenir 10 chiffres';
                
                const existing = this.parentElement.querySelector('small.phone-hint');
                if (existing) existing.remove();
                hint.classList.add('phone-hint');
                this.parentElement.appendChild(hint);
            } else {
                this.style.borderColor = '';
                const hint = this.parentElement.querySelector('small.phone-hint');
                if (hint) hint.remove();
            }
        });
    }

    // Animation d'entrée
    const mainContent = document.querySelector('.profil-main');
    if (mainContent) {
        mainContent.style.animation = 'slideIn 0.5s ease';
    }
});

// Animation fadeOut
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }
`;
document.head.appendChild(style);
