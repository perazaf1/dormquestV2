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
            </div>
            <div class="hero__img">
                <img src="img/hero-illustration2.jpg" alt="Etudiante cherchant un logement">
            </div>
        </div>
    </section>


    <!-- Section Statistiques -->
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
                    <span class="stats__value" data-count="2200">0</span>
                    <span class="stats__suffix">+</span>
                </div>
                <h3 class="stats__label">Loueurs</h3>
            </div>
            <div class="stats__item">
                <div class="stats__number">
                    <span class="stats__value" data-count="1500">0</span>
                    <span class="stats__suffix">+</span>
                </div>
                <h3 class="stats__label">Logements disponibles</h3>
            </div>
            <div class="stats__item">
                <div class="stats__number">
                    <span class="stats__value" data-count="99">0</span>
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
