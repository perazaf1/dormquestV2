<?php
// mentions-legales.php - Mentions légales (DormQuest)
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions légales - DormQuest</title>
    <meta name="description" content="Mentions légales du site DormQuest, plateforme de mise en relation entre loueurs et étudiants.">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/styles.css">
    <style>
    .legal {
        padding: 4rem 0;
        background: var(--color-white);
        color: var(--color-text);
    }
    .legal__container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    .legal__title {
        font-size: 2rem;
        color: var(--color-primary);
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .legal__intro {
        color: var(--color-gray-dark);
        margin-bottom: 1.5rem;
    }
    .legal__section {
        margin-bottom: 2rem;
    }
    .legal__section-title {
        font-size: 1.25rem;
        color: var(--color-primary);
        margin-bottom: 0.5rem;
        font-weight: 700;
    }
    .legal__text {
        color: var(--color-gray-dark);
        line-height: 1.7;
    }
    .legal__actions {
        margin-top: 2rem;
        display:flex;
        gap: 0.5rem;
        flex-wrap:wrap;
    }
    .btn--ghost {
        background: transparent;
        color: var(--color-primary);
        border:1px solid rgba(0,0,0,0.06);
        padding: 0.6rem 1rem;
        border-radius: 8px;
        text-decoration:none;
        display:inline-block;
    }
    .legal__small {
        color: var(--color-gray);
        font-size: 0.95rem;
    }
    @media (max-width:768px) {
        .legal__container { padding: 0 1rem; }
        .legal__title { font-size: 1.6rem; }
    }
    </style>
</head>
<body>
    <header class="header">
        <div class="header__container">
            <a href="index.php" class="header__logo">
                <img src="images/logo-dormquest.png" alt="DormQuest Logo" class="header__logo-img">
                <span class="header__logo-text">DormQuest</span>
            </a>
            <nav class="header__nav">
                <a href="annonces.php" class="header__nav-link">Annonces</a>
                <a href="login.php" class="header__nav-link">Connexion</a>
                <a href="register.php" class="header__nav-link">Inscription</a>
                <a href="contact.php" class="header__nav-link">Contact</a>
            </nav>
        </div>
    </header>

    <main class="legal" id="mentions">
        <div class="legal__container">
            <h1 class="legal__title">Mentions légales</h1>
            <p class="legal__intro">Dernière mise à jour : <strong>09/11/2025</strong><br>
            Conformément à la loi n°2004-575 du 21 juin 2004 pour la confiance dans l’économie numérique (LCEN), il est précisé aux utilisateurs du site DormQuest l’identité des différents intervenants dans le cadre de sa réalisation et de son suivi.</p>

            <section id="editeur" class="legal__section">
                <h2 class="legal__section-title">1. Éditeur du site</h2>
                <div class="legal__text">
                    <strong>DormQuest</strong><br>
                    Société créée par <strong>Nyzer</strong><br>
                    Siège social : 10 Rue de Vanves, 92130 Issy-les-Moulineaux, France<br>
                    Email : <a href="mailto:contact@dormquest.fr">contact@dormquest.fr</a><br>
                    Directeur de la publication : <strong>Paul-Emile Razafindrakoto</strong><br>
                    Numéro SIRET (le cas échéant) : Pas de SIRET communiqué<br>
                    Forme juridique : Micro-Entreprise
                </div>
            </section>

            <section id="hebergeur" class="legal__section">
                <h2 class="legal__section-title">2. Hébergeur</h2>
                <div class="legal__text">
                    Le site <strong>DormQuest</strong> est hébergé par :<br>
                    <strong>OVH Cloud</strong><br>
                    Siège social : 2 Rue Kellermann, 59100 Roubaix, France<br>
                    Téléphone : 09 72 10 10 07<br>
                    Site web : <a href="https://www.ovhcloud.com" target="_blank" rel="noopener noreferrer">www.ovhcloud.com</a>
                </div>
            </section>

            <section id="realisation" class="legal__section">
                <h2 class="legal__section-title">3. Conception et développement</h2>
                <div class="legal__text">
                    Design, intégration et développement réalisés par <strong>Nyzer</strong> dans le cadre du projet étudiant <strong>DormQuest</strong>.
                </div>
            </section>

            <section id="conditions" class="legal__section">
                <h2 class="legal__section-title">4. Conditions d’utilisation</h2>
                <div class="legal__text">
                    L’utilisation du site <strong>DormQuest</strong> implique l’acceptation pleine et entière des <a href="CGU.php">Conditions Générales d’Utilisation (CGU)</a>.  
                    Les utilisateurs du site s’engagent à accéder au site en utilisant un matériel récent, ne contenant pas de virus et avec un navigateur de dernière génération mis à jour.
                </div>
            </section>

            <section id="donnees" class="legal__section">
                <h2 class="legal__section-title">5. Gestion des données personnelles</h2>
                <div class="legal__text">
                    Les informations recueillies sur le site <strong>DormQuest</strong> font l’objet d’un traitement destiné à assurer le bon fonctionnement du service.  
                    DormQuest s’engage à ne jamais vendre ni céder ces données à des tiers.  
                    Pour plus d’informations sur la collecte et le traitement de vos données personnelles, consultez la section <a href="CGU.php#donnees">« Données personnelles »</a> de nos CGU.  
                    Vous disposez des droits d’accès, de rectification, de suppression, d’opposition et de portabilité prévus par le Règlement Général sur la Protection des Données (RGPD).  
                    Pour toute demande : <a href="mailto:dpo@dormquest.fr">dpo@dormquest.fr</a>.
                </div>
            </section>

            <section id="cookies" class="legal__section">
                <h2 class="legal__section-title">6. Cookies</h2>
                <div class="legal__text">
                    Le site <strong>DormQuest</strong> utilise des cookies techniques nécessaires à son fonctionnement et des cookies de mesure d’audience anonymisés.  
                    Vous pouvez refuser le dépôt des cookies non essentiels via le bandeau de gestion des cookies lors de votre première visite ou depuis les paramètres de votre navigateur.
                </div>
            </section>

            <section id="propriete" class="legal__section">
                <h2 class="legal__section-title">7. Propriété intellectuelle</h2>
                <div class="legal__text">
                    Tous les éléments du site <strong>DormQuest</strong> (textes, images, graphismes, logo, structure, code source) sont protégés par le droit d’auteur et la propriété intellectuelle.  
                    Toute reproduction, représentation, modification, publication ou adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite sauf autorisation écrite préalable de DormQuest.
                </div>
            </section>

            <section id="liens" class="legal__section">
                <h2 class="legal__section-title">8. Liens hypertextes</h2>
                <div class="legal__text">
                    Le site <strong>DormQuest</strong> peut contenir des liens vers d’autres sites. DormQuest n’exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu.  
                    La création de liens vers le site DormQuest est autorisée uniquement dans le respect de la législation en vigueur et à condition de ne pas porter atteinte à son image.
                </div>
            </section>

            <section id="droit" class="legal__section">
                <h2 class="legal__section-title">9. Droit applicable</h2>
                <div class="legal__text">
                    Les présentes mentions légales sont régies par le droit français.  
                    En cas de litige, et après échec de toute tentative de résolution amiable, les tribunaux français seront seuls compétents.
                </div>
            </section>

            <div class="legal__actions">
                <a href="index.php" class="btn--ghost">Retour à l'accueil</a>
                <a href="CGU.php" class="btn--ghost">CGU</a>
                <a href="contact.php" class="btn--ghost">Nous contacter</a>
            </div>

            <p class="legal__small" style="margin-top:1.5rem;">
                Note : ces mentions légales sont fournies à titre informatif. Pour une conformité complète, prévoir une validation par un professionnel du droit.
            </p>
        </div>
    </main>

    <footer class="footer">
        <div class="footer__container">
            <div class="footer__section">
                <h4 class="footer__title">DormQuest</h4>
                <p class="footer__text">Trouvez le logement parfait pour vos études !</p>
            </div>
            <div class="footer__section">
                <h4 class="footer__title">Navigation</h4>
                <ul class="footer__list">
                    <li class="footer__item"><a href="annonces.php" class="footer__link">Annonces</a></li>
                    <li class="footer__item"><a href="CGU.php" class="footer__link">CGU</a></li>
                    <li class="footer__item"><a href="mentions-legales.php" class="footer__link">Mentions</a></li>
                </ul>
            </div>
            <div class="footer__section">
                <h4 class="footer__title">Contact</h4>
                <p class="footer__text"><a href="mailto:contact@dormquest.fr" class="footer__link">contact@dormquest.fr</a></p>
            </div>
            <div class="footer__section">
                <h4 class="footer__title">Powered by</h4>
                <img src="images/logo-nyzer.png" alt="Nyzer" class="footer__nyzer-logo">
            </div>
        </div>
        <div class="footer__bottom">
            <p class="footer__copyright">&copy; <?php echo date('Y'); ?> DormQuest. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
