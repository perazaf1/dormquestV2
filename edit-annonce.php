<?php
session_start();
define('ACCESS_ALLOWED', true);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Seuls les loueurs peuvent modifier des annonces
require_loueur();

$errors = [];
$success = false;
$annonceId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer l'annonce
if ($annonceId) {
    $annonce = get_annonce_by_id($pdo, $annonceId);

    // Vérifier que l'annonce existe et appartient au loueur connecté
    if (!$annonce || $annonce['idLoueur'] != get_user_id()) {
        header('Location: dashboard-loueur.php');
        exit;
    }

    // Récupérer les photos et critères
    $photos = get_photos_annonce_with_ids($pdo, $annonceId);
    $criteres = get_criteres_annonce($pdo, $annonceId);
} else {
    header('Location: dashboard-loueur.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        // Récupérer et valider les données
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $typeLogement = $_POST['type_logement'] ?? '';
        $prixMensuel = floatval($_POST['prix_mensuel'] ?? 0);
        $superficie = intval($_POST['superficie'] ?? 0);

        // Critères optionnels
        $accesPMR = isset($_POST['acces_pmr']) ? 1 : 0;
        $meuble = isset($_POST['meuble']) ? 1 : 0;
        $eligibleAPL = isset($_POST['eligible_apl']) ? 1 : 0;
        $parkingDisponible = isset($_POST['parking']) ? 1 : 0;
        $animauxAcceptes = isset($_POST['animaux']) ? 1 : 0;

        // Validation
        if (empty($titre)) $errors[] = 'Le titre est requis';
        if (empty($description)) $errors[] = 'La description est requise';
        if (empty($adresse)) $errors[] = 'L\'adresse est requise';
        if (empty($ville)) $errors[] = 'La ville est requise';
        if (!is_valid_value($typeLogement, array_keys(TYPES_LOGEMENT))) {
            $errors[] = 'Type de logement invalide';
        }
        if ($prixMensuel <= 0) $errors[] = 'Le prix mensuel doit être supérieur à 0';
        if ($superficie <= 0) $errors[] = 'La superficie doit être supérieure à 0';

        // Validation des nouvelles photos
        $photoActuelCount = count($photos);
        $newPhotoCount = 0;
        if (isset($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
            $newPhotoCount = count(array_filter($_FILES['photos']['name']));
            if ($photoActuelCount + $newPhotoCount > MAX_PHOTOS_PER_ANNONCE) {
                $errors[] = 'Maximum ' . MAX_PHOTOS_PER_ANNONCE . ' photos autorisées (actuellement: ' . $photoActuelCount . ')';
            }
        }

        // Si pas d'erreurs, mettre à jour dans la base de données
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Mettre à jour l'annonce
                $updateData = [
                    'titre' => $titre,
                    'description' => $description,
                    'adresse' => $adresse,
                    'ville' => $ville,
                    'typeLogement' => $typeLogement,
                    'prixMensuel' => $prixMensuel,
                    'superficie' => $superficie
                ];
                update_annonce($pdo, $annonceId, $updateData);

                // Mettre à jour les critères
                $criteresData = [
                    'accesPMR' => $accesPMR,
                    'meuble' => $meuble,
                    'eligibleAPL' => $eligibleAPL,
                    'parkingDisponible' => $parkingDisponible,
                    'animauxAcceptes' => $animauxAcceptes
                ];
                update_criteres_annonce($pdo, $annonceId, $criteresData);

                // Traiter les nouvelles photos
                if (isset($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
                    foreach ($_FILES['photos']['name'] as $key => $name) {
                        if (empty($name)) continue;

                        $file = [
                            'name' => $_FILES['photos']['name'][$key],
                            'type' => $_FILES['photos']['type'][$key],
                            'tmp_name' => $_FILES['photos']['tmp_name'][$key],
                            'error' => $_FILES['photos']['error'][$key],
                            'size' => $_FILES['photos']['size'][$key]
                        ];

                        if (is_valid_image($file)) {
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $filename = generate_unique_filename($extension);
                            $destination = ANNONCE_UPLOAD_PATH . '/' . $filename;

                            if (move_uploaded_file($file['tmp_name'], $destination)) {
                                add_photo_annonce($pdo, $annonceId, 'uploads/annonces/' . $filename);
                            }
                        }
                    }
                }

                $pdo->commit();
                $success = true;

                // Recharger les données
                $annonce = get_annonce_by_id($pdo, $annonceId);
                $photos = get_photos_annonce_with_ids($pdo, $annonceId);
                $criteres = get_criteres_annonce($pdo, $annonceId);

            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = 'Erreur lors de la mise à jour de l\'annonce : ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'annonce - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/edit-annonce.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="edit-annonce-container">
        <h1>Modifier l'annonce</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Erreurs :</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>Succès !</strong> Votre annonce a été mise à jour avec succès.
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="form-group">
                <label for="titre">Titre de l'annonce *</label>
                <input type="text" id="titre" name="titre" required
                       placeholder="Ex: Studio meublé proche université"
                       value="<?php echo e($annonce['titre']); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required
                          placeholder="Décrivez votre logement en détail..."><?php echo e($annonce['description']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="type_logement">Type de logement *</label>
                    <select id="type_logement" name="type_logement" required>
                        <option value="">Sélectionnez...</option>
                        <?php foreach (TYPES_LOGEMENT as $key => $label): ?>
                            <option value="<?php echo $key; ?>"
                                    <?php echo ($annonce['typeLogement'] === $key) ? 'selected' : ''; ?>>
                                <?php echo e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" required
                           placeholder="Ex: Paris"
                           value="<?php echo e($annonce['ville']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="adresse">Adresse complète *</label>
                <input type="text" id="adresse" name="adresse" required
                       placeholder="Ex: 15 rue de la République"
                       value="<?php echo e($annonce['adresse']); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prix_mensuel">Prix mensuel (€) *</label>
                    <input type="number" id="prix_mensuel" name="prix_mensuel" step="0.01" min="0" required
                           placeholder="Ex: 450"
                           value="<?php echo e($annonce['prixMensuel']); ?>">
                </div>

                <div class="form-group">
                    <label for="superficie">Superficie (m²) *</label>
                    <input type="number" id="superficie" name="superficie" min="1" required
                           placeholder="Ex: 25"
                           value="<?php echo e($annonce['superficie']); ?>">
                </div>
            </div>

            <div class="form-group criteres-group">
                <h3>Critères du logement</h3>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="meuble" name="meuble"
                               <?php echo $criteres['meuble'] ? 'checked' : ''; ?>>
                        <label for="meuble">Meublé</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="eligible_apl" name="eligible_apl"
                               <?php echo $criteres['eligibleAPL'] ? 'checked' : ''; ?>>
                        <label for="eligible_apl">Éligible APL</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="acces_pmr" name="acces_pmr"
                               <?php echo $criteres['accesPMR'] ? 'checked' : ''; ?>>
                        <label for="acces_pmr">Accès PMR</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="parking" name="parking"
                               <?php echo $criteres['parkingDisponible'] ? 'checked' : ''; ?>>
                        <label for="parking">Parking</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="animaux" name="animaux"
                               <?php echo $criteres['animauxAcceptes'] ? 'checked' : ''; ?>>
                        <label for="animaux">Animaux acceptés</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Photos actuelles (<?php echo count($photos); ?>/<?php echo MAX_PHOTOS_PER_ANNONCE; ?>)</label>
                <?php if (!empty($photos)): ?>
                    <div class="photos-grid">
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-item" data-photo-id="<?php echo $photo['id']; ?>">
                                <img src="<?php echo e($photo['cheminPhoto']); ?>" alt="Photo">
                                <button type="button" class="delete-photo-btn"
                                        data-photo-id="<?php echo $photo['id']; ?>"
                                        data-csrf="<?php echo generate_csrf_token(); ?>">
                                    Supprimer
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-photos">Aucune photo pour cette annonce</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Ajouter des photos (max <?php echo MAX_PHOTOS_PER_ANNONCE - count($photos); ?> photos)</label>
                <div class="photo-upload">
                    <p>Ajoutez de nouvelles photos de votre logement</p>
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/jpg" multiple>
                    <small>Formats acceptés : JPG, PNG (max 2 Mo par photo)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="dashboard-loueur.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/edit-annonce.js"></script>
</body>
</html>
