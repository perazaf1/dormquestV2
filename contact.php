<?php
// contact.php - Page de contact DormQuest
session_start();
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est connect�
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/contact.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero__contact">
        <div class="hero__contact__container">
            <h1 class="hero__contact__container hero__contact__container--title">
                Contactez-nous en cas de problème
            </h1>
            <div class="hero__contact--form">
                <form id="contact" action="" method="post">
                    <h3></h3>
                    <h3>Contactez-nous aujourd'hui, obtenez une réponse sous 48 heures !</h3>
                    <fieldset>
                        <input placeholder="Votre nom" type="text" tabindex="1" required autofocus>
                    </fieldset>
                    <fieldset>
                        <input placeholder="Votre adresse mail" type="email" tabindex="2" required>
                    </fieldset>
                    <fieldset>
                        <input placeholder="Votre numéro de téléphone" type="tel" tabindex="3" required>
                    </fieldset>
                    <fieldset>
                        <textarea placeholder="Ecrivez votre message ici..." tabindex="4" required></textarea>
                    </fieldset>
                    <fieldset>
                        <button name="submit" type="submit" id="contact-submit"
                            data-submit="...Sending">Envoyer</button>
                    </fieldset>
                </form>
            </div>

        </div>
        <div class="hero__contact__img">
            <img src="img/contact-img.png" alt="Equipe répondant à des mails">
        </div>


    </section>

    <section class="coordonnees">
        <div class="coordonnees__container">
            <div class="coordonnees__title">
                <h2>Nous contacter</h2>
            </div>
            <div class="coordonnees__description">
                <h3 class="coordonnees__description--title">
                    Nos horaires - SAV téléphonique & présentiel
                </h3>
                <li class="coordonnees__description--list">
                    <ul>Lundi : 9h00 - 18h</ul>
                    <ul>Mardi : 9h00 - 18h</ul>
                    <ul>Mercredi : 9h00 - 18h</ul>
                    <ul>Jeudi: 9h00 - 18h</ul>
                    <ul>Vendredi : 9h00 - 18h</ul>
                    <ul>Samedi : 10h00 - 14h</ul>
                </li>
            </div>
                <div class="coordonnees__info">
                    <h3 class="coordonnees__info--title">
                        Nos coordonnées
                    </h3>
                    <div class="coordonnees__info--items">
                        <div class="coordonnees__info--item">
                            <div class="coordonnees__info--icon"><i class="fa-solid fa-phone-volume"></i></div>
                            <div class="coordonnees__info--content">
                                <span class="coordonnees__info--label">Téléphone</span>
                                <a href="tel:+33123456789" class="coordonnees__info--value">+33 1 23 45 67 89</a>
                            </div>
                        </div>
                        <div class="coordonnees__info--item">
                            <div class="coordonnees__info--icon"><i class="fa-regular fa-message"></i></div>
                            <div class="coordonnees__info--content">
                                <span class="coordonnees__info--label">Email</span>
                                <a href="mailto:contact@dormquest.com" class="coordonnees__info--value">contact@dormquest.com</a>
                            </div>
                        </div>
                        <div class="coordonnees__info--item">
                            <div class="coordonnees__info--icon"><i class="fa-solid fa-location-dot"></i></div>
                            <div class="coordonnees__info--content">
                                <span class="coordonnees__info--label">Adresse</span>
                                <span class="coordonnees__info--value">10 Rue de Vanves<br>92130 Issy-les-Moulineaux, France</span>
                            </div>
                        </div>
                    </div>
                </div>
                <h2 class="cooordonnees__map--title">
                    Nous trouver
                </h2>
            <div class="coordonees__map">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5253.5316899423!2d2.2772733121775306!3d48.824528971207954!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e670797ea4730d%3A0xe0d3eb2ad501cb27!2sInstitut%20sup%C3%A9rieur%20d&#39;%C3%A9lectronique%20de%20Paris%20(ISEP)!5e0!3m2!1sfr!2sfr!4v1765401086560!5m2!1sfr!2sfr"
                    width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="https://kit.fontawesome.com/794b85b760.js" crossorigin="anonymous"></script>
</body>

</html>