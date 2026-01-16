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

    /**
     * ============================================
     * CHARGEMENT ET AFFICHAGE DES ANNONCES
     * ============================================
     */
    const annonceGrid = document.querySelector('.annonces__grid');
    let allAnnonces = []; // Stocker toutes les annonces

    // Charger les annonces depuis l'API
    function loadAnnonces(filters = {}) {
        // Construire l'URL avec les filtres
        const params = new URLSearchParams();

        if (filters.ville) params.append('ville', filters.ville);
        if (filters.typeLogement) params.append('typeLogement', filters.typeLogement);
        if (filters.prixMin !== undefined) params.append('prixMin', filters.prixMin);
        if (filters.prixMax !== undefined) params.append('prixMax', filters.prixMax);

        const url = `api/get-annonces.php${params.toString() ? '?' + params.toString() : ''}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allAnnonces = data.data;
                    displayAnnonces(allAnnonces);
                } else {
                    console.error('Erreur:', data.error);
                    annonceGrid.innerHTML = '<p style="text-align: center; padding: 2rem; color: var(--color-text-light);">Erreur lors du chargement des annonces</p>';
                }
            })
            .catch(error => {
                console.error('Erreur fetch:', error);
                annonceGrid.innerHTML = '<p style="text-align: center; padding: 2rem; color: var(--color-text-light);">Erreur lors du chargement des annonces</p>';
            });
    }

    // Afficher les annonces dans la grille
    function displayAnnonces(annonces) {
        if (!annonceGrid) return;

        if (annonces.length === 0) {
            annonceGrid.innerHTML = '<p style="text-align: center; padding: 2rem; color: var(--color-text-light);">Aucune annonce trouvée</p>';
            updateAnnonceCount();
            return;
        }

        annonceGrid.innerHTML = '';

        annonces.forEach(annonce => {
            const card = createAnnonceCard(annonce);
            annonceGrid.appendChild(card);
        });

        updateAnnonceCount();
    }

    // Créer une carte d'annonce
    function createAnnonceCard(annonce) {
        const card = document.createElement('article');
        card.className = 'annonces__card';

        // Photo principale (première photo ou placeholder dynamique)
        const photoUrl = annonce.photos && annonce.photos.length > 0
            ? annonce.photos[0].cheminPhoto
            : `placeholder.php?type=${annonce.typeLogement}&seed=${annonce.id}&width=400&height=300`;

        // Type de logement formaté
        const typeLogement = formatTypeLogement(annonce.typeLogement);

        // Critères
        const criteres = annonce.criteres || {};
        const criteresHTML = [];
        if (criteres.meuble) criteresHTML.push('<span class="annonces__badge">Meublé</span>');
        if (criteres.eligibleAPL) criteresHTML.push('<span class="annonces__badge">APL</span>');
        if (criteres.parkingDisponible) criteresHTML.push('<span class="annonces__badge">Parking</span>');

        card.innerHTML = `
            <a href="annonce.php?id=${annonce.id}" class="annonces__card-link">
                <div class="annonces__card-image">
                    <img src="${photoUrl}" alt="${annonce.titre}">
                    <div class="annonces__card-type">${typeLogement}</div>
                </div>
                <div class="annonces__card-content">
                    <h3 class="annonces__card-title">${annonce.titre}</h3>
                    <p class="annonces__card-location">
                        <i class="fa-solid fa-location-dot"></i>
                        ${annonce.ville}
                    </p>
                    <div class="annonces__card-details">
                        <span class="annonces__card-surface">
                            <i class="fa-solid fa-maximize"></i>
                            ${annonce.superficie} m²
                        </span>
                    </div>
                    ${criteresHTML.length > 0 ? `<div class="annonces__card-badges">${criteresHTML.join('')}</div>` : ''}
                    <div class="annonces__card-footer">
                        <span class="annonces__card-price">${annonce.prixMensuel}€<small>/mois</small></span>
                        <span class="annonces__card-cta">Voir plus →</span>
                    </div>
                </div>
            </a>
        `;

        return card;
    }

    // Formater le type de logement
    function formatTypeLogement(type) {
        const types = {
            'studio': 'Studio',
            'colocation': 'Colocation',
            'residence_etudiante': 'Résidence étudiante',
            'chambre_habitant': 'Chambre chez l\'habitant'
        };
        return types[type] || type;
    }

    // Charger les annonces au chargement de la page
    loadAnnonces();

    /**
     * ============================================
     * FILTRAGE DES ANNONCES
     * ============================================
     */

    // Fonction pour appliquer les filtres
    function applyFilters() {
        const filters = {};

        // Budget
        const minBudget = parseInt(sliderMin?.value || 0);
        const maxBudget = parseInt(sliderMax?.value || 3000);

        if (minBudget > 0) filters.prixMin = minBudget;
        if (maxBudget < 3000) filters.prixMax = maxBudget;

        // Recherche par ville
        const searchValue = searchInput?.value.trim();
        if (searchValue) filters.ville = searchValue;

        // Type de logement (depuis les checkboxes)
        const typeLogement = [];
        if (document.getElementById('studio')?.checked) typeLogement.push('studio');
        if (document.getElementById('appartement')?.checked) typeLogement.push('colocation');
        if (document.getElementById('chambre')?.checked) typeLogement.push('chambre_habitant');

        if (typeLogement.length > 0) {
            // Pour l'instant, on ne filtre que par le premier type sélectionné
            // (l'API search_annonces ne supporte qu'un type à la fois)
            filters.typeLogement = typeLogement[0];
        }

        loadAnnonces(filters);
    }

    // Écouter les changements sur les filtres
    if (sliderMin && sliderMax) {
        sliderMin.addEventListener('change', applyFilters);
        sliderMax.addEventListener('change', applyFilters);
    }

    // Écouter les checkboxes
    const filterCheckboxes = document.querySelectorAll('.annonces__checkbox input[type="checkbox"]');
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });

    // Modifier le bouton réinitialiser pour recharger les annonces
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

            // Réinitialiser la recherche
            if (searchInput) {
                searchInput.value = '';
            }

            // Recharger toutes les annonces
            loadAnnonces();

            // Animation du bouton
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    }

    // Modifier la recherche pour filtrer les annonces
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }

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
