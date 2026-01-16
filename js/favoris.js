/**
 * FAVORIS.JS
 * Gestion de la page des favoris
 */

document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Gestion de la suppression de favoris
     */
    document.querySelectorAll('.remove-fav').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Voulez-vous vraiment retirer cette annonce de vos favoris ?')) {
                return;
            }

            const annonceId = parseInt(this.dataset.annonceId, 10);
            const favId = parseInt(this.dataset.favId, 10);
            const row = document.getElementById('fav-row-' + favId);

            // Désactiver le bouton pendant le traitement
            this.disabled = true;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Retrait...';

            fetch('api/toggle-favori.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json' 
                },
                body: JSON.stringify({ 
                    annonce_id: annonceId, 
                    action: 'remove' 
                })
            })
            .then(function(resp) { 
                return resp.json(); 
            })
            .then(function(json) {
                if (json.success) {
                    // Animation de suppression
                    if (row) {
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-20px)';
                        row.style.transition = 'all 0.3s ease';
                        
                        setTimeout(function() {
                            row.remove();
                            
                            // Vérifier s'il reste des favoris
                            const tbody = document.querySelector('.table tbody');
                            if (tbody && tbody.children.length === 0) {
                                // Recharger la page pour afficher le message "aucun favori"
                                window.location.reload();
                            }
                        }, 300);
                    }
                } else {
                    alert('Erreur : ' + (json.error || json.message || 'Impossible de retirer le favori'));
                    // Réactiver le bouton en cas d'erreur
                    this.disabled = false;
                    this.innerHTML = '<i class="fa-solid fa-trash-can"></i> Retirer';
                }
            })
            .catch(function(err) {
                console.error('Erreur:', err);
                alert('Erreur réseau lors de la suppression.');
                // Réactiver le bouton en cas d'erreur
                this.disabled = false;
                this.innerHTML = '<i class="fa-solid fa-trash-can"></i> Retirer';
            });
        });
    });
});
