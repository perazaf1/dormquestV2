<?php
// annonce.php - Detail d'une annonce
session_start();
define('ACCESS_ALLOWED', true);
require_once 'config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Verifier si l'utilisateur est connecte
$isLoggedIn = is_logged_in();
$userRole = get_user_role();

// Recuperer l'ID de l'annonce
$annonceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($annonceId <= 0) {
    header('Location: annonces.php');
    exit;
}

// Recuperer les donnees de l'annonce
$annonce = get_annonce_by_id($pdo, $annonceId);

if (!$annonce) {
    header('Location: annonces.php');
    exit;
}

// Recuperer les photos
$photos = get_photos_annonce($pdo, $annonceId);

// Recuperer les criteres
$criteres = get_criteres_annonce($pdo, $annonceId);

// Verifier si l'annonce est dans les favoris (si etudiant connecte)
$isFavori = false;
$hasApplied = false;

if ($isLoggedIn && $userRole === 'etudiant') {
    $isFavori = is_favori($pdo, get_user_id(), $annonceId);
    $hasApplied = has_candidature($pdo, get_user_id(), $annonceId);
}

// Fonctions helper pour le formatage
function format_type_logement($type) {
    $types = [
        'studio' => 'Studio',
        'colocation' => 'Colocation',
        'residence_etudiante' => 'Residence etudiante',
        'chambre_habitant' => 'Chambre chez l\'habitant'
    ];
    return $types[$type] ?? $type;
}

