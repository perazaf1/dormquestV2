<?php
// CGU.php - Conditions Générales d'Utilisation (DormQuest)
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CGU - DormQuest</title>
    <meta name="description" content="Conditions générales d'utilisation (CGU) de DormQuest, plateforme de mise en relation loueurs / étudiants.">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/styles.css">
    <style>
    /* Styles locaux pour la page CGU - respecte la charte BEM / DormQuest */
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
    .legal__toc {
        background: var(--color-gray-light);
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        border-left: 4px solid var(--color-secondary);
    }
    .legal__toc ul { list-style: none; padding-left: 0; }
    .legal__toc a { color: var(--color-primary); font-weight:600; }
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
    .legal__small {
        color: var(--color-gray);
        font-size: 0.95rem;
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
    /* Responsive */
    @media (max-width:768px) {
        .legal__container { padding: 0 1rem; }
        .legal__title { font-size: 1.6rem; }
    }
    </style>
</head>
<body>
    <!-- Header (identique à index) -->
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

    <main class="legal" id="legal">
        <div class="legal__container">
            <h1 class="legal__title">Conditions Générales d'Utilisation (CGU)</h1>
            <p class="legal__intro">Dernière mise à jour : <strong>09/11/2025</strong><br>
            Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation du site <strong>DormQuest</strong> (ci-après « le Service »), plateforme de mise en relation entre loueurs et étudiants. En utilisant le Service, vous acceptez les présentes CGU.</p>

            <aside class="legal__toc" aria-label="Table des matières">
                <strong>Sommaire</strong>
                <ul>
                    <li><a href="#objet">1. Objet</a></li>
                    <li><a href="#inscription">2. Inscription et comptes</a></li>
                    <li><a href="#annonces">3. Publication d'annonces</a></li>
                    <li><a href="#mise-en-relation">4. Mise en relation</a></li>
                    <li><a href="#responsabilites">5. Responsabilités</a></li>
                    <li><a href="#donnees">6. Données personnelles</a></li>
                    <li><a href="#propriete">7. Propriété intellectuelle</a></li>
                    <li><a href="#modifications">8. Modification des CGU</a></li>
                    <li><a href="#resiliation">9. Résiliation</a></li>
                    <li><a href="#droit">10. Droit applicable</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </aside>

            <section id="objet" class="legal__section">
                <h2 class="legal__section-title">1. Objet</h2>
                <div class="legal__text">
                    Les présentes CGU ont pour objet de définir les conditions dans lesquelles DormQuest met à disposition une plateforme en ligne permettant la mise en relation entre des personnes proposant des logements destinés aux étudiants (« loueurs ») et des étudiants recherchant un logement (« étudiants »). DormQuest n'est qu'un intermédiaire technique facilitant la mise en relation.
                </div>
            </section>

            <section id="inscription" class="legal__section">
                <h2 class="legal__section-title">2. Inscription et comptes</h2>
                <div class="legal__text">
                    L'utilisation de certaines fonctionnalités (création d'annonces, candidatures, messagerie) nécessite la création d'un compte. L'utilisateur garantit l'exactitude et l'actualité des informations transmises. Chaque utilisateur est responsable de la confidentialité de ses identifiants. Toute activité réalisée à partir d'un compte est réputée réalisée par le titulaire du compte.
                </div>
            </section>

            <section id="annonces" class="legal__section">
                <h2 class="legal__section-title">3. Publication d'annonces</h2>
                <div class="legal__text">
                    Les loueurs peuvent créer et publier des annonces. Chaque annonce doit être conforme à la législation applicable (notamment en matière de non-discrimination et d'information du locataire). Le loueur est entièrement responsable des éléments publiés (descriptions, prix, photos). DormQuest se réserve le droit de refuser, modifier ou supprimer toute annonce non conforme, trompeuse ou contraire à l'ordre public.
                </div>
            </section>

            <section id="mise-en-relation" class="legal__section">
                <h2 class="legal__section-title">4. Mise en relation</h2>
                <div class="legal__text">
                    Le Service permet aux étudiants de contacter des loueurs et d'envoyer des candidatures. DormQuest n'intervient pas dans les négociations, ne traite pas les paiements et ne garantit pas la réussite d'une mise en location. Les modalités concrètes (visite, contrat, dépôt de garantie, modalités de paiement) sont à définir entre loueur et locataire.
                </div>
            </section>

            <section id="responsabilites" class="legal__section">
                <h2 class="legal__section-title">5. Responsabilités</h2>
                <div class="legal__text">
                    DormQuest fournit une plateforme technique. DormQuest décline toute responsabilité quant à la véracité, l'exactitude ou l'exhaustivité des informations publiées par les utilisateurs, ainsi que concernant les éventuels litiges entre utilisateurs. Les utilisateurs s'engagent à indemniser DormQuest en cas de réclamation liée à leur utilisation du Service.
                </div>
            </section>

            <section id="donnees" class="legal__section">
                <h2 class="legal__section-title">6. Données personnelles</h2>
                <div class="legal__text">
                    Les traitements de données personnelles sont réalisés conformément au RGPD et à la loi Informatique et Libertés. Les données collectées (ex. nom, email, téléphone, informations d'annonces) servent au fonctionnement du Service. Elles ne sont pas vendues à des tiers. Conformément à la réglementation, l'utilisateur dispose d'un droit d'accès, de rectification, d'effacement, de limitation et d'opposition, ainsi que du droit à la portabilité. Pour exercer ces droits, contacter : <strong>dpo@dormquest.fr</strong>.
                    <br><br>
                    Durée de conservation : les données d'un compte sont conservées pendant la durée d'inscription; en cas de suppression de compte, les données sont conservées pendant une durée maximale de 12 mois sauf obligation légale contraire.
                </div>
            </section>

            <section id="propriete" class="legal__section">
                <h2 class="legal__section-title">7. Propriété intellectuelle</h2>
                <div class="legal__text">
                    Le contenu éditorial et technique du site (design, logo, code, contenus rédactionnels) est la propriété de DormQuest ou de ses partenaires et est protégé par le droit d'auteur. Les utilisateurs conservent les droits sur les contenus qu'ils publient, mais concèdent à DormQuest une licence non-exclusive d'affichage et de reproduction dans le cadre de la diffusion des annonces sur le Service.
                </div>
            </section>

            <section id="modifications" class="legal__section">
                <h2 class="legal__section-title">8. Modification des CGU</h2>
                <div class="legal__text">
                    DormQuest peut modifier les présentes CGU à tout moment. Les modifications seront publiées sur le site avec une date de mise à jour. L'utilisation continue du Service après publication vaut acceptation des nouvelles conditions.
                </div>
            </section>

            <section id="resiliation" class="legal__section">
                <h2 class="legal__section-title">9. Résiliation</h2>
                <div class="legal__text">
                    L'utilisateur peut supprimer son compte à tout moment depuis les réglages. DormQuest se réserve le droit de suspendre ou supprimer un compte en cas de manquement grave aux présentes CGU ou d'activité frauduleuse.
                </div>
            </section>

            <section id="droit" class="legal__section">
                <h2 class="legal__section-title">10. Droit applicable et litiges</h2>
                <div class="legal__text">
                    Les présentes CGU sont soumises au droit français. En cas de litige, les parties s'efforceront de trouver une solution amiable. À défaut, les tribunaux compétents en France seront seuls compétents.
                </div>
            </section>

            <section id="contact" class="legal__section">
                <h2 class="legal__section-title">Contact</h2>
                <div class="legal__text">
                    Éditeur : <strong>DormQuest, société créée par Nyzer</strong><br>
                    Adresse : <strong>10 Rue de Vanves, 92130 Issy-les-Moulineaux</strong><br>
                    Email : <a href="mailto:contact@dormquest.fr">contact@dormquest.fr</a><br>
                    Directeur de la publication : <strong>Razafindrakoto, Paul-Emile</strong><br>
                    Hébergeur : <strong>OVH Cloud</strong>
                </div>
            </section>

            <div class="legal__actions">
                <a href="index.php" class="btn--ghost">Retour à l'accueil</a>
                <a href="mentions-legales.php" class="btn--ghost">Mentions légales</a>
                <a href="contact.php" class="btn--ghost">Nous contacter</a>
            </div>

            <p class="legal__small" style="margin-top:1.5rem;">
                Note : ce document est fourni à titre informatif. Pour une mise en conformité complète (RGPD, mentions CNIL si nécessaire), prévoyez une revue par un conseiller juridique.
            </p>
        </div>
    </main>

    <!-- Footer -->
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
                    <li class="footer__item"><a href="mentions-legales.php" class="footer__link">Mentions légales</a></li>
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

    <script>
    // Smooth scroll pour la table des matières
    document.querySelectorAll('.legal__toc a').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                window.scrollTo({ top: target.offsetTop - 80, behavior: 'smooth' });
            }
        });
    });
    </script>
</body>
</html>
