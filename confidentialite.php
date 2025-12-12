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
    <title>Dormquest - Politique de confidentialité</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="legal" id="rgpd">
        <div class="legal__container">
            <h1 class="legal__title">
                DORMQUEST - POLITIQUE DE CONFIDENTIALITE
            </h1>
            <p class="legal__intro">Dernière mise à jour : <strong>10/12/2025</strong><br>

                Cette politique de confidentialité décrit les données personnelles qui sont collectées ou générées
                (traitées) lorsque vous interagissez avec DormQuest par l’intermédiaire de nos sites internet ,
                expériences digitales, lequels font tous partie de la plateforme DormQuest ("Plateforme") Elle explique
                également la façon dont vos données personnelles sont utilisées, partagées et protégées, les choix dont
                vous disposez s’agissant de vos données personnelles et la façon dont vous pouvez nous contacter.
            <p>
            <section id="questions" class="legal__section">
                <ul class="legal__intro-list">
                    <li>Qui est responsable du traitement de vos données personnelles ?</li>
                    <li>Quelles sont les données personnelles que nous collectons et à quel moment ?</li>
                    <li>Les outils de gestion des données personnelles que nous collectons</li>
                    <li>Pourquoi et comment nous utilisons vos données personnelles ?</li>
                    <li>Partage de vos données personnelles</li>
                    <li>Protection et gestion de vos données personnelles</li>
                    <li>Vos droits</li>
                    <li>Données relatives aux enfants</li>
                    <li>Modification de notre Politique de Confidentialité</li>
                    <li>Cookies et balises</li>
                    <li>Questions et commentaires</li>
                </ul>
            </section>

            <section id="responsabilite" class="legal__section">
                <h2 class="legal__section-title">
                    Qui est responsable du traitement de vos données personnelles ?
                </h2>
                <div class="legal__text">
                    <p>
                        L’entité Dormquest responsable du traitement de vos données personnelles va dépendre de la façon
                        dont vous interagissez avec la Plateforme de Dormquest et de votre localisation géographique
                        dans le monde.
                    </p>
                </div>
            </section>

            <section id="donneesPerso" class="legal__section">
                <h2 class="legal__section-title">
                    Quelles sont les données personnelles que nous collectons et à quel moment ?
                </h2>
                <div class="legal__text">
                    Nous vous demandons de nous communiquer certaines données personnelles pour vous fournir le
                    sservices que vous sollicitez. Tel est le cas, par exemple, lorsque vous contactez notre service,
                    demandez à recevoir des communications, créez un compte, ou lorsque vous utilisez notre Plateforme.
                    <br>
                    Ces données personnelles comprennent vos :
                    <ul>
                        <li>coordonnées, comprenant nom, prénom, e-mail, numéro de téléphone, photo d'identité</li>
                        <li>informations d’utilisateur et de compte, comprenant vos pseudonyme, mot de passe et
                            identifiant unique d’utilisateur ;</li>
                        <li>images, photos</li>
                        <li>données sur votre budget fournies par vos soins</li>
                        <li>données sur votre localisation fournies par vos soins</li>
                        <li>préférences personnelles, comprenant votre liste de souhaits, vos préférences marketing et
                            en matière de cookies.</li>
                    </ul>
                    <p>
                        Lorsque vous interagissez avec notre Plateforme, certaines données sont automatiquement
                        collectées depuis votre appareil ou navigateur internet. Plus d’informations sur ces pratiques
                        sont fournies dans la section “Cookies et balises” ci-après. Ces données incluent:
                    </p>
                    <ul>
                        <li>ID et type d’ appareil, état des appels, accès au réseau, information de stockage et de
                            batterie ;</li>
                        <li>cookies, adresses IP, en-têtes de référent, données identifiant votre navigateur internet sa
                            version, pixels espions, balises web et les interactions avec notre Plateforme.</li>
                    </ul>
                </div>
            </section>

            <section id="enfants" class="legal__section">
                <h2 class="legal__section-title">
                    Données relatives aux enfants
                </h2>
                <div class="legal__text">
                    Nous respectons les dispositions légales locales et n’autorisons pas les enfants à s’inscrire sur
                    notre Plateforme s’ils n’ont pas atteint la limite d’âge légale du pays dans lequel ils résident.
                </div>
            </section>

            <section id="outils" class="legal__section">
                <h2 class="legal__section-title">
                    Les outils de gestion des données personnelles que nous collectons
                </h2>
                <div class="legal__text">
                    Lorsque vous utilisez notre Plateforme, nous vous fournissons en temps utile l’information requise
                    ou obtenons votre consentement pour certains usages. Par exemple, nous recueillerons votre
                    consentement pour utiliser votre localisation ou vous envoyer des notifications. Nous pouvons
                    recueillir ce consentement à partir de la Plateforme ou en utilisant les autorisations standard
                    disponibles sur votre appareil.
                    <br>
                    Dans de nombreux cas, votre navigateur internet ou votre appareil mobile fournira des outils
                    supplémentaires pour vous permettre de contrôler la façon dont votre appareil collecte ou partage
                    certaines catégories de données personnelles. Par exemple, votre appareil mobile ou votre navigateur
                    internet contiennent des outils vous permettant de gérer l’usage des cookies ou le partage de votre
                    localisation. Nous vous encourageons à vous familiariser avec les outils disponibles sur vos
                    appareils et à les utiliser.
                </div>
            </section>

            <section id="objet" class="legal__section">
                <h2 class="legal__section-title">
                    Pourquoi et comment nous utilisons vos données personnelles ?
                </h2>
                <p class="legal_intro">
                    Nous utilisons vos données personnelles aux fins suivantes :
                    <br>
                    Nous vous enverrons des communications marketing et des nouvelles à propos des offres de logement, services susceptibles de vous intéresser.  Vous pouvez vous désinscrire à tout moment après avoir donné votre consentement. 
                    <br>
                    Si vous avez déjà un compte chez Dormquest, nous pouvons utiliser les informations de contact que vous avez fournies pour vous envoyer des communications marketing à propos de notre site, lorsque la loi applicable le permet (à moins que vous les ayez refusées). Dans d’autres cas, nous vous demandons votre consentement pour vous envoyer des communications marketing.

                </p>
                <div class="legal__text">
                    <h3 class="legal__text-title">
                        Pour fournir les fonctionnalités de la Plateforme et des Services que vous sollicitez
                    </h3>
                    <p>
                        Si vous utilisez notre Plateforme, nous utiliserons vos données personnelles pour vous fournir le logement ou service demandé.
                        Si vous contactez notre service, nous utiliserons les informations vous concernant, telles que les informations de contact, afin de vous aider à résoudre votre problème ou répondre à votre question.
                        Dans de nombreux cas, l’utilisation de certaines fonctionnalités de notre Plateforme nécessitent de fournir à DormQuest des données complémentaires ou un consentement additionnel, et ce pour l’utilisation de vos données à certaines fins.
                    </p>
                    <h3 class="legal__text_title">
                        Pour faire fonctionner, améliorer et maintenir nos activités, produits et servicesx
                    </h3>
                    <p class="legal_intro">
                        Nous utilisons les données personnelles que vous nous communiquez pour faire fonctionner nos activités.  Par exemple, lorsque vous effectuez un payement pour un logement, nous utilisons cette information à des fins comptables, d’audit et d’autres finalités internes. Nous pouvons utiliser les données personnelles relatives à la façon dont vous utilisez nos propositions et services pour améliorer votre expérience d’utilisateur et nous permettre de détecter des problèmes techniques ou de service et administrer notre Plateforme.
                    </p>


                    
                </div>
            </section>



        </div>



    </main>


    <?php include 'includes/footer.php'; ?>

</body>

</html>