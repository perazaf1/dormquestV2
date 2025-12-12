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

    // Upload auto de la photo et preview
    const photoInput = document.getElementById('photo');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Vérifier la taille
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                alert('La photo ne doit pas dépasser 2MB');
                this.value = '';
                return;
            }

            // Vérifier le type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format non autorisé. Utilisez JPG ou PNG.');
                this.value = '';
                return;
            }

            // Afficher un aperçu immédiat pour l'utilisateur
            const reader = new FileReader();
            reader.onload = function(event) {
                const previewImg = document.getElementById('preview-photo');
                if (previewImg) {
                    previewImg.src = event.target.result;
                    previewImg.style.opacity = '1';
                }
            };
            reader.readAsDataURL(file);

            // Préparer l'upload vers l'API
            try {
                const tokenInput = document.querySelector('input[name="csrf_token"]');
                const csrf = tokenInput ? tokenInput.value : '';
                const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
                const url = baseUrl + '/api/upload-profile-photo.php';

                const formData = new FormData();
                formData.append('photo', file);
                formData.append('csrf_token', csrf);

                fetch(url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(resp => resp.json())
                .then(data => {
                    if (data && data.success && data.path) {
                        // Remplacer l'aperçu par le chemin serveur (persistant)
                        const previewImg = document.getElementById('preview-photo');
                        if (previewImg) {
                            previewImg.src = baseUrl + '/' + data.path;
                            previewImg.style.opacity = '1';
                        }
                        console.log('Photo uploadée :', data.path);

                        // Créer le bouton de suppression s'il n'existe pas
                        let deleteBtn = document.getElementById('deletePhotoBtn');
                        if (!deleteBtn) {
                            deleteBtn = document.createElement('button');
                            deleteBtn.type = 'button';
                            deleteBtn.id = 'deletePhotoBtn';
                            deleteBtn.className = 'btn-delete-photo';
                            deleteBtn.textContent = '🗑️ Supprimer la photo';
                            
                            // Insérer après le champ photo
                            const photoGroup = photoInput.parentElement;
                            if (photoGroup) {
                                photoGroup.appendChild(deleteBtn);
                            }

                            // Ajouter l'event listener au nouveau bouton
                            attachDeletePhotoListener(deleteBtn);
                        }
                    } else if (data && data.error) {
                        alert('Erreur upload: ' + data.error);
                        photoInput.value = '';
                    } else {
                        alert('Erreur inconnue lors de l\'upload');
                        photoInput.value = '';
                    }
                })
                .catch(err => {
                    console.error('Erreur upload photo :', err);
                    alert('Erreur lors de l\'upload de la photo');
                    photoInput.value = '';
                });
            } catch (e) {
                console.error('upload photo error', e);
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

    // Auto-save budget: envoi au serveur et fallback localStorage
    const budgetInput = document.getElementById('budget');
    if (budgetInput) {
        // Charger depuis localStorage si champ vide
        try {
            const storedBudget = localStorage.getItem('budget');
            if (storedBudget && budgetInput.value.trim() === '') {
                budgetInput.value = storedBudget;
            }
        } catch (e) { console.warn('localStorage read failed', e); }

        // Écouter les changements (debounce simple)
        let budgetTimeout = null;
        budgetInput.addEventListener('input', () => {
            clearTimeout(budgetTimeout);
            budgetTimeout = setTimeout(() => {
                // Ne pas supprimer le message d'erreur automatiquement :
                // l'erreur doit rester visible jusqu'à ce que l'utilisateur
                // appuie sur Entrée avec une valeur valide.
                saveBudget(budgetInput.value);
            }, 300);
        });

        // Empêcher la touche Entrée de soumettre le formulaire depuis ce champ
        budgetInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const raw = budgetInput.value.trim();
                const norm = raw.replace(',', '.');
                const num = parseFloat(norm);
                if (isNaN(num) || num < 300) {
                    showBudgetError('Veuillez entrer une valeur minimum 300');
                    budgetInput.focus();
                    return;
                }
                // si tout va bien, sauvegarder et ne supprimer l'erreur
                // qu'après confirmation côté serveur
                saveBudget(budgetInput.value)
                    .then(data => {
                        if (data && data.success) {
                            removeBudgetError();
                            budgetInput.blur();
                        } else if (data && data.error) {
                            showBudgetError(data.error);
                            budgetInput.focus();
                        } else {
                            // comportement par défaut : supprimer l'erreur
                            removeBudgetError();
                        }
                    })
                    .catch(err => {
                        showBudgetError('Erreur lors de la sauvegarde, réessayez');
                        console.error(err);
                    });
            }
        });
        // Remarque : on ne supprime pas l'erreur au blur pour obliger
        // l'utilisateur à confirmer via la touche Entrée.
    }

    // Animation d'entrée
    const mainContent = document.querySelector('.profil-main');
    if (mainContent) {
        mainContent.style.animation = 'slideIn 0.5s ease';
    }

    // Gestion de la suppression de photo de profil
    const deletePhotoBtn = document.getElementById('deletePhotoBtn');
    if (deletePhotoBtn) {
        attachDeletePhotoListener(deletePhotoBtn);
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

// Fonction de sauvegarde du budget côté client
function saveBudget(value) {
    // Enregistrer localement en premier
    try { localStorage.setItem('budget', value); } catch (e) { console.warn('localStorage set failed', e); }

    // Préparer le POST vers l'API
    try {
        const tokenInput = document.querySelector('input[name="csrf_token"]');
        const csrf = tokenInput ? tokenInput.value : '';
        const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
        const url = baseUrl + '/api/update-budget.php';

        const form = new URLSearchParams();
        form.append('csrf_token', csrf);
        form.append('budget', value);

        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: form.toString(),
            credentials: 'same-origin'
        })
        .then(resp => resp.json())
        .then(data => {
            if (data && data.success) {
                console.log('Budget sauvegardé :', data.budget);
            } else if (data && data.error) {
                console.warn('Erreur sauvegarde budget :', data.error);
            }
            return data;
        })
        .catch(err => {
            console.error('Erreur lors de la sauvegarde du budget :', err);
            throw err;
        });
    } catch (e) {
        console.error('saveBudget error', e);
        return Promise.resolve({ success: false, error: 'client_error' });
    }
}

