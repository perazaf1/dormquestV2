<?php
// CGU.php - Conditions G�n�rales d'Utilisation
// CGU.php - Condi tions G�n�rales d'Utilisation
session_start();

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
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="cgu-container">
    <div class="cgu-wrapper">
        <div class="cgu-header">
            <h1>Conditions Générales d'Utilisation</h1>
            <p class="update-date">Dernière mise à jour : [DATE]</p>
        </div>

        <div class="cgu-content">
            <section class="cgu-section">
                <h2>1. Objet</h2>
                <p>
                    Les présentes Conditions Générales d'Utilisation (CGU) ont pour objet de définir les modalités 
                    et conditions dans lesquelles les utilisateurs peuvent accéder et utiliser la plateforme 
                    <strong>DormQuest</strong>, accessible à l'adresse <strong>[URL DU SITE]</strong>.
                </p>
                <p>
                    L'utilisation de la plateforme implique l'acceptation pleine et entière des présentes CGU.
                </p>
            </section>

            <section class="cgu-section">
                <h2>2. Définitions</h2>
                <ul>
                    <li><strong>Plateforme :</strong> désigne le site web DormQuest et l'ensemble de ses fonctionnalités</li>
                    <li><strong>Utilisateur :</strong> toute personne accédant à la plateforme</li>
                    <li><strong>Étudiant :</strong> utilisateur recherchant un logement</li>
                    <li><strong>Propriétaire :</strong> utilisateur proposant un logement à la location</li>
                    <li><strong>Compte :</strong> espace personnel créé sur la plateforme</li>
                </ul>
            </section>

            <section class="cgu-section">
                <h2>3. Accès à la plateforme</h2>
                <p>
                    La plateforme est accessible gratuitement à tout utilisateur disposant d'un accès à Internet. 
                    Tous les coûts liés à l'accès (matériel, logiciels, connexion) sont à la charge de l'utilisateur.
                </p>
                <p>
                    DormQuest se réserve le droit de suspendre, modifier ou interrompre l'accès à la plateforme 
                    à tout moment, sans préavis ni indemnité, notamment pour des raisons de maintenance.
                </p>
            </section>

            <section class="cgu-section">
                <h2>4. Inscription et compte utilisateur</h2>
                <h3>4.1 Création de compte</h3>
                <p>
                    L'accès à certaines fonctionnalités de DormQuest nécessite la création d'un compte utilisateur 
                    en tant qu'étudiant ou propriétaire.
                </p>
                
                <h3>4.2 Obligations de l'utilisateur</h3>
                <p>Lors de l'inscription, l'utilisateur s'engage à :</p>
                <ul>
                    <li>Fournir des informations exactes, complètes et à jour</li>
                    <li>Avoir au moins 18 ans ou disposer de l'autorisation parentale</li>
                    <li>Maintenir la confidentialité de ses identifiants de connexion</li>
                    <li>Informer immédiatement DormQuest de toute utilisation non autorisée de son compte</li>
                    <li>Être responsable de toutes les activités effectuées depuis son compte</li>
                </ul>

                <h3>4.3 Suspension et suppression de compte</h3>
                <p>
                    DormQuest se réserve le droit de suspendre ou supprimer tout compte en cas de 
                    violation des présentes CGU, sans préavis ni indemnité.
                </p>
            </section>

            <section class="cgu-section">
                <h2>5. Utilisation de la plateforme</h2>
                <h3>5.1 Règles générales</h3>
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

                <h3>5.2 Annonces de logements</h3>
                <p>Les propriétaires s'engagent à :</p>
                <ul>
                    <li>Publier des annonces véridiques et conformes à la réalité</li>
                    <li>Fournir des photos et descriptions fidèles du logement</li>
                    <li>Respecter la législation en vigueur concernant la location</li>
                    <li>Mettre à jour ou supprimer les annonces obsolètes</li>
                    <li>Ne pas discriminer les candidats locataires</li>
                </ul>

                <h3>5.3 Recherche de logement</h3>
                <p>Les étudiants s'engagent à :</p>
                <ul>
                    <li>Fournir des informations exactes lors des demandes</li>
                    <li>Respecter les propriétaires et les biens visités</li>
                    <li>Honorer les rendez-vous pris</li>
                </ul>
            </section>

            <section class="cgu-section">
                <h2>6. Contenu utilisateur</h2>
                <p>
                    Les utilisateurs peuvent publier du contenu sur DormQuest (annonces, commentaires, photos, etc.).
                </p>
                <p>
                    L'utilisateur garantit qu'il dispose de tous les droits nécessaires sur le contenu qu'il publie 
                    et que ce contenu ne viole aucun droit de tiers (propriété intellectuelle, droit à l'image, etc.).
                </p>
                <p>
                    En publiant du contenu, l'utilisateur accorde à DormQuest une licence non exclusive d'utilisation, 
                    de reproduction et de représentation de ce contenu dans le cadre du fonctionnement de la plateforme.
                </p>
                <p>
                    DormQuest se réserve le droit de modérer, supprimer ou refuser tout contenu qui ne respecterait pas 
                    les présentes CGU ou la législation en vigueur, sans préavis.
                </p>
            </section>

            <section class="cgu-section">
                <h2>7. Rôle de DormQuest</h2>
                <p>
                    DormQuest est une plateforme de mise en relation entre étudiants et propriétaires. 
                    Elle n'est pas partie aux contrats de location conclus entre les utilisateurs.
                </p>
                <p>
                    DormQuest ne garantit pas :
                </p>
                <ul>
                    <li>La véracité des informations publiées par les utilisateurs</li>
                    <li>La disponibilité effective des logements</li>
                    <li>La solvabilité des étudiants</li>
                    <li>La conclusion effective d'un contrat de location</li>
                </ul>
                <p>
                    Il appartient aux utilisateurs de vérifier les informations et de prendre toutes les précautions 
                    nécessaires avant de conclure une transaction.
                </p>
            </section>

            <section class="cgu-section">
                <h2>8. Propriété intellectuelle</h2>
                <p>
                    Tous les éléments de la plateforme (textes, images, logos, structure, charte graphique, 
                    code source, etc.) sont protégés par le droit d'auteur et appartiennent à DormQuest ou à ses partenaires.
                </p>
                <p>
                    Toute reproduction, distribution, modification ou exploitation sans autorisation expresse est interdite 
                    et peut faire l'objet de poursuites judiciaires.
                </p>
                <p>
                    Les marques, logos et signes distinctifs figurant sur la plateforme sont des marques déposées. 
                    Toute reproduction non autorisée constitue une contrefaçon.
                </p>
            </section>

            <section class="cgu-section">
                <h2>9. Données personnelles</h2>
                <p>
                    Le traitement des données personnelles est effectué conformément au Règlement Général sur la 
                    Protection des Données (RGPD) et à notre Politique de Confidentialité.
                </p>
                <p>
                    Les données collectées sont nécessaires au fonctionnement de la plateforme et à la mise en relation 
                    entre étudiants et propriétaires.
                </p>
                <p>
                    Les utilisateurs disposent d'un droit d'accès, de rectification, de suppression, d'opposition 
                    et de portabilité concernant leurs données personnelles.
                </p>
                <p>
                    Pour exercer ces droits, contactez-nous à : <strong>contact@dormquest.com</strong>
                </p>
            </section>

            <section class="cgu-section">
                <h2>10. Responsabilité</h2>
                <h3>10.1 Limitation de responsabilité</h3>
                <p>DormQuest ne saurait être tenu responsable :</p>
                <ul>
                    <li>Des dommages directs ou indirects résultant de l'utilisation de la plateforme</li>
                    <li>Des interruptions, bugs ou dysfonctionnements techniques</li>
                    <li>Du contenu publié par les utilisateurs</li>
                    <li>Des litiges entre utilisateurs</li>
                    <li>Des préjudices causés par des tiers (piratage, virus, etc.)</li>
                    <li>De la perte de données</li>
                </ul>

                <h3>10.2 Force majeure</h3>
                <p>
                    DormQuest ne saurait être tenu responsable en cas de force majeure ou d'événements indépendants 
                    de sa volonté (catastrophe naturelle, grève, panne d'infrastructure, etc.).
                </p>
            </section>

            <section class="cgu-section">
                <h2>11. Signalement et modération</h2>
                <p>
                    Tout utilisateur peut signaler un contenu ou un comportement inapproprié via les outils 
                    de signalement mis à disposition sur la plateforme.
                </p>
                <p>
                    DormQuest s'engage à examiner les signalements dans les meilleurs délais et à prendre 
                    les mesures appropriées si nécessaire.
                </p>
            </section>

            <section class="cgu-section">
                <h2>12. Liens hypertextes</h2>
                <p>
                    La plateforme peut contenir des liens vers d'autres sites web. DormQuest n'exerce aucun contrôle 
                    sur ces sites et décline toute responsabilité quant à leur contenu, leur disponibilité ou 
                    leur politique de confidentialité.
                </p>
            </section>

            <section class="cgu-section">
                <h2>13. Modification des CGU</h2>
                <p>
                    DormQuest se réserve le droit de modifier les présentes CGU à tout moment. 
                    Les modifications prennent effet dès leur publication sur la plateforme.
                </p>
                <p>
                    Les utilisateurs seront informés des modifications importantes par email ou notification sur la plateforme. 
                    Il leur est conseillé de consulter régulièrement cette page.
                </p>
                <p>
                    La poursuite de l'utilisation de la plateforme après modification vaut acceptation des nouvelles CGU.
                </p>
            </section>

            <section class="cgu-section">
                <h2>14. Résiliation</h2>
                <p>
                    L'utilisateur peut supprimer son compte à tout moment depuis son espace personnel 
                    ou en contactant le support.
                </p>
                <p>
                    DormQuest peut résilier l'accès d'un utilisateur en cas de violation des présentes CGU, 
                    avec ou sans préavis selon la gravité du manquement.
                </p>
            </section>

            <section class="cgu-section">
                <h2>15. Droit applicable et juridiction</h2>
                <p>
                    Les présentes CGU sont régies par le droit français.
                </p>
                <p>
                    En cas de litige, les parties s'efforceront de trouver une solution amiable. 
                    À défaut, le litige sera soumis aux tribunaux compétents de <strong>Paris</strong>.
                </p>
            </section>

            <section class="cgu-section">
                <h2>16. Médiation</h2>
                <p>
                    Conformément aux dispositions du Code de la consommation, en cas de litige, l'utilisateur 
                    peut recourir gratuitement à un médiateur de la consommation en vue de la résolution amiable 
                    du litige.
                </p>
                <p>
                    Coordonnées du médiateur : [À COMPLÉTER]
                </p>
            </section>

            <section class="cgu-section">
                <h2>17. Contact</h2>
                <p>
                    Pour toute question concernant les présentes CGU ou l'utilisation de la plateforme, 
                    vous pouvez nous contacter :
                </p>
                <div class="contact-info">
                    <p><strong>Email :</strong> <a href="mailto:[VOTRE EMAIL]">contact@dormquest.com</a></p>
                    <p><strong>Adresse :</strong> Paris, France</p>
                    <p><strong>Téléphone :</strong> +33 1 23 45 67 89</p>
                </div>
            </section>

            <div class="cgu-acceptance">
                <p>
                    <strong>En utilisant la plateforme DormQuest, vous reconnaissez avoir lu, compris et accepté 
                    les présentes Conditions Générales d'Utilisation.</strong>
                </p>
            </div>
        </div>
    </div>
</main>

<style>
.cgu-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 20px;
}

.cgu-wrapper {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.cgu-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    text-align: center;
}

.cgu-header h1 {
    margin: 0;
    font-size: 36px;
    font-weight: 700;
}

.update-date {
    margin: 10px 0 0 0;
    font-size: 14px;
    opacity: 0.9;
}

.cgu-content {
    padding: 40px;
}

.cgu-section {
    margin-bottom: 40px;
}

.cgu-section h2 {
    color: #667eea;
    font-size: 24px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.cgu-section h3 {
    color: #333;
    font-size: 18px;
    margin-top: 20px;
    margin-bottom: 10px;
}

.cgu-section p {
    line-height: 1.8;
    color: #555;
    margin-bottom: 15px;
    text-align: justify;
}

.cgu-section ul {
    margin: 15px 0;
    padding-left: 30px;
}

.cgu-section li {
    line-height: 1.8;
    color: #555;
    margin-bottom: 10px;
}

.contact-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.contact-info p {
    margin: 8px 0;
}

.contact-info a {
    color: #667eea;
    text-decoration: none;
}

.contact-info a:hover {
    text-decoration: underline;
}

.cgu-acceptance {
    background: #fff9e6;
    border: 2px solid #ffeb3b;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
    text-align: center;
}

.cgu-acceptance p {
    margin: 0;
    color: #333;
    font-size: 16px;
}

@media (max-width: 768px) {
    .cgu-container {
        padding: 20px 10px;
    }

    .cgu-header {
        padding: 30px 20px;
    }

    .cgu-header h1 {
        font-size: 28px;
    }

    .cgu-content {
        padding: 20px;
    }

    .cgu-section h2 {
        font-size: 20px;
    }
}
</style>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
