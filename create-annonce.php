<?php
session_start();
define('ACCESS_ALLOWED', true);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Seuls les loueurs peuvent créer des annonces
require_loueur();

$errors = [];
$success = false;

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

        // Validation des photos
        $photos = [];
        if (isset($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
            $photoCount = count(array_filter($_FILES['photos']['name']));
            if ($photoCount > MAX_PHOTOS_PER_ANNONCE) {
                $errors[] = 'Maximum ' . MAX_PHOTOS_PER_ANNONCE . ' photos autorisées';
            }
        }

        // Si pas d'erreurs, insérer dans la base de données
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Insérer l'annonce
                $sql = "INSERT INTO annonces (idLoueur, titre, description, adresse, ville, typeLogement, prixMensuel, superficie, statut, dateCreation)
                        VALUES (:idLoueur, :titre, :description, :adresse, :ville, :typeLogement, :prixMensuel, :superficie, 'active', NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':idLoueur' => get_user_id(),
                    ':titre' => $titre,
                    ':description' => $description,
                    ':adresse' => $adresse,
                    ':ville' => $ville,
                    ':typeLogement' => $typeLogement,
                    ':prixMensuel' => $prixMensuel,
                    ':superficie' => $superficie
                ]);

                $annonceId = $pdo->lastInsertId();

                // Insérer les critères
                $sqlCriteres = "INSERT INTO criteres_logement (idAnnonce, accesPMR, meuble, eligibleAPL, parkingDisponible, animauxAcceptes)
                                VALUES (:idAnnonce, :accesPMR, :meuble, :eligibleAPL, :parkingDisponible, :animauxAcceptes)";
                $stmtCriteres = $pdo->prepare($sqlCriteres);
                $stmtCriteres->execute([
                    ':idAnnonce' => $annonceId,
                    ':accesPMR' => $accesPMR,
                    ':meuble' => $meuble,
                    ':eligibleAPL' => $eligibleAPL,
                    ':parkingDisponible' => $parkingDisponible,
                    ':animauxAcceptes' => $animauxAcceptes
                ]);

                // Traiter les photos
                $uploadErrors = [];
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

                        // Vérifier les erreurs d'upload
                        if ($file['error'] !== UPLOAD_ERR_OK) {
                            $uploadErrors[] = "Erreur lors de l'upload de " . $file['name'];
                            continue;
                        }

                        if (is_valid_image($file)) {
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $filename = generate_unique_filename($extension);
                            $destination = ANNONCE_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename;

                            if (move_uploaded_file($file['tmp_name'], $destination)) {
                                $sqlPhoto = "INSERT INTO photos_annonces (idAnnonce, cheminPhoto) VALUES (:idAnnonce, :cheminPhoto)";
                                $stmtPhoto = $pdo->prepare($sqlPhoto);
                                $stmtPhoto->execute([
                                    ':idAnnonce' => $annonceId,
                                    ':cheminPhoto' => 'uploads/annonces/' . $filename
                                ]);
                            } else {
                                $uploadErrors[] = "Impossible de déplacer le fichier " . $file['name'];
                            }
                        } else {
                            $uploadErrors[] = "Le fichier " . $file['name'] . " n'est pas une image valide (max 20 Mo, formats: JPG, PNG)";
                        }
                    }
                }

                // Afficher les erreurs d'upload s'il y en a
                if (!empty($uploadErrors)) {
                    $_SESSION['upload_errors'] = $uploadErrors;
                }

                $pdo->commit();
                $success = true;

            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = 'Erreur lors de la création de l\'annonce : ' . $e->getMessage();
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
    <title>Créer une annonce - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/create-annonce.css">
    <style>
        
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="create-annonce-container">
        <h1>Créer une annonce</h1>

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
                <strong>Succès !</strong> Votre annonce a été créée avec succès.
                <br><a href="dashboard-loueur.php">Voir mes annonces</a>
            </div>
            <?php if (isset($_SESSION['upload_errors']) && !empty($_SESSION['upload_errors'])): ?>
                <div class="alert alert-warning">
                    <strong>Attention :</strong> Certaines photos n'ont pas pu être uploadées :
                    <ul>
                        <?php foreach ($_SESSION['upload_errors'] as $uploadError): ?>
                            <li><?php echo e($uploadError); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['upload_errors']); ?>
            <?php endif; ?>
        <?php else: ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="form-group">
                <label for="titre">Titre de l'annonce *</label>
                <input type="text" id="titre" name="titre" required
                       placeholder="Ex: Studio meublé proche université"
                       value="<?php echo isset($_POST['titre']) ? e($_POST['titre']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required
                          placeholder="Décrivez votre logement en détail..."><?php echo isset($_POST['description']) ? e($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="type_logement">Type de logement *</label>
                    <select id="type_logement" name="type_logement" required>
                        <option value="">Sélectionnez...</option>
                        <?php foreach (TYPES_LOGEMENT as $key => $label): ?>
                            <option value="<?php echo $key; ?>"
                                    <?php echo (isset($_POST['type_logement']) && $_POST['type_logement'] === $key) ? 'selected' : ''; ?>>
                                <?php echo e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" required
                           placeholder="Ex: Paris"
                           value="<?php echo isset($_POST['ville']) ? e($_POST['ville']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="adresse">Adresse complète *</label>
                <input type="text" id="adresse" name="adresse" required
                       placeholder="Ex: 15 rue de la République"
                       value="<?php echo isset($_POST['adresse']) ? e($_POST['adresse']) : ''; ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prix_mensuel">Prix mensuel (€) *</label>
                    <input type="number" id="prix_mensuel" name="prix_mensuel" step="0.01" min="0" required
                           placeholder="Ex: 450"
                           value="<?php echo isset($_POST['prix_mensuel']) ? e($_POST['prix_mensuel']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="superficie">Superficie (m²) *</label>
                    <input type="number" id="superficie" name="superficie" min="1" required
                           placeholder="Ex: 25"
                           value="<?php echo isset($_POST['superficie']) ? e($_POST['superficie']) : ''; ?>">
                </div>
            </div>

            <div class="form-group criteres-group">
                <h3>Critères du logement</h3>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="meuble" name="meuble"
                               <?php echo isset($_POST['meuble']) ? 'checked' : ''; ?>>
                        <label for="meuble">Meublé</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="eligible_apl" name="eligible_apl"
                               <?php echo isset($_POST['eligible_apl']) ? 'checked' : ''; ?>>
                        <label for="eligible_apl">Éligible APL</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="acces_pmr" name="acces_pmr"
                               <?php echo isset($_POST['acces_pmr']) ? 'checked' : ''; ?>>
                        <label for="acces_pmr">Accès PMR</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="parking" name="parking"
                               <?php echo isset($_POST['parking']) ? 'checked' : ''; ?>>
                        <label for="parking">Parking</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="animaux" name="animaux"
                               <?php echo isset($_POST['animaux']) ? 'checked' : ''; ?>>
                        <label for="animaux">Animaux acceptés</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Photos (max <?php echo MAX_PHOTOS_PER_ANNONCE; ?>)</label>
                <div class="photo-upload">
                    <p>Ajoutez des photos de votre logement</p>
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/jpg" multiple>
                    <small>Formats acceptés : JPG, PNG (max 20 Mo par photo)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Créer l'annonce</button>
                <a href="dashboard-loueur.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>

        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