// Afficher un message d'erreur sous le champ budget
function showBudgetError(msg) {
    try {
        const budgetInput = document.getElementById('budget');
        if (!budgetInput) return;
        removeBudgetError();
        const hint = document.createElement('small');
        hint.className = 'budget-hint';
        hint.style.color = '#ef4444';
        hint.style.display = 'block';
        hint.style.marginTop = '6px';
        hint.textContent = msg;
        budgetInput.parentElement.appendChild(hint);
        budgetInput.style.borderColor = '#ef4444';
    } catch (e) { console.warn('showBudgetError', e); }
}

function removeBudgetError() {
    try {
        const budgetInput = document.getElementById('budget');
        if (!budgetInput) return;
        const existing = budgetInput.parentElement.querySelector('small.budget-hint');
        if (existing) existing.remove();
        budgetInput.style.borderColor = '';
    } catch (e) { console.warn('removeBudgetError', e); }
}
// Fonction réutilisable pour attacher l'event listener de suppression de photo
function attachDeletePhotoListener(deleteBtn) {
    deleteBtn.addEventListener('click', function(e) {
        e.preventDefault();

        if (!confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil ?')) {
            return;
        }

        try {
            const tokenInput = document.querySelector('input[name="csrf_token"]');
            const csrf = tokenInput ? tokenInput.value : '';
            const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
            const url = baseUrl + '/api/delete-profile-photo.php';

            const form = new URLSearchParams();
            form.append('csrf_token', csrf);

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: form.toString(),
                credentials: 'same-origin'
            })
            .then(resp => resp.json())
            .then(data => {
                if (data && data.success) {
                    // Remplacer l'aperçu avec l'image par défaut
                    const previewImg = document.getElementById('preview-photo');
                    if (previewImg) {
                        previewImg.src = 'img/default-avatar.png';
                        previewImg.style.opacity = '0.8';
                    }

                    // Vider le champ file input
                    const photoInput = document.getElementById('photo');
                    if (photoInput) {
                        photoInput.value = '';
                    }

                    // Supprimer le bouton de suppression
                    deleteBtn.remove();

                    // Message de succès
                    alert('Photo supprimée avec succès');
                    console.log('Photo supprimée');
                } else if (data && data.error) {
                    alert('Erreur: ' + data.error);
                    console.error('Erreur suppression photo:', data.error);
                } else {
                    alert('Erreur inconnue lors de la suppression');
                }
            })
            .catch(err => {
                console.error('Erreur suppression photo:', err);
                alert('Erreur lors de la suppression de la photo');
            });
        } catch (e) {
            console.error('delete photo error', e);
        }
    });
}