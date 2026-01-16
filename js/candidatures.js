/**
 * ============================================================================
 * candidatures.js - Gestion interactive des candidatures (AJAX)
 * ============================================================================
 * 
 * FONCTIONNALITÉS :
 * - Annulation de candidature sans rechargement (AJAX)
 * - Spinner de chargement pendant l'action
 * - Toast notifications (success/error)
 * - Suppression animée de la ligne
 * - Bouton désactivé après clic (évite double-envoi)
 * - Gestion robuste des erreurs réseau
 * - Fallback si JavaScript est désactivé (formulaires HTML classiques)
 * 
 * CONCEPTS ABORDÉS :
 * - Fetch API (modern AJAX)
 * - Manipulation du DOM (getElementById, querySelector, classList)
 * - Gestion des événements (addEventListener)
 * - Async/await et Promises
 * - FormData pour récupérer les données
 * - CSS animations (fade-out)
 * ============================================================================
 */

// Exécuter le code quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // ========================================================================
    // ÉTAPE 1 : Attacher les handlers de clic aux boutons "Annuler"
    // ========================================================================
    
    // Sélectionner TOUS les formulaires de candidature (chacun a une classe "candidature-form")
    const forms = document.querySelectorAll('.candidature-form');
    
    // Pour chaque formulaire, on ajoute un handler pour éviter la soumission classique
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            // Empêcher la soumission HTTP classique du formulaire
            e.preventDefault();
            
            // Traiter l'annulation via AJAX
            handleCancelCandidature(form);
        });
    });
});

/**
 * ========================================================================
 * Fonction principale : gérer l'annulation d'une candidature
 * ========================================================================
 * 
 * @param {HTMLFormElement} form - Le formulaire HTML contenant les données
 */
function handleCancelCandidature(form) {
    // ====================================================================
    // ÉTAPE 2 : Extraire les données du formulaire
    // ====================================================================
    
    // Récupérer l'ID de la candidature (stocké dans data-candidature-id)
    const candidatureId = parseInt(form.dataset.candidatureId, 10);
    
    // Récupérer le titre de l'annonce pour l'affichage
    const annonceTitle = form.dataset.annonceTitle || 'cette candidature';
    
    // Récupérer le token CSRF pour la sécurité (CSRF = Cross-Site Request Forgery)
    const csrfToken = form.querySelector('input[name="csrf_token"]').value;
    
    // ====================================================================
    // ÉTAPE 3 : Confirmation utilisateur
    // ====================================================================
    
    // Afficher une fenêtre de confirmation avant l'action
    // Si l'utilisateur clique sur "Annuler" (cancel), on ne fait rien
    if (!confirm(`Êtes-vous sûr de vouloir annuler votre candidature pour "${annonceTitle}" ?`)) {
        return; // Arrêter l'exécution ici
    }
    
    // ====================================================================
    // ÉTAPE 4 : Récupérer les éléments DOM importants
    // ====================================================================
    
    // Trouver le bouton "Annuler" dans ce formulaire
    const button = form.querySelector('button[type="submit"]');
    
    // Trouver la ligne du tableau (tr) qui contient cette candidature
    const row = form.closest('tr');
    
    // ====================================================================
    // ÉTAPE 5 : Préparer l'interface pour le traitement
    // ====================================================================
    
    // Désactiver le bouton et afficher un spinner
    button.disabled = true;
    button.innerHTML = '<span class="spinner"></span> Annulation...';
    
    // Ajouter une classe CSS pour le style du loader
    button.classList.add('loading');
    
    // ====================================================================
    // ÉTAPE 6 : Envoyer la requête au serveur (AJAX POST)
    // ====================================================================
    
    // Construire les données à envoyer au serveur
    const formData = new FormData(form);
    
    // Envoyer une requête POST asynchrone à api/candidature-action.php
    fetch(form.action, {
        method: 'POST',  // Méthode HTTP POST
        body: formData   // Corps de la requête (données du formulaire)
    })
    .then(function(response) {
        // ================================================================
        // ÉTAPE 7a : La réponse du serveur est arrivée
        // ================================================================
        
        // Vérifier que la requête HTTP a réussi (code 200-299)
        if (!response.ok) {
            // Si ce n'est pas un succès HTTP (ex: 500 erreur serveur)
            throw new Error(`Erreur serveur (${response.status})`);
        }
        
        // La réponse HTTP est OK, on peut la traiter
        // Mais le serveur envoie une redirection (Header Location)
        // Donc on interprète le succès si response.ok = true
        
        // Marquer comme succès
        return { success: true };
    })
    .then(function(result) {
        // ================================================================
        // ÉTAPE 7b : L'action a réussi, mettre à jour l'interface
        // ================================================================
        
        // Afficher une notification de succès (toast)
        showToast('Candidature annulée avec succès !', 'success');
        
        // Animer la suppression de la ligne
        // Ajouter une classe CSS pour la transition (fade-out)
        row.classList.add('row-removing');
        
        // Attendre la fin de l'animation (500ms), puis supprimer la ligne du DOM
        setTimeout(function() {
            row.remove();
        }, 500);
    })
    .catch(function(error) {
        // ================================================================
        // ÉTAPE 7c : Une erreur s'est produite
        // ================================================================
        
        console.error('Erreur lors de l\'annulation :', error);
        
        // Afficher un message d'erreur à l'utilisateur (toast)
        showToast(`Erreur : ${error.message}. Veuillez réessayer.`, 'error');
        
        // Réactiver le bouton pour permettre un nouvel essai
        button.disabled = false;
        button.innerHTML = 'Annuler';
        button.classList.remove('loading');
    });
}

