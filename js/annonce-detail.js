/**
 * ANNONCE-DETAIL.JS
 * Gestion de la page de detail d'une annonce
 */

document.addEventListener('DOMContentLoaded', function() {

    /**
     * Changement de la photo principale de la galerie
     */
    window.changeMainPhoto = function(photoUrl, thumbElement) {
        const mainPhoto = document.getElementById('mainPhoto');
        const allThumbs = document.querySelectorAll('.annonce-detail__gallery-thumb');

        if (mainPhoto) {
            // Changer la photo principale avec animation
            mainPhoto.style.opacity = '0';

            setTimeout(() => {
                mainPhoto.src = photoUrl;
                mainPhoto.style.opacity = '1';
            }, 200);
        }

        // Mettre à jour l'état actif des miniatures
        allThumbs.forEach(thumb => thumb.classList.remove('active'));
        if (thumbElement) {
            thumbElement.classList.add('active');
        }
    };

    /**
     * Gestion du bouton favori
     */
    const favoriteBtn = document.querySelector('.annonce-detail__btn-favorite');

    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            const annonceId = this.dataset.annonceId;
            const isActive = this.classList.contains('active');

            // Appeler l'API pour toggle le favori
            fetch('api/toggle-favori.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    annonce_id: annonceId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle l'état visuel
                    this.classList.toggle('active');

                    // Changer l'icône
                    const icon = this.querySelector('i');
                    const text = this.querySelector('span');

                    if (this.classList.contains('active')) {
                        icon.classList.remove('fa-regular');
                        icon.classList.add('fa-solid');
                        text.textContent = 'Retirer des favoris';
                    } else {
                        icon.classList.remove('fa-solid');
                        icon.classList.add('fa-regular');
                        text.textContent = 'Ajouter aux favoris';
                    }

                    // Animation de feedback
                    this.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                } else {
                    console.error('Erreur:', data.error);
                    alert('Erreur lors de l\'ajout aux favoris');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout aux favoris');
            });
        });
    }

    /**
     * Animation au scroll des sections
     */
    const sections = document.querySelectorAll('.annonce-detail__section');

    if (sections.length > 0 && 'IntersectionObserver' in window) {
        const sectionObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        sections.forEach((section, index) => {
            // Animation initiale
            section.style.opacity = '0';
            section.style.transform = 'translateY(30px)';
            section.style.transition = `all 0.6s ease ${index * 0.1}s`;

            // Observer la section
            sectionObserver.observe(section);
        });
    }
});

/**
 * Fonction pour postuler à une annonce
 */
function postuler(annonceId) {
    if (!confirm('Voulez-vous vraiment postuler à cette annonce ?')) {
        return;
    }

    // Récupérer le token CSRF depuis la page
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('api/postuler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            annonce_id: annonceId,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Votre candidature a été envoyée avec succès !');

            // Désactiver le bouton
            const applyBtn = document.querySelector('.annonce-detail__btn-apply');
            if (applyBtn) {
                applyBtn.disabled = true;
                applyBtn.innerHTML = '<i class="fa-solid fa-check"></i> Candidature envoyee';
            }
        } else {
            alert('Erreur: ' + (data.error || 'Impossible d\'envoyer la candidature'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'envoi de la candidature');
    });
}
