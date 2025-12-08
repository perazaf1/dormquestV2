<?php
// index.php - Landing Page DormQuest
session_start();

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DormQuest - Trouvez le logement parfait pour vos études ! Plateforme de mise en relation entre étudiants et loueurs.">
    <title>DormQuest - Trouvez le logement parfait pour vos études !</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/header.php'; ?>

<!-- Section Hero - section de présentation de l'outil -->
    <section class="hero">
        <div class="hero__container">
            <h1 class="hero__title">
                Trouvez le logement parfait pour vos études !
            </h1>
            <P class="hero__subtitle">
                DormQuest relie les étudiants aux meilleurs logements.
                Simple, gratuit et sécurisé.
            </P>
            <div class="hero__button">
                <a href="register.php" class="hero__btn hero__btn--primary">
                    Je cherche un logement
                </a>
                <a href="register.php" class="hero__btn hero__btn--secondary">
                    Je propose un logement
                </a>
                <a href="#avantages" class="hero__btn hero__btn--cta">
                    Avantages
                </a>
                <a href="#faq" class="hero__btn hero__btn--faq">
                    FAQ
                </a>
            </div>
            <div class="hero__img">
                <img src="img/hero-illustration2.jpg" alt="Etudiante cherchant un logement">
            </div>
        </div>
    </section>

    <section class="avantages" id="avantages">
        <div class="avantages__container">
            <h1 class="avantages__title">
                Pour les étudiants
            </h1>
            <div class="avantages__card" data-color="blue">
                <div class="avantages__card-icon">🔍</div>
                <h3 class="avantages__card-title">
                    Recherche simplifiée
                </h3>
                <p class="avantages__card-description">
                    Trouvez rapidement des logements adaptés à vos critères et votre budget.
                </p>
            </div>
            <div class="avantages__card" data-color="green">
                <div class="avantages__card-icon">💬</div>
                <h3 class="avantages__card-title">
                    Contact direct
                </h3>
                <p class="avantages__card-description">
                    Candidatez en un clic et communiquez directement avec les loueurs.
                </p>
            </div>
            <div class="avantages__card" data-color="purple">
                <div class="avantages__card-icon">⭐</div>
                <h3 class="avantages__card-title">
                    Liste de favoris
                </h3>
                <p class="avantages__card-description">
                    Sauvegardez vos annonces préférées et comparez-les facilement.
                </p>
            </div>
            <a href="https://www.dossierfacile.logement.gouv.fr/" target="_blank" rel="noopener noreferrer" class="avantages__card avantages__card--link" data-color="orange">
                <div class="avantages__card-icon">📋</div>
                <h3 class="avantages__card-title">
                    Dossier facile
                </h3>
                <p class="avantages__card-description">
                    Créez facilement votre dossier grâce à des formulaires du gouvernement.
                </p>
            </a>
            <a href="https://wwwd.caf.fr/wps/portal/caffr/aidesetdemarches/mesdemarches/faireunesimulation/lelogement#/preparation" target="_blank" rel="noopener noreferrer" class="avantages__card avantages__card--link" data-color="pink">
                <div class="avantages__card-icon">💰</div>
                <h3 class="avantages__card-title">
                    Calculateur d'APL
                </h3>
                <p class="avantages__card-description">
                    Calculez rapidement vos aides au logement avec l'outil officiel de la CAF.
                </p>
            </a>
            <a href="https://www.visale.fr/" target="_blank" rel="noopener noreferrer" class="avantages__card avantages__card--link" data-color="teal">
                <div class="avantages__card-icon">🤝</div>
                <h3 class="avantages__card-title">
                    Obtenez un garant
                </h3>
                <p class="avantages__card-description">
                    Testez votre éligibilité d'un garant locatif via le dispositif Visale.
                </p>
            </a>

            <h1 class="avantages__title">
                Pour les loueurs
            </h1>
            <div class="avantages__card" data-color="indigo">
                <div class="avantages__card-icon">⚙️</div>
                <h3 class="avantages__card-title">
                    Gestion facile
                </h3>
                <p class="avantages__card-description">
                    Créez, gérez et modifiez vos annonces en quelques clics.
                </p>
            </div>
            <div class="avantages__card" data-color="emerald">
                <div class="avantages__card-icon">🔒</div>
                <h3 class="avantages__card-title">
                    Sécurisé
                </h3>
                <p class="avantages__card-description">
                    Profils sécurisés et vérifiés
                </p>
            </div>
            <div class="avantages__card" data-color="amber">
                <div class="avantages__card-icon">📢</div>
                <h3 class="avantages__card-title">
                    Large audience
                </h3>
                <p class="avantages__card-description">
                    Touchez des milliers d'étudiants en recherche de logement.
                </p>
            </div>


        </div>
    </section>


    <section class="faq" id="faq">
    <div class="faq__container">
        <h1 class="faq__title">
            FAQ
        </h1>
    </div>
    </section>




    <!-- Section Statistiques qui s'incrémentent -->
    <section class="stats">
        <div class="stats__container">
            <div class="stats__item">
                <div class="stats__number">
                    <span class="stats__value" data-count="3500">0</span>
                    <span class="stats__suffix">+</span>
                </div>
                <h3 class="stats__label">Étudiants inscrits</h3>
            </div>
            <div class="stats__item">
                <div class="stats__number">
                    <span class="stats__value" data-count="800">0</span>
                    <span class="stats__suffix">+</span>
                </div>
                <h3 class="stats__label">Loueurs</h3>
            </div>
            <div class="stats__item">
                <div class="stats__number">
                    <span class="stats__value" data-count="1000">0</span>
                    <span class="stats__suffix">+</span>
                </div>
                <h3 class="stats__label">Logements disponibles</h3>
            </div>
            <div class="stats__item">
                <div class="stats__number">
                    <span class="stats__value" data-count="96">0</span>
                    <span class="stats__suffix">%</span>
                </div>
                <h3 class="stats__label">Taux de satisfaction</h3>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>