/**
 * ========================================================================
 * Fonction utilitaire : afficher une notification (toast)
 * ========================================================================
 * 
 * Les toasts sont des petites notifications UI qui s'affichent temporairement
 * en haut ou bas de la page, sans bloquer l'interaction.
 * 
 * @param {string} message - Le texte à afficher
 * @param {string} type - 'success' ou 'error' (pour le style CSS)
 */
function showToast(message, type) {
    // Créer un élément <div> pour le toast
    const toast = document.createElement('div');
    
    // Ajouter des classes CSS pour le style
    toast.className = `toast toast-${type}`;
    
    // Définir le contenu HTML du toast
    toast.innerHTML = `
        <span>${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Ajouter le toast au document (en haut de la page)
    document.body.appendChild(toast);
    
    // Afficher le toast avec une animation (CSS transition)
    toast.offsetHeight; // Force le reflow (hack pour que l'animation CSS fonctionne)
    toast.classList.add('show');
    
    // Supprimer automatiquement le toast après 4 secondes
    setTimeout(function() {
        toast.classList.remove('show');
        
        // Attendre la fin de l'animation (300ms) avant de retirer du DOM
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 4000);
}

// ============================================================================
// RÉSUMÉ DU FONCTIONNEMENT :
// ============================================================================
//
// 1. Au chargement de la page (DOMContentLoaded) :
//    - Sélectionner tous les formulaires de candidature
//    - Attacher un handler "submit" à chacun
//
// 2. Lors du clic sur "Annuler" :
//    - Bloquer la soumission classique (preventDefault)
//    - Appeler handleCancelCandidature(form)
//
// 3. Dans handleCancelCandidature() :
//    - Extraire les données (ID, titre, token CSRF)
//    - Confirmer avec l'utilisateur
//    - Désactiver le bouton et afficher un spinner
//    - Envoyer une requête POST AJAX au serveur
//    - Gérer la réponse (succès → supprimer ligne animée)
//    - Gérer les erreurs (afficher toast d'erreur)
//
// 4. Affichage des toasts :
//    - Créer un élément <div> avec le message
//    - L'ajouter au DOM
//    - L'afficher avec une animation CSS
//    - Le supprimer automatiquement après 4 secondes
//
// AVANTAGES PAR RAPPORT AU FORMULAIRE CLASSIQUE :
// ✅ Pas de rechargement de page
// ✅ Spinner visuel pendant le chargement
// ✅ Notifications élégantes (toasts)
// ✅ Suppression animée
// ✅ Bouton désactivé = évite double-clic
// ✅ Gestion d'erreur professionnelle
//
// SÉCURITÉ :
// ✅ Token CSRF toujours envoyé (via FormData)
// ✅ Validation serveur intacte
// ✅ Pas de contournement de la sécurité
// ✅ Fallback HTML si JavaScript est désactivé
//
// ============================================================================
