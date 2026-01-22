<?php
// annonces.php - Liste des annonces.
session_start();
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';
// V�rifier si l'utilisateur est connect�
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annonces - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/annonces.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Section principale avec layout sidebar + contenu -->
    <section class="annonces">
        <div class="annonces__container">

            <!-- Sidebar des filtres -->
            <aside class="annonces__sidebar">
                <div class="annonces__filters">
                    <h2 class="annonces__filters-title">Filtres</h2>

                    <!-- Groupe: Type de logement -->
                    <div class="annonces__filter-group">
                        <h3 class="annonces__filter-label">Type de Logement</h3>
                        <div class="annonces__filter-options">
                            <label class="annonces__checkbox">
                                <input type="checkbox" name="appartement" id="appartement">
                                <span class="annonces__checkbox-text">Appartement</span>
                            </label>
                            <label class="annonces__checkbox">
                                <input type="checkbox" name="studio" id="studio">
                                <span class="annonces__checkbox-text">Studio</span>
                            </label>
                            <label class="annonces__checkbox">
                                <input type="checkbox" name="chambre" id="chambre">
                                <span class="annonces__checkbox-text">Chambre</span>
                            </label>
                        </div>
                    </div>

                    <!-- Groupe: Budget mensuel -->
                    <div class="annonces__filter-group">
                        <h3 class="annonces__filter-label">Budget mensuel</h3>
                        <div class="annonces__slider-container">
                            <div class="annonces__slider-values">
                                <span class="annonces__slider-value" id="budgetMin">0€</span>
                                <span class="annonces__slider-value" id="budgetMax">3000€</span>
                            </div>
                            <div class="annonces__slider-wrapper">
                                <input
                                    type="range"
                                    class="annonces__slider annonces__slider--min"
                                    id="sliderMin"
                                    min="0"
                                    max="3000"
                                    value="0"
                                    step="50"
                                >
                                <input
                                    type="range"
                                    class="annonces__slider annonces__slider--max"
                                    id="sliderMax"
                                    min="0"
                                    max="3000"
                                    value="3000"
                                    step="50"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Groupe: Critères -->
                    <div class="annonces__filter-group">
                        <h3 class="annonces__filter-label">Critères</h3>
                        <div class="annonces__filter-options">
                            <label class="annonces__checkbox">
                                <input type="checkbox" name="meuble" id="meuble">
                                <span class="annonces__checkbox-text">Meublé</span>
                            </label>
                            <label class="annonces__checkbox">
                                <input type="checkbox" name="eligibleApl" id="eligibleApl">
                                <span class="annonces__checkbox-text">Éligible aux APL</span>
                            </label>
                            <label class="annonces__checkbox">
                                <input type="checkbox" name="parking" id="parking">
                                <span class="annonces__checkbox-text">Parking</span>
                            </label>
                            <label class="annonces__checkbox">
                                <input type="checkbox" name="accesPMR" id="accesPMR">
                                <span class="annonces__checkbox-text">Accès PMR</span>
                            </label>
                        </div>
                    </div>

                    <!-- Bouton réinitialiser -->
                    <button class="annonces__reset-btn" type="button">
                        Réinitialiser
                    </button>
                </div>
            </aside>

            <!-- Contenu principal -->
            <main class="annonces__main">

                <!-- Barre de recherche -->
                <div class="annonces__search">
                    <h1 class="annonces__search-title">Trouvez votre logement idéal</h1>
                    <div class="annonces__search-box">
                        <form class="annonces__search-form">
                            <div class="annonces__search-icon">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <input
                                type="text"
                                class="annonces__search-input"
                                placeholder="Rechercher par ville, quartier..."
                                name="search"
                            >
                            <button type="submit" class="annonces__search-btn">
                                Rechercher
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Grille des annonces -->
                <div class="annonces__content">
                    <div class="annonces__header">
                        <h2 class="annonces__content-title">Toutes les annonces</h2>
                        <p class="annonces__count">
                            <span class="annonces__count-number">0</span> annonce(s) trouvée(s)
                        </p>
                    </div>

                    <!-- Grille qui contiendra les annonces -->
                    <div class="annonces__grid">
                        <!-- Les annonces seront ajoutées ici dynamiquement -->

                        <!-- Exemple de carte d'annonce (à supprimer plus tard) -->
                        

                    </div>
                </div>

            </main>

        </div>
    </section>


    <?php include 'includes/footer.php'; ?>
    <script src="https://kit.fontawesome.com/794b85b760.js" crossorigin="anonymous"></script>
    <script src="js/annonces.js"></script>
</body>

</html>
