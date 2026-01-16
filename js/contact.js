/**
 * CONTACT.JS
 * Gestion du formulaire de contact
 */

document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact');
    const contactMessage = document.getElementById('contact-message');
    const submitBtn = document.getElementById('contact-submit');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Récupérer les données du formulaire
            const formData = new FormData(contactForm);

            // Désactiver le bouton
            submitBtn.disabled = true;
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Envoi en cours...';

            // Envoyer via AJAX
            fetch('api/send-contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;

                // Afficher le message
                contactMessage.className = data.success ? 'alert alert-success' : 'alert alert-error';
                contactMessage.textContent = data.message || data.error;
                contactMessage.style.display = 'block';
                contactMessage.style.marginBottom = '1rem';

                // Réinitialiser le formulaire si succès
                if (data.success) {
                    contactForm.reset();
                    setTimeout(() => {
                        contactMessage.style.display = 'none';
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                contactMessage.className = 'alert alert-error';
                contactMessage.textContent = 'Erreur lors de l\'envoi du message. Veuillez réessayer.';
                contactMessage.style.display = 'block';
                contactMessage.style.marginBottom = '1rem';
            });
        });
    }
});
