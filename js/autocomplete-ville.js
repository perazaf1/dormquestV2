// js/autocomplete-ville.js - Autocomplétion pour le champ "Ville de recherche"

class VilleAutocomplete {
    constructor(inputId, suggestionsContainerId) {
        this.input = document.getElementById(inputId);
        this.suggestionsContainer = document.getElementById(suggestionsContainerId);
        this.currentFocus = -1;
        this.timeout = null;
        
        if (!this.input || !this.suggestionsContainer) {
            console.error('Elements not found for autocomplete');
            return;
        }
        
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Événement de saisie avec délai pour éviter trop de requêtes
        this.input.addEventListener('input', () => {
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                this.handleInput();
            }, 200);
        });
        
        // Navigation avec les flèches et Entrée
        this.input.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
        
        // Focus sur l'input
        this.input.addEventListener('focus', () => {
            if (this.input.value.length > 0) {
                this.handleInput();
            }
        });
        
        // Fermer les suggestions quand on clique ailleurs
        document.addEventListener('click', (e) => {
            if (e.target !== this.input && !this.suggestionsContainer.contains(e.target)) {
                this.closeSuggestions();
            }
        });
    }
    
    handleInput() {
        const value = this.input.value.trim();
        
        if (value.length < 1) {
            this.closeSuggestions();
            return;
        }
        
        this.fetchSuggestions(value);
    }
    
    fetchSuggestions(query) {
        // Obtenir le chemin de base du site
        const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
        const url = baseUrl + '/api/get-villes.php?q=' + encodeURIComponent(query);
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error('API Error:', data.error);
                    this.closeSuggestions();
                } else if (Array.isArray(data)) {
                    this.showSuggestions(data, query);
                } else {
                    console.error('Invalid data format:', data);
                    this.closeSuggestions();
                }
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des suggestions:', error);
                this.closeSuggestions();
            });
    }
    
    showSuggestions(villes, query) {
        this.suggestionsContainer.innerHTML = '';
        this.currentFocus = -1;
        
        if (villes.length === 0) {
            this.closeSuggestions();
            return;
        }
        
        villes.forEach((ville, index) => {
            const item = document.createElement('div');
            item.classList.add('autocomplete-item');
            item.setAttribute('data-index', index);
            
            // Mettre en évidence la partie correspondant à la requête
            const highlightedVille = this.highlightMatch(ville, query);
            item.innerHTML = highlightedVille;
            
            item.addEventListener('click', () => {
                this.selectItem(ville);
            });
            
            item.addEventListener('mouseover', () => {
                this.setFocus(index);
            });
            
            this.suggestionsContainer.appendChild(item);
        });
        
        this.suggestionsContainer.style.display = 'block';
    }
    
    highlightMatch(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong style="color: var(--color-primary); font-weight: 600;">$1</strong>');
    }
    
    closeSuggestions() {
        this.suggestionsContainer.innerHTML = '';
        this.suggestionsContainer.style.display = 'none';
        this.currentFocus = -1;
    }
    
    handleKeydown(e) {
        const items = this.suggestionsContainer.querySelectorAll('.autocomplete-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (items.length > 0) {
                    this.setFocus(this.currentFocus + 1);
                }
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                if (items.length > 0) {
                    this.setFocus(this.currentFocus - 1);
                }
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.currentFocus >= 0 && items[this.currentFocus]) {
                    this.selectItem(items[this.currentFocus].textContent);
                }
                break;
                
            case 'Escape':
                this.closeSuggestions();
                break;
        }
    }
    
    setFocus(index) {
        const items = this.suggestionsContainer.querySelectorAll('.autocomplete-item');
        
        if (index < -1 || index >= items.length) {
            this.currentFocus = -1;
            items.forEach(item => item.classList.remove('autocomplete-focused'));
            return;
        }
        
        this.currentFocus = index;
        items.forEach(item => item.classList.remove('autocomplete-focused'));
        if (items[index]) {
            items[index].classList.add('autocomplete-focused');
            items[index].scrollIntoView({ block: 'nearest' });
        }
    }
    
    selectItem(ville) {
        // Extraire le texte sans les balises HTML
        const plainText = ville.replace(/<[^>]*>/g, '').trim();
        this.input.value = plainText;
        this.closeSuggestions();
        
        // Déclencher un événement change pour notifier les autres scripts
        const event = new Event('change', { bubbles: true });
        this.input.dispatchEvent(event);
    }
}

// Initialiser l'autocomplétion au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('ville_recherche');
    const container = document.getElementById('ville-suggestions');
    if (input && container) {
        new VilleAutocomplete('ville_recherche', 'ville-suggestions');
    }
});
