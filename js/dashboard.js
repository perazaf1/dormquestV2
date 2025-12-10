/**
 * DASHBOARD JAVASCRIPT
 * Gère les confirmations d'actions, l'affichage des modales de messages,
 * et l'auto-suppression des alertes sur le tableau de bord
 */

/**
 * CONFIRMER UNE ACTION GÉNÉRIQUE
 * @param {HTMLFormElement} form - Le formulaire à soumettre après confirmation
 * @returns {boolean} - true si l'utilisateur confirme, false sinon
 * 
 * Affiche une boîte de dialogue de confirmation avant d'exécuter une action
 * Peut être utilisé dans l'attribut onsubmit d'un formulaire: onsubmit="return confirmAction(this)"
 */
function confirmAction(form) {
    return confirm('Confirmer cette action ?');
}

/**
 * CONFIRMER L'ANNULATION D'UNE CANDIDATURE
 * @returns {boolean} - true si l'utilisateur confirme l'annulation, false sinon
 * 
 * Affiche une boîte de dialogue spécifique pour confirmer l'annulation d'une candidature
 * Empêche les annulations accidentelles
 */
function confirmCancel() {
    return confirm('Voulez-vous vraiment annuler cette candidature ?');
}

/**
 * INITIALISATION DU DASHBOARD
 * Point d'entrée pour ajouter des améliorations futures aux boutons ou autres éléments
 */
document.addEventListener('DOMContentLoaded', function() {
    // Emplacement réservé pour d'éventuelles améliorations futures des boutons
    // Exemple: ajout d'animations, de tooltips, etc.
});

/**
 * GESTION DES ALERTES ET DES MODALES DE MESSAGES
 * Configure l'auto-suppression des alertes et les interactions avec la modale de messages
 */
document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * AUTO-SUPPRESSION DES ALERTES
     * Fait disparaître automatiquement les messages d'alerte après 5 secondes
     * avec une animation de fondu et de déplacement vers le haut
     */
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(a => {
        setTimeout(() => {
            // Configuration de l'animation de sortie
            a.style.transition = 'opacity 0.4s, transform 0.4s';
            a.style.opacity = '0';
            a.style.transform = 'translateY(-8px)';
            
            // Suppression du DOM après l'animation
            setTimeout(() => a.remove(), 400);
        }, 5000); // Délai de 5 secondes avant le début de l'animation
    });

    /**
     * BOUTONS "VOIR LE MESSAGE"
     * Configure les écouteurs d'événements pour tous les boutons permettant
     * d'afficher les messages des candidatures dans une modale
     */
    document.querySelectorAll('.view-message').forEach(btn => {
        btn.addEventListener('click', function() {
            // Récupération des données depuis les attributs data-*
            const message = this.getAttribute('data-message') || '';
            const student = this.getAttribute('data-student') || '';
            const annonce = this.getAttribute('data-annonce') || '';
            
            // Affichage de la modale avec les informations récupérées
            showMessageModal(student, annonce, message);
        });
    });

    /**
     * FERMETURE DE LA MODALE
     * Configure les différentes méthodes pour fermer la modale de message
     */
    const modal = document.getElementById('messageModal');
    if (modal) {
        // Fermeture via le bouton de fermeture (X)
        modal.querySelector('.modal__close').addEventListener('click', hideMessageModal);
        
        // Fermeture en cliquant sur le fond (backdrop)
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideMessageModal();
            }
        });
    }
});

/**
 * AFFICHER LA MODALE DE MESSAGE
 * @param {string} student - Le nom de l'étudiant qui a envoyé le message
 * @param {string} annonce - Le titre de l'annonce concernée
 * @param {string} message - Le contenu du message à afficher
 * 
 * Ouvre une modale pour afficher le message d'un candidat
 * Le titre de la modale affiche le nom de l'étudiant et l'annonce
 * Format du titre: "Nom Étudiant — Titre Annonce" ou "Message" si vide
 */
function showMessageModal(student, annonce, message) {
    const modal = document.getElementById('messageModal');
    if (!modal) return;
    
    // Construction du titre de la modale
    const title = (student ? student + ' — ' : '') + (annonce ? annonce : 'Message');
    modal.querySelector('.modal__title').textContent = title;
    
    // Affichage du contenu du message ou d'un message par défaut
    modal.querySelector('.modal__body').textContent = message || '(Aucun message)';
    
    // Activation de la modale (ajout de la classe 'active')
    modal.classList.add('active');
}

/**
 * MASQUER LA MODALE DE MESSAGE
 * Ferme la modale de message en retirant la classe 'active'
 * La modale reste dans le DOM mais devient invisible
 */
function hideMessageModal() {
    const modal = document.getElementById('messageModal');
    if (!modal) return;
    
    // Désactivation de la modale
    modal.classList.remove('active');
}