function format_type_loueur($type) {
    $types = [
        'particulier' => 'Particulier',
        'agence' => 'Agence immobiliere',
        'organisme' => 'Organisme',
        'crous' => 'CROUS'
    ];
    return $types[$type] ?? $type;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($annonce['titre']) ?> - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/annonce-detail.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="annonce-detail">
        <div class="annonce-detail__container">

            <!-- Fil d'Ariane -->
            <nav class="annonce-detail__breadcrumb">
                <a href="index.php">Accueil</a>
                <span>/</span>
                <a href="annonces.php">Annonces</a>
                <span>/</span>
                <span><?= e($annonce['ville']) ?></span>
            </nav>

            <!-- En-tete avec titre et actions -->
            <div class="annonce-detail__header">
                <div class="annonce-detail__header-content">
                    <h1 class="annonce-detail__title"><?= e($annonce['titre']) ?></h1>
                    <p class="annonce-detail__location">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= e($annonce['adresse']) ?>, <?= e($annonce['ville']) ?>
                    </p>
                </div>
                <div class="annonce-detail__header-actions">
                    <?php if ($isLoggedIn && $userRole === 'etudiant'): ?>
                        <button class="annonce-detail__btn-favorite <?= $isFavori ? 'active' : '' ?>" data-annonce-id="<?= $annonceId ?>">
                            <i class="fa-<?= $isFavori ? 'solid' : 'regular' ?> fa-heart"></i>
                            <span><?= $isFavori ? 'Retirer des favoris' : 'Ajouter aux favoris' ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Galerie de photos -->
            <div class="annonce-detail__gallery">
                <?php if (empty($photos)): ?>
                    <div class="annonce-detail__gallery-main">
                        <img src="placeholder.php?type=<?= e($annonce['typeLogement']) ?>&seed=<?= $annonceId ?>&width=800&height=600" alt="Pas de photo disponible">
                    </div>
                <?php else: ?>
                    <div class="annonce-detail__gallery-main">
                        <img src="<?= e($photos[0]['cheminPhoto']) ?>" alt="<?= e($annonce['titre']) ?>" id="mainPhoto">
                    </div>
                    <?php if (count($photos) > 1): ?>
                        <div class="annonce-detail__gallery-thumbs">
                            <?php foreach ($photos as $index => $photo): ?>
                                <img src="<?= e($photo['cheminPhoto']) ?>"
                                     alt="Photo <?= $index + 1 ?>"
                                     class="annonce-detail__gallery-thumb <?= $index === 0 ? 'active' : '' ?>"
                                     onclick="changeMainPhoto('<?= e($photo['cheminPhoto']) ?>', this)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Contenu principal : informations + sidebar -->
            <div class="annonce-detail__content">

                <!-- Colonne principale -->
                <div class="annonce-detail__main">

                    <!-- Caracteristiques principales -->
                    <section class="annonce-detail__section">
                        <h2 class="annonce-detail__section-title">Caracteristiques</h2>
                        <div class="annonce-detail__features">
                            <div class="annonce-detail__feature">
                                <i class="fa-solid fa-home"></i>
                                <span class="annonce-detail__feature-label">Type</span>
                                <span class="annonce-detail__feature-value"><?= format_type_logement($annonce['typeLogement']) ?></span>
                            </div>
                            <div class="annonce-detail__feature">
                                <i class="fa-solid fa-maximize"></i>
                                <span class="annonce-detail__feature-label">Surface</span>
                                <span class="annonce-detail__feature-value"><?= e($annonce['superficie']) ?> m2</span>
                            </div>
                            <div class="annonce-detail__feature">
                                <i class="fa-solid fa-euro-sign"></i>
                                <span class="annonce-detail__feature-label">Loyer</span>
                                <span class="annonce-detail__feature-value"><?= format_prix($annonce['prixMensuel']) ?>/mois</span>
                            </div>
                            <div class="annonce-detail__feature">
                                <i class="fa-solid fa-calendar"></i>
                                <span class="annonce-detail__feature-label">Publie le</span>
                                <span class="annonce-detail__feature-value"><?= format_date($annonce['dateCreation']) ?></span>
                            </div>
                        </div>
                    </section>

                    <!-- Description -->
                    <section class="annonce-detail__section">
                        <h2 class="annonce-detail__section-title">Description</h2>
                        <p class="annonce-detail__description"><?= nl2br(e($annonce['description'])) ?></p>
                    </section>

                    <!-- Criteres du logement -->
                    <?php if ($criteres): ?>
                    <section class="annonce-detail__section">
                        <h2 class="annonce-detail__section-title">Equipements et services</h2>
                        <div class="annonce-detail__criteria">
                            <?php if ($criteres['meuble']): ?>
                                <div class="annonce-detail__criterion">
                                    <i class="fa-solid fa-couch"></i>
                                    <span>Meuble</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($criteres['eligibleAPL']): ?>
                                <div class="annonce-detail__criterion">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                    <span>Eligible APL</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($criteres['parkingDisponible']): ?>
                                <div class="annonce-detail__criterion">
                                    <i class="fa-solid fa-square-parking"></i>
                                    <span>Parking disponible</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($criteres['accesPMR']): ?>
                                <div class="annonce-detail__criterion">
                                    <i class="fa-solid fa-wheelchair"></i>
                                    <span>Acces PMR</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($criteres['animauxAcceptes']): ?>
                                <div class="annonce-detail__criterion">
                                    <i class="fa-solid fa-paw"></i>
                                    <span>Animaux acceptes</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>

                <!-- Sidebar avec informations du loueur -->
                <aside class="annonce-detail__sidebar">

                    <!-- Prix et action -->
                    <div class="annonce-detail__price-box">
                        <div class="annonce-detail__price">
                            <span class="annonce-detail__price-amount"><?= format_prix($annonce['prixMensuel']) ?></span>
                            <span class="annonce-detail__price-period">/mois</span>
                        </div>

                        <?php if ($isLoggedIn && $userRole === 'etudiant'): ?>
                            <?php if ($hasApplied): ?>
                                <button class="annonce-detail__btn-apply" disabled>
                                    <i class="fa-solid fa-check"></i>
                                    Candidature envoyee
                                </button>
                            <?php else: ?>
                                <button class="annonce-detail__btn-apply" onclick="postuler(<?= $annonceId ?>)">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    Postuler
                                </button>
                            <?php endif; ?>
                        <?php elseif ($isLoggedIn && $userRole === 'loueur'): ?>
                            <p class="annonce-detail__info-message">
                                <i class="fa-solid fa-info-circle"></i>
                                Vous etes loueur
                            </p>
                        <?php else: ?>
                            <a href="login.php" class="annonce-detail__btn-apply">
                                <i class="fa-solid fa-sign-in-alt"></i>
                                Se connecter pour postuler
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Informations du loueur -->
                    <div class="annonce-detail__landlord">
                        <h3 class="annonce-detail__landlord-title">Proprietaire</h3>
                        <div class="annonce-detail__landlord-info">
                            <?php if ($annonce['photoDeProfil']): ?>
                                <img src="<?= e($annonce['photoDeProfil']) ?>" alt="<?= e($annonce['prenom']) ?>" class="annonce-detail__landlord-photo">
                            <?php else: ?>
                                <div class="annonce-detail__landlord-photo-placeholder">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <div class="annonce-detail__landlord-details">
                                <p class="annonce-detail__landlord-name"><?= e($annonce['prenom']) ?> <?= e($annonce['nom']) ?></p>
                                <?php if ($annonce['typeLoueur']): ?>
                                    <p class="annonce-detail__landlord-type"><?= format_type_loueur($annonce['typeLoueur']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($isLoggedIn && $userRole === 'etudiant'): ?>
                            <a href="mailto:<?= e($annonce['email']) ?>" class="annonce-detail__btn-contact">
                                <i class="fa-solid fa-envelope"></i>
                                Contacter
                            </a>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://kit.fontawesome.com/794b85b760.js" crossorigin="anonymous"></script>
    <script src="js/annonce-detail.js"></script>
</body>

</html>
