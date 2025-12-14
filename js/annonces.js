/**
 * ============================================
 * ANNONCES.JS - Gestion de la page annonces
 * ============================================
 * - Double slider pour le budget
 * - Filtres dynamiques
 * - Animation de la barre de recherche
 */

document.addEventListener('DOMContentLoaded', function() {

    /**
     * ============================================
     * GESTION DU DOUBLE SLIDER DE BUDGET
     * ============================================
     */
    const sliderMin = document.getElementById('sliderMin');
    const sliderMax = document.getElementById('sliderMax');
    const budgetMinDisplay = document.getElementById('budgetMin');
    const budgetMaxDisplay = document.getElementById('budgetMax');

    if (sliderMin && sliderMax && budgetMinDisplay && budgetMaxDisplay) {

        // Fonction pour mettre à jour les valeurs affichées
        function updateBudgetDisplay() {
            const minValue = parseInt(sliderMin.value);
            const maxValue = parseInt(sliderMax.value);

            // Empêcher le chevauchement des sliders
            if (minValue >= maxValue) {
                sliderMin.value = maxValue - 50;
            }
            if (maxValue <= minValue) {
                sliderMax.value = minValue + 50;
            }

            // Mettre à jour l'affichage
            budgetMinDisplay.textContent = sliderMin.value + '€';
            budgetMaxDisplay.textContent = sliderMax.value + '€';

            // Animation de pulsation lors du changement
            budgetMinDisplay.style.transform = 'scale(1.1)';
            budgetMaxDisplay.style.transform = 'scale(1.1)';

            setTimeout(() => {
                budgetMinDisplay.style.transform = 'scale(1)';
                budgetMaxDisplay.style.transform = 'scale(1)';
            }, 200);
        }

        // Écouteurs d'événements pour les sliders
        sliderMin.addEventListener('input', updateBudgetDisplay);
        sliderMax.addEventListener('input', updateBudgetDisplay);

        // Initialiser l'affichage
        updateBudgetDisplay();
    }

    /**
     * ============================================
     * BOUTON RÉINITIALISER LES FILTRES
     * ============================================
     */
    const resetBtn = document.querySelector('.annonces__reset-btn');

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            // Réinitialiser toutes les checkboxes
            const checkboxes = document.querySelectorAll('.annonces__checkbox input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            // Réinitialiser les sliders de budget
            if (sliderMin && sliderMax) {
                sliderMin.value = 0;
                sliderMax.value = 3000;
                updateBudgetDisplay();
            }

            // Animation du bouton
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);

            console.log('Filtres réinitialisés');
        });
    }

    /**
     * ============================================
     * ANIMATION DE LA BARRE DE RECHERCHE
     * ============================================
     */
    const searchInput = document.querySelector('.annonces__search-input');
    const searchForm = document.querySelector('.annonces__search-form');
    const searchIcon = document.querySelector('.annonces__search-icon');

    if (searchInput && searchIcon) {
        // Animation au focus
        searchInput.addEventListener('focus', function() {
            searchIcon.style.color = 'var(--color-primary-dark)';
        });

        searchInput.addEventListener('blur', function() {
            if (this.value === '') {
                searchIcon.style.color = 'var(--color-primary)';
            }
        });

        // Animation lors de la saisie
        searchInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                searchIcon.style.transform = 'scale(1.15) rotate(15deg)';
            } else {
                searchIcon.style.transform = 'scale(1)';
            }
        });
    }

    /**
     * ============================================
     * GESTION DE LA SOUMISSION DU FORMULAIRE
     * ============================================
     */
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const searchValue = searchInput.value.trim();

            if (searchValue === '') {
                // Animation de shake si vide
                searchForm.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    searchForm.style.animation = '';
                }, 500);
                searchInput.focus();
                return;
            }

            console.log('Recherche:', searchValue);
            // Ici, tu ajouteras la logique de recherche
        });
    }

    /**
     * ============================================
     * ANIMATION DES CARTES AU SCROLL (Optionnel)
     * ============================================
     */
    const cards = document.querySelectorAll('.annonces__card');

    if (cards.length > 0) {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const cardObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        cards.forEach((card, index) => {
            // Animation initiale
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `all 0.5s ease ${index * 0.1}s`;

            // Observer la carte
            cardObserver.observe(card);
        });
    }

    /**
     * ============================================
     * COMPTEUR D'ANNONCES (Mise à jour dynamique)
     * ============================================
     */
    function updateAnnonceCount() {
        const countElement = document.querySelector('.annonces__count-number');
        const gridCards = document.querySelectorAll('.annonces__grid .annonces__card');

        if (countElement) {
            countElement.textContent = gridCards.length;

            // Animation du compteur
            countElement.style.transform = 'scale(1.2)';
            setTimeout(() => {
                countElement.style.transform = 'scale(1)';
            }, 300);
        }
    }

    // Initialiser le compteur
    updateAnnonceCount();

});

/**
 * ============================================
 * ANIMATION SHAKE (pour validation formulaire)
 * ============================================
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);
