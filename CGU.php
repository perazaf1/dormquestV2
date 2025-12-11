<?php
// CGU.php - Conditions G�n�rales d'Utilisation
// CGU.php - Condi tions G�n�rales d'Utilisation
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
    <title>CGU - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
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
    .legal__section-subtitle {
        font-size: 1.1rem;
        color: var(--color-primary);
        margin: 1rem 0 0.5rem 0;
        font-weight: 600;
    }
    .legal__text {
        color: var(--color-gray-dark);
        line-height: 1.7;
        margin-bottom: 1rem;
    }
    .legal__text ul {
        margin: 1rem 0;
        padding-left: 2rem;
    }
    .legal__text li {
        margin-bottom: 0.5rem;
        line-height: 1.7;
    }
    .legal__text a {
        color: var(--color-primary);
        text-decoration: none;
        border-bottom: 1px solid rgba(102, 126, 234, 0.3);
        transition: border-color 0.2s;
    }
    .legal__text a:hover {
        border-bottom-color: var(--color-primary);
    }
    .contact-info {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid var(--color-primary);
    }
    .contact-info p {
        margin: 0.5rem 0;
        color: var(--color-gray-dark);
    }
    .cgu-acceptance {
        background: #fff9e6;
        border: 2px solid #ffd700;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 2rem;
    }
    .cgu-acceptance p {
        margin: 0;
        color: var(--color-text);
        font-weight: 500;
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
        .legal__section-title { font-size: 1.1rem; }
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="legal" id="cgu">
        <div class="legal__container">
            <h1 class="legal__title">Conditions Générales d'Utilisation</h1>
            <p class="legal__intro">Dernière mise à jour : <strong>10/12/2025</strong><br>
            L'utilisation de la plateforme implique l'acceptation pleine et entière des présentes CGU.</p>

            <section id="objet" class="legal__section">
                <h2 class="legal__section-title">1. Objet</h2>
                <div class="legal__text">
                    <p>Les présentes Conditions Générales d'Utilisation (CGU) ont pour objet de définir les modalités
                    et conditions dans lesquelles les utilisateurs peuvent accéder et utiliser la plateforme
                    <strong>DormQuest</strong>, accessible à l'adresse <strong>dormquest.com</strong>.</p>
                    <p>L'utilisation de la plateforme implique l'acceptation pleine et entière des présentes CGU.</p>
                </div>
            </section>

            <section id="definitions" class="legal__section">
                <h2 class="legal__section-title">2. Définitions</h2>
                <div class="legal__text">
                <ul>
                    <li><strong>Plateforme :</strong> désigne le site web DormQuest et l'ensemble de ses fonctionnalités</li>
                    <li><strong>Utilisateur :</strong> toute personne accédant à la plateforme</li>
                    <li><strong>Étudiant :</strong> utilisateur recherchant un logement</li>
                    <li><strong>Propriétaire :</strong> utilisateur proposant un logement à la location</li>
                    <li><strong>Compte :</strong> espace personnel créé sur la plateforme</li>
                </ul>
                </div>
            </section>

            <section id="acces" class="legal__section">
                <h2 class="legal__section-title">3. Accès à la plateforme</h2>
                <div class="legal__text">
                    <p>La plateforme est accessible gratuitement à tout utilisateur disposant d'un accès à Internet.
                    Tous les coûts liés à l'accès (matériel, logiciels, connexion) sont à la charge de l'utilisateur.</p>
                    <p>DormQuest se réserve le droit de suspendre, modifier ou interrompre l'accès à la plateforme
                    à tout moment, sans préavis ni indemnité, notamment pour des raisons de maintenance.</p>
                </div>
            </section>

            <section id="inscription" class="legal__section">
                <h2 class="legal__section-title">4. Inscription et compte utilisateur</h2>
                <div class="legal__text">
                <h3 class="legal__section-subtitle">4.1 Création de compte</h3>
                <p>L'accès à certaines fonctionnalités de DormQuest nécessite la création d'un compte utilisateur
                en tant qu'étudiant ou propriétaire.</p>

                <h3 class="legal__section-subtitle">4.2 Obligations de l'utilisateur</h3>
                <p>Lors de l'inscription, l'utilisateur s'engage à :</p>
                <ul>
                    <li>Fournir des informations exactes, complètes et à jour</li>
                    <li>Avoir au moins 18 ans ou disposer de l'autorisation parentale</li>
                    <li>Maintenir la confidentialité de ses identifiants de connexion</li>
                    <li>Informer immédiatement DormQuest de toute utilisation non autorisée de son compte</li>
                    <li>Être responsable de toutes les activités effectuées depuis son compte</li>
                </ul>

                <h3 class="legal__section-subtitle">4.3 Suspension et suppression de compte</h3>
                <p>DormQuest se réserve le droit de suspendre ou supprimer tout compte en cas de
                violation des présentes CGU, sans préavis ni indemnité.</p>
                </div>
            </section>

            <section id="utilisation" class="legal__section">
                <h2 class="legal__section-title">5. Utilisation de la plateforme</h2>
                <div class="legal__text">
                <h3 class="legal__section-subtitle">5.1 Règles générales</h3>
                <p>L'utilisateur s'engage à utiliser la plateforme de manière loyale et à ne pas :</p>
                <ul>
                    <li>Porter atteinte aux droits de tiers ou à l'ordre public</li>
                    <li>Publier du contenu illégal, diffamatoire, offensant ou frauduleux</li>
                    <li>Tenter de nuire au bon fonctionnement de la plateforme</li>
                    <li>Utiliser la plateforme à des fins commerciales non autorisées</li>
                    <li>Collecter des données personnelles d'autres utilisateurs</li>
                    <li>Créer plusieurs comptes pour la même personne</li>
                    <li>Usurper l'identité d'une autre personne</li>
                </ul>

                <h3 class="legal__section-subtitle">5.2 Annonces de logements</h3>
                <p>Les propriétaires s'engagent à :</p>
                <ul>
                    <li>Publier des annonces véridiques et conformes à la réalité</li>
                    <li>Fournir des photos et descriptions fidèles du logement</li>
                    <li>Respecter la législation en vigueur concernant la location</li>
                    <li>Mettre à jour ou supprimer les annonces obsolètes</li>
                    <li>Ne pas discriminer les candidats locataires</li>
                </ul>

                <h3 class="legal__section-subtitle">5.3 Recherche de logement</h3>
                <p>Les étudiants s'engagent à :</p>
                <ul>
                    <li>Fournir des informations exactes lors des demandes</li>
                    <li>Respecter les propriétaires et les biens visités</li>
                    <li>Honorer les rendez-vous pris</li>
                </ul>
                </div>
            </section>

            <section id="contenu" class="legal__section">
                <h2 class="legal__section-title">6. Contenu utilisateur</h2>
                <div class="legal__text">
                    <p>Les utilisateurs peuvent publier du contenu sur DormQuest (annonces, commentaires, photos, etc.).</p>
                    <p>L'utilisateur garantit qu'il dispose de tous les droits nécessaires sur le contenu qu'il publie
                    et que ce contenu ne viole aucun droit de tiers (propriété intellectuelle, droit à l'image, etc.).</p>
                    <p>En publiant du contenu, l'utilisateur accorde à DormQuest une licence non exclusive d'utilisation,
                    de reproduction et de représentation de ce contenu dans le cadre du fonctionnement de la plateforme.</p>
                    <p>DormQuest se réserve le droit de modérer, supprimer ou refuser tout contenu qui ne respecterait pas
                    les présentes CGU ou la législation en vigueur, sans préavis.</p>
                </div>
            </section>

            <section id="role" class="legal__section">
                <h2 class="legal__section-title">7. Rôle de DormQuest</h2>
                <div class="legal__text">
                    <p>DormQuest est une plateforme de mise en relation entre étudiants et propriétaires.
                    Elle n'est pas partie aux contrats de location conclus entre les utilisateurs.</p>
                    <p>DormQuest ne garantit pas :</p>
                <ul>
                    <li>La véracité des informations publiées par les utilisateurs</li>
                    <li>La disponibilité effective des logements</li>
                    <li>La solvabilité des étudiants</li>
                    <li>La conclusion effective d'un contrat de location</li>
                </ul>
                <p>Il appartient aux utilisateurs de vérifier les informations et de prendre toutes les précautions
                nécessaires avant de conclure une transaction.</p>
                </div>
            </section>

            <section id="propriete" class="legal__section">
                <h2 class="legal__section-title">8. Propriété intellectuelle</h2>
                <div class="legal__text">
                    <p>Tous les éléments de la plateforme (textes, images, logos, structure, charte graphique,
                    code source, etc.) sont protégés par le droit d'auteur et appartiennent à DormQuest ou à ses partenaires.</p>
                    <p>Toute reproduction, distribution, modification ou exploitation sans autorisation expresse est interdite
                    et peut faire l'objet de poursuites judiciaires.</p>
                    <p>Les marques, logos et signes distinctifs figurant sur la plateforme sont des marques déposées.
                    Toute reproduction non autorisée constitue une contrefaçon.</p>
                </div>
            </section>

            <section id="donnees" class="legal__section">
                <h2 class="legal__section-title">9. Données personnelles</h2>
                <div class="legal__text">
                    <p>Le traitement des données personnelles est effectué conformément au Règlement Général sur la
                    Protection des Données (RGPD) et à notre Politique de Confidentialité.</p>
                    <p>Les données collectées sont nécessaires au fonctionnement de la plateforme et à la mise en relation
                    entre étudiants et propriétaires.</p>
                    <p>Les utilisateurs disposent d'un droit d'accès, de rectification, de suppression, d'opposition
                    et de portabilité concernant leurs données personnelles.</p>
                    <p>Pour exercer ces droits, contactez-nous à : <a href="mailto:contact@dormquest.com">contact@dormquest.com</a></p>
                </div>
            </section>

            <section id="responsabilite" class="legal__section">
                <h2 class="legal__section-title">10. Responsabilité</h2>
                <div class="legal__text">
                <h3 class="legal__section-subtitle">10.1 Limitation de responsabilité</h3>
                <p>DormQuest ne saurait être tenu responsable :</p>
                <ul>
                    <li>Des dommages directs ou indirects résultant de l'utilisation de la plateforme</li>
                    <li>Des interruptions, bugs ou dysfonctionnements techniques</li>
                    <li>Du contenu publié par les utilisateurs</li>
                    <li>Des litiges entre utilisateurs</li>
                    <li>Des préjudices causés par des tiers (piratage, virus, etc.)</li>
                    <li>De la perte de données</li>
                </ul>

                <h3 class="legal__section-subtitle">10.2 Force majeure</h3>
                <p>DormQuest ne saurait être tenu responsable en cas de force majeure ou d'événements indépendants
                de sa volonté (catastrophe naturelle, grève, panne d'infrastructure, etc.).</p>
                </div>
            </section>

            <section id="signalement" class="legal__section">
                <h2 class="legal__section-title">11. Signalement et modération</h2>
                <div class="legal__text">
                    <p>Tout utilisateur peut signaler un contenu ou un comportement inapproprié via les outils
                    de signalement mis à disposition sur la plateforme.</p>
                    <p>DormQuest s'engage à examiner les signalements dans les meilleurs délais et à prendre
                    les mesures appropriées si nécessaire.</p>
                </div>
            </section>

            <section id="liens" class="legal__section">
                <h2 class="legal__section-title">12. Liens hypertextes</h2>
                <div class="legal__text">
                    <p>La plateforme peut contenir des liens vers d'autres sites web. DormQuest n'exerce aucun contrôle
                    sur ces sites et décline toute responsabilité quant à leur contenu, leur disponibilité ou
                    leur politique de confidentialité.</p>
                </div>
            </section>

            <section id="modification" class="legal__section">
                <h2 class="legal__section-title">13. Modification des CGU</h2>
                <div class="legal__text">
                    <p>DormQuest se réserve le droit de modifier les présentes CGU à tout moment.
                    Les modifications prennent effet dès leur publication sur la plateforme.</p>
                    <p>Les utilisateurs seront informés des modifications importantes par email ou notification sur la plateforme.
                    Il leur est conseillé de consulter régulièrement cette page.</p>
                    <p>La poursuite de l'utilisation de la plateforme après modification vaut acceptation des nouvelles CGU.</p>
                </div>
            </section>

            <section id="resiliation" class="legal__section">
                <h2 class="legal__section-title">14. Résiliation</h2>
                <div class="legal__text">
                    <p>L'utilisateur peut supprimer son compte à tout moment depuis son espace personnel
                    ou en contactant le support.</p>
                    <p>DormQuest peut résilier l'accès d'un utilisateur en cas de violation des présentes CGU,
                    avec ou sans préavis selon la gravité du manquement.</p>
                </div>
            </section>

            <section id="droit" class="legal__section">
                <h2 class="legal__section-title">15. Droit applicable et juridiction</h2>
                <div class="legal__text">
                    <p>Les présentes CGU sont régies par le droit français.</p>
                    <p>En cas de litige, les parties s'efforceront de trouver une solution amiable.
                    À défaut, le litige sera soumis aux tribunaux compétents de <strong>Issy-Les-Moulineaux</strong>.</p>
                </div>
            </section>

            <section id="mediation" class="legal__section">
                <h2 class="legal__section-title">16. Médiation</h2>
                <div class="legal__text">
                    <p>Conformément aux dispositions du Code de la consommation, en cas de litige, l'utilisateur
                    peut recourir gratuitement à un médiateur de la consommation en vue de la résolution amiable
                    du litige.</p>
                    <p>Coordonnées du médiateur : <a href="mailto:mediation@dormquest.com">mediation@dormquest.com</a></p>
                </div>
            </section>

            <section id="contact" class="legal__section">
                <h2 class="legal__section-title">17. Contact</h2>
                <div class="legal__text">
                    <p>Pour toute question concernant les présentes CGU ou l'utilisation de la plateforme,
                    vous pouvez nous contacter :</p>
                <div class="contact-info">
                    <p><strong>Email :</strong> <a href="mailto:contact@dormquest.com">contact@dormquest.com</a></p>
                    <p><strong>Adresse :</strong> 10 rue de Vanves, 92130 Issy-Les-moulineaux</p>
                    <p><strong>Téléphone :</strong> 01 49 54 52 00</p>
                </div>
                </div>
            </section>

            <div class="cgu-acceptance">
                <p><strong>En utilisant la plateforme DormQuest, vous reconnaissez avoir lu, compris et accepté
                les présentes Conditions Générales d'Utilisation.</strong></p>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
