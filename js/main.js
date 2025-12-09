


/**
 * ============================================
 * ANIMATION DES COMPTEURS DE STATISTIQUES
 * ============================================
 * Ce script anime les chiffres dans la section "stats" de la page.
 * Les compteurs partent de 0 et s'incrémentent jusqu'à leur valeur cible
 * quand l'utilisateur scrolle jusqu'à cette section.
 */

// Attendre que le DOM soit complètement chargé avant d'exécuter le script
// Cela évite les erreurs si le script se lance avant que les éléments HTML existent
document.addEventListener("DOMContentLoaded", function() {

  // ====================
  // 1. SÉLECTION DES ÉLÉMENTS
  // ====================

  // Sélectionner tous les éléments avec la classe "stats__value" (les chiffres à animer)
  // Exemple : <span class="stats__value" data-count="3500">0</span>
  const counters = document.querySelectorAll(".stats__value");

  // Sélectionner le conteneur de la section stats (pour détecter quand elle est visible)
  const container = document.querySelector(".stats");

  // Vérifier que les éléments existent sur la page
  // Si on est sur une page sans section stats, on arrête le script
  if (!container || counters.length === 0) {
    console.log("Stats elements not found on this page");
    return; // Sortir de la fonction
  }

  // ====================
  // 2. VARIABLES GLOBALES
  // ====================

  // Variable pour s'assurer que l'animation ne se lance qu'une seule fois
  // false = pas encore lancé, true = déjà lancé
  let activated = false;

  // ====================
  // 3. FONCTION D'ANIMATION D'UN COMPTEUR
  // ====================

  /**
   * Anime un compteur individuel de 0 jusqu'à sa valeur cible
   * @param {HTMLElement} counter - L'élément HTML du compteur à animer
   */
  function animateCounter(counter) {
    // Récupérer la valeur cible depuis l'attribut data-count du HTML
    // Exemple : data-count="3500" donnera target = 3500
    const target = parseInt(counter.dataset.count);

    // Durée totale de l'animation en millisecondes (1000ms = 1 seconde)
    const duration = 3500;

    // Calculer l'incrément à chaque frame
    // requestAnimationFrame tourne à ~60fps, donc une frame toutes les ~16ms
    // Si duration = 1000ms, on aura environ 60 frames (1000/16)
    // increment = valeur_cible / nombre_de_frames
    // Exemple : pour atteindre 3500 en 60 frames, on incrémente de 3500/60 ≈ 58 par frame
    const increment = target / (duration / 16);

    // Variable qui stocke la valeur actuelle du compteur pendant l'animation
    let currentCount = 0;

    /**
     * Fonction récursive qui met à jour le compteur à chaque frame
     * Elle s'appelle elle-même jusqu'à atteindre la valeur cible
     */
    function updateCounter() {
      // Ajouter l'incrément à la valeur actuelle
      currentCount += increment;

      // Vérifier si on a atteint ou dépassé la cible
      if (currentCount < target) {
        // Pas encore à la cible : afficher la valeur arrondie à l'entier inférieur
        // Math.floor(58.7) = 58
        counter.innerText = Math.floor(currentCount);

        // Demander au navigateur d'appeler updateCounter à la prochaine frame
        // requestAnimationFrame assure une animation fluide à 60fps
        requestAnimationFrame(updateCounter);
      } else {
        // On a atteint ou dépassé la cible : afficher exactement la valeur cible
        // Cela garantit qu'on affiche 3500 et pas 3499 ou 3501
        counter.innerText = target;
        // L'animation s'arrête ici (on n'appelle plus requestAnimationFrame)
      }
    }

    // Lancer l'animation en appelant updateCounter pour la première fois
    updateCounter();
  }

  // ====================
  // 4. FONCTION DE DÉTECTION DE VISIBILITÉ
  // ====================

  /**
   * Vérifie si la section stats est visible dans la fenêtre du navigateur
   * @returns {boolean} true si visible, false sinon
   */
  function isInViewport() {
    // getBoundingClientRect() retourne la position de l'élément par rapport à la fenêtre
    // rect.top = distance du haut de l'élément au haut de la fenêtre
    // rect.bottom = distance du bas de l'élément au haut de la fenêtre
    const rect = container.getBoundingClientRect();

    // L'élément est visible si :
    // - Son haut (rect.top) est au-dessus ou dans la fenêtre (≤ hauteur de la fenêtre)
    // - Son bas (rect.bottom) est en dessous ou dans la fenêtre (≥ 0)
    return (
      rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
      rect.bottom >= 0
    );
  }

  // ====================
  // 5. GESTIONNAIRE DE SCROLL
  // ====================

  /**
   * Fonction appelée à chaque scroll pour vérifier si on doit lancer l'animation
   */
  function handleScroll() {
    // Vérifier deux conditions :
    // 1. La section stats est visible dans la fenêtre (isInViewport())
    // 2. L'animation n'a pas encore été lancée (!activated)
    if (isInViewport() && !activated) {
      // Parcourir tous les compteurs pour les animer
      counters.forEach((counter) => {
        // Remettre le compteur à 0 avant de lancer l'animation
        counter.innerText = 0;

        // Lancer l'animation pour ce compteur
        animateCounter(counter);
      });

      // Marquer l'animation comme lancée pour ne pas la relancer
      activated = true;
    }
  }

  // ====================
  // 6. ÉCOUTE DES ÉVÉNEMENTS
  // ====================

  // Ajouter un écouteur d'événement sur le scroll de la page
  // À chaque fois que l'utilisateur scrolle, handleScroll() sera appelée
  window.addEventListener("scroll", handleScroll);

  // Vérifier immédiatement au chargement de la page
  // Utile si la section stats est déjà visible sans avoir besoin de scroller
  handleScroll();

  // ====================

  // ====================
  // 8. ANIMATION TYPEWRITER
  // ====================

  /**
   * Effet machine à écrire qui affiche les mots un par un
   * Les mots changent de couleur à chaque cycle
   */
  const typewriterElement = document.getElementById("typewriter");

  // Vérifier que l'élément existe
  if (typewriterElement) {
    // Tableau des mots à afficher
    const words = [
      { text: "Simple", color: "#60a5fa" },      // Bleu clair
      { text: "Gratuit", color: "#2563eb" },     // Bleu principal
      { text: "Sécurisé", color: "#fbbf24" }     // Jaune accent
    ];

    let wordIndex = 0;        // Index du mot actuel
    let charIndex = 0;        // Index du caractère actuel
    let isDeleting = false;   // true = on efface, false = on écrit
    let typingSpeed = 150;    // Vitesse d'écriture en ms

    /**
     * Fonction principale qui gère l'animation typewriter
     */
    function typeWriter() {
      const currentWord = words[wordIndex];
      const currentText = currentWord.text;

      if (isDeleting) {
        // Mode effacement : retirer un caractère
        charIndex--;
        typewriterElement.textContent = currentText.substring(0, charIndex);
        typingSpeed = 75; // Effacer plus vite qu'écrire
      } else {
        // Mode écriture : ajouter un caractère
        charIndex++;
        typewriterElement.textContent = currentText.substring(0, charIndex);
        typingSpeed = 100; // Vitesse normale d'écriture
      }

      // Changer la couleur du texte selon le mot actuel
      typewriterElement.style.color = currentWord.color;

      // Vérifier si on a fini d'écrire le mot
      if (!isDeleting && charIndex === currentText.length) {
        // Pause après avoir écrit le mot complet
        typingSpeed = 1500; // Attendre 1,5 secondes
        isDeleting = true;  // Passer en mode effacement
      }
      // Vérifier si on a fini d'effacer le mot
      else if (isDeleting && charIndex === 0) {
        isDeleting = false;        // Repasser en mode écriture
        wordIndex = (wordIndex + 1) % words.length; // Passer au mot suivant (boucle infinie)
        typingSpeed = 500;         // Petite pause avant le prochain mot
      }

      // Appeler récursivement la fonction après le délai
      setTimeout(typeWriter, typingSpeed);
    }

    // Démarrer l'animation au chargement de la page
    typeWriter();
  }
});
