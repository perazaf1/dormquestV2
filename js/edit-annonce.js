// Gestion de la suppression de photos
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-photo-btn');

    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')) {
                return;
            }

            const photoId = this.getAttribute('data-photo-id');
            const csrfToken = this.getAttribute('data-csrf');
            const photoItem = this.closest('.photo-item');

            // Désactiver le bouton pendant le traitement
            button.disabled = true;
            button.textContent = 'Suppression...';

            // Envoyer la requête de suppression
            fetch('api/delete-photo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    photo_id: photoId,
                    csrf_token: csrfToken
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    // Supprimer l'élément du DOM avec animation
                    photoItem.style.opacity = '0';
                    setTimeout(function() {
                        photoItem.remove();

                        // Mettre à jour le compteur
                        const label = document.querySelector('.form-group label');
                        if (label && label.textContent.includes('Photos actuelles')) {
                            const photosGrid = document.querySelector('.photos-grid');
                            const remainingPhotos = photosGrid ? photosGrid.querySelectorAll('.photo-item').length : 0;
                            const maxPhotos = label.textContent.match(/\/(\d+)\)/);
                            if (maxPhotos) {
                                label.textContent = 'Photos actuelles (' + remainingPhotos + '/' + maxPhotos[1] + ')';
                            }

                            // Si plus de photos, afficher le message "Aucune photo"
                            if (remainingPhotos === 0) {
                                photosGrid.innerHTML = '<p class="no-photos">Aucune photo pour cette annonce</p>';
                            }
                        }

                        // Mettre à jour le label "Ajouter des photos"
                        const addLabel = document.querySelectorAll('.form-group label')[1];
                        if (addLabel && addLabel.textContent.includes('Ajouter des photos')) {
                            const photosGrid = document.querySelector('.photos-grid');
                            const remainingPhotos = photosGrid ? photosGrid.querySelectorAll('.photo-item').length : 0;
                            const maxPhotos = addLabel.textContent.match(/max (\d+)/);
                            if (maxPhotos) {
                                const max = parseInt(maxPhotos[1]);
                                addLabel.textContent = 'Ajouter des photos (max ' + (max + 1) + ' photos)';
                            }
                        }
                    }, 300);
                } else {
                    alert('Erreur : ' + (data.error || 'Impossible de supprimer la photo'));
                    button.disabled = false;
                    button.textContent = 'Supprimer';
                }
            })
            .catch(function(error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la suppression');
                button.disabled = false;
                button.textContent = 'Supprimer';
            });
        });
    });
});
