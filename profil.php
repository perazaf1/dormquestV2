<?php
// profil.php - Page de profil utilisateur (étudiant ou loueur)
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Vérifier que l'utilisateur est connecté
require_login();

$user_id = get_user_id();
$errors = [];
$success = '';

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    
    // Validation
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email est invalide.";
    }
    
    // Champs spécifiques selon le rôle
    if (is_etudiant()) {
        $ville_recherche = trim($_POST['ville_recherche'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        
        if (empty($ville_recherche)) {
            $errors[] = "La ville de recherche est obligatoire.";
        }
        if (empty($budget) || !is_numeric($budget) || $budget <= 0) {
            $errors[] = "Le budget doit être un nombre positif.";
        }
    } elseif (is_loueur()) {
        $type_loueur = $_POST['type_loueur'] ?? '';
        
        if (empty($type_loueur)) {
            $errors[] = "Le type de loueur est obligatoire.";
        }
        if (!empty($telephone) && !preg_match('/^[0-9]{10}$/', str_replace(' ', '', $telephone))) {
            $errors[] = "Le téléphone doit contenir 10 chiffres.";
        }
    }
    
    // Gestion du changement de mot de passe
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
    }
    
    // Gestion de l'upload de photo
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['photo']['type'], $allowed_types)) {
            $errors[] = "Format de photo non autorisé. Utilisez JPG, JPEG ou PNG.";
        } elseif ($_FILES['photo']['size'] > $max_size) {
            $errors[] = "La photo ne doit pas dépasser 2MB.";
        } else {
            $upload_dir = 'uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $unique_name = uniqid('profile_', true) . '.' . $file_extension;
            $photo_path = $upload_dir . $unique_name;
            
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                $errors[] = "Erreur lors de l'upload de la photo.";
                $photo_path = null;
            }
        }
    }
    
    // Si pas d'erreurs, mise à jour
    if (empty($errors)) {
        try {
            // Vérifier l'email unique (sauf pour l'utilisateur actuel)
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé par un autre compte.";
            } else {
                // Préparer la requête de mise à jour
                if (is_etudiant()) {
                    $sql = "UPDATE utilisateurs SET 
                            prenom = ?, nom = ?, email = ?, 
                            villeRecherche = ?, budget = ?";
                    $params = [$prenom, $nom, $email, $ville_recherche, $budget];
                } else {
                    $sql = "UPDATE utilisateurs SET 
                            prenom = ?, nom = ?, email = ?, 
                            telephone = ?, typeLoueur = ?";
                    $params = [$prenom, $nom, $email, $telephone, $type_loueur];
                }
                
                // Ajouter la photo si uploadée
                if ($photo_path) {
                    $sql .= ", photoDeProfil = ?";
                    $params[] = $photo_path;
                }
                
                // Ajouter le mot de passe si changé
                if (!empty($new_password)) {
                    $sql .= ", motDePasse = ?";
                    $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                }
                
                $sql .= " WHERE id = ?";
                $params[] = $user_id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                // Mettre à jour la session
                $_SESSION['user_prenom'] = $prenom;
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_email'] = $email;
                if ($photo_path) {
                    $_SESSION['user_photo'] = $photo_path;
                }
                
                $success = "Profil mis à jour avec succès !";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// Récupérer les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: logout.php');
        exit();
    }
    
    // Statistiques pour l'utilisateur
    if (is_etudiant()) {
        // Nombre de favoris
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE idEtudiant = ?");
        $stmt->execute([$user_id]);
        $nb_favoris = $stmt->fetchColumn();
        
        // Nombre de candidatures
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidatures WHERE idEtudiant = ?");
        $stmt->execute([$user_id]);
        $nb_candidatures = $stmt->fetchColumn();
        
        // Candidatures acceptées
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidatures WHERE idEtudiant = ? AND statut = 'acceptee'");
        $stmt->execute([$user_id]);
        $nb_acceptees = $stmt->fetchColumn();
        
    } else {
        // Nombre d'annonces
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM annonces WHERE idLoueur = ?");
        $stmt->execute([$user_id]);
        $nb_annonces = $stmt->fetchColumn();
        
        // Nombre d'annonces actives
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM annonces WHERE idLoueur = ? AND statut = 'active'");
        $stmt->execute([$user_id]);
        $nb_actives = $stmt->fetchColumn();
        
        // Nombre de candidatures reçues
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT c.id) 
            FROM candidatures c
            JOIN annonces a ON c.idAnnonce = a.id
            WHERE a.idLoueur = ?
        ");
        $stmt->execute([$user_id]);
        $nb_candidatures_recues = $stmt->fetchColumn();
    }
    
} catch (PDOException $e) {
    $errors[] = "Erreur lors de la récupération du profil : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - DormQuest</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/profil.css">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header__container">
            <a href="index.php" class="header__logo">
                <img src="images/logo-dormquest.png" alt="DormQuest Logo" class="header__logo-img">
                <span class="header__logo-text">DormQuest</span>
            </a>
            <nav class="header__nav">
                <?php if (is_etudiant()): ?>
                    <a href="annonces.php" class="header__nav-link">Annonces</a>
                    <a href="favoris.php" class="header__nav-link">Mes favoris</a>
                    <a href="candidatures.php" class="header__nav-link">Mes candidatures</a>
                    <a href="dashboard-etudiant.php" class="header__nav-link">Dashboard</a>
                <?php else: ?>
                    <a href="dashboard-loueur.php" class="header__nav-link">Mes annonces</a>
                    <a href="create-annonce.php" class="header__nav-link">Créer une annonce</a>
                <?php endif; ?>
                
                
                <div class="header__user">
                    <img src="<?php echo htmlspecialchars(get_user_photo()); ?>" 
                         alt="Photo de profil" 
                         class="header__user-photo"
                         onerror="this.src='images/default-avatar.png'"
                         width="100px" 
                         height="100px">
                    <span class="header__user-name"><?php echo htmlspecialchars(get_user_prenom()); ?></span>
                </div>
                <a href="logout.php" class="header__btn header__btn--logout">Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="profil-page">
        <div class="profil-page__container">
            
            <!-- En-tête -->
            <div class="profil-header">
                <div class="profil-header__content">
                    <h1 class="profil-header__title">⚙️ Mon profil</h1>
                    <p class="profil-header__subtitle">Gérez vos informations personnelles</p>
                </div>
                <div class="profil-header__badge">
                    <?php echo is_etudiant() ? '🎓 Étudiant' : '🏠 Loueur'; ?>
                </div>
            </div>

            <div class="profil-content">
                
                <!-- Colonne gauche : Statistiques -->
                <aside class="profil-sidebar">
                    
                    <!-- Photo de profil -->
                    <div class="profile-card">
                        <div class="profile-card__photo-container">
                            <img src="<?php echo htmlspecialchars($user['photoDeProfil'] ?? 'images/default-avatar.png' ); ?>" 
                                 alt="Photo de profil" 
                                 class="profile-card__photo"
                                 id="preview-photo"
                                 onerror="this.src='images/default-avatar.png'"
                                 >
                        </div>
                        <h2 class="profile-card__name">
                            <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
                        </h2>
                        <p class="profile-card__role">
                            <?php echo is_etudiant() ? '🎓 Étudiant' : '🏠 Loueur'; ?>
                        </p>
                        <div class="profile-card__date">
                            Membre depuis le <?php echo date('d/m/Y', strtotime($user['dateInscription'])); ?>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="stats-card">
                        <h3 class="stats-card__title">📊 Mes statistiques</h3>
                        <div class="stats-card__items">
                            <?php if (is_etudiant()): ?>
                                <div class="stats-card__item">
                                    <span class="stats-card__icon">⭐</span>
                                    <div class="stats-card__content">
                                        <strong><?php echo $nb_favoris; ?></strong>
                                        <span>Favori<?php echo $nb_favoris > 1 ? 's' : ''; ?></span>
                                    </div>
                                </div>
                                <div class="stats-card__item">
                                    <span class="stats-card__icon">📨</span>
                                    <div class="stats-card__content">
                                        <strong><?php echo $nb_candidatures; ?></strong>
                                        <span>Candidature<?php echo $nb_candidatures > 1 ? 's' : ''; ?></span>
                                    </div>
                                </div>
                                <div class="stats-card__item">
                                    <span class="stats-card__icon">✅</span>
                                    <div class="stats-card__content">
                                        <strong><?php echo $nb_acceptees; ?></strong>
                                        <span>Acceptée<?php echo $nb_acceptees > 1 ? 's' : ''; ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="stats-card__item">
                                    <span class="stats-card__icon">📋</span>
                                    <div class="stats-card__content">
                                        <strong><?php echo $nb_annonces; ?></strong>
                                        <span>Annonce<?php echo $nb_annonces > 1 ? 's' : ''; ?></span>
                                    </div>
                                </div>
                                <div class="stats-card__item">
                                    <span class="stats-card__icon">✅</span>
                                    <div class="stats-card__content">
                                        <strong><?php echo $nb_actives; ?></strong>
                                        <span>Active<?php echo $nb_actives > 1 ? 's' : ''; ?></span>
                                    </div>
                                </div>
                                <div class="stats-card__item">
                                    <span class="stats-card__icon">📬</span>
                                    <div class="stats-card__content">
                                        <strong><?php echo $nb_candidatures_recues; ?></strong>
                                        <span>Candidature<?php echo $nb_candidatures_recues > 1 ? 's' : ''; ?> reçue<?php echo $nb_candidatures_recues > 1 ? 's' : ''; ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="danger-zone">
                        <h3 class="danger-zone__title">⚠️ Action sensible ⚠️</h3>
                        <p class="danger-zone__text">
                            La suppression de votre compte est irréversible.
                        </p>
                        <button class="danger-zone__btn" id="delete-account-btn">
                            Supprimer mon compte
                        </button>
                    </div>
                </aside>

                <!-- Colonne droite : Formulaire -->
                <div class="profil-main">
                    
                    <!-- Messages -->
                    <?php if ($success): ?>
                        <div class="alert alert--success">
                            <strong>✅ <?php echo htmlspecialchars($success); ?></strong>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert--error">
                            <strong>⚠️ Erreurs :</strong>
                            <ul class="alert__list">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire de modification -->
                    <form method="POST" action="profil.php" class="profil-form" enctype="multipart/form-data">
                        
                        <!-- Section Informations personnelles -->
                        <div class="form-section">
                            <h2 class="form-section__title">👤 Informations personnelles</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <input type="text" id="prenom" name="prenom" 
                                           value="<?php echo htmlspecialchars($user['prenom']); ?>" 
                                           class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" id="nom" name="nom" 
                                           value="<?php echo htmlspecialchars($user['nom']); ?>" 
                                           class="form-input" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       class="form-input" required>
                            </div>

                            <div class="form-group">
                                <label for="photo" class="form-label">Photo de profil</label>
                                <input type="file" id="photo" name="photo" 
                                       class="form-input form-input--file" 
                                       accept="image/jpeg,image/png,image/jpg">
                                <small class="form-hint">JPG, PNG (Max 2MB)</small>
                            </div>
                        </div>

                        <!-- Section spécifique selon le rôle -->
                        <?php if (is_etudiant()): ?>
                            <div class="form-section">
                                <h2 class="form-section__title">🎓 Informations étudiant</h2>
                                
                                <div class="form-group">
                                    <label for="ville_recherche" class="form-label">Ville de recherche *</label>
                                    <input type="text" id="ville_recherche" name="ville_recherche" 
                                           value="<?php echo htmlspecialchars($user['villeRecherche']); ?>" 
                                           class="form-input" required>
                                </div>

                                <div class="form-group">
                                    <label for="budget" class="form-label">Budget mensuel (€) *</label>
                                    <input type="number" id="budget" name="budget" 
                                           value="<?php echo htmlspecialchars($user['budget']); ?>" 
                                           class="form-input" required min="0" step="50">
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="form-section">
                                <h2 class="form-section__title">🏠 Informations loueur</h2>
                                
                                <div class="form-group">
                                    <label for="type_loueur" class="form-label">Type de loueur *</label>
                                    <select id="type_loueur" name="type_loueur" class="form-input" required>
                                        <option value="particulier" <?php echo $user['typeLoueur'] === 'particulier' ? 'selected' : ''; ?>>Particulier</option>
                                        <option value="agence" <?php echo $user['typeLoueur'] === 'agence' ? 'selected' : ''; ?>>Agence immobilière</option>
                                        <option value="organisme" <?php echo $user['typeLoueur'] === 'organisme' ? 'selected' : ''; ?>>Organisme</option>
                                        <option value="crous" <?php echo $user['typeLoueur'] === 'crous' ? 'selected' : ''; ?>>CROUS</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" id="telephone" name="telephone" 
                                           value="<?php echo htmlspecialchars($user['telephone']); ?>" 
                                           class="form-input" placeholder="0612345678">
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Section Sécurité -->
                        <div class="form-section">
                            <h2 class="form-section__title">🔒 Modifier le mot de passe</h2>
                            <p class="form-section__desc">Laissez vide si vous ne souhaitez pas changer votre mot de passe.</p>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" id="new_password" name="new_password" 
                                           class="form-input" minlength="8">
                                    <small class="form-hint">Minimum 8 caractères</small>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                    <input type="password" id="confirm_password" name="confirm_password" 
                                           class="form-input" minlength="8">
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="form-actions form-actions--multiple">
                            <a href="<?php echo is_etudiant() ? 'dashboard-etudiant.php' : 'dashboard-loueur.php'; ?>" 
                               class="form-btn form-btn--secondary">
                                Annuler
                            </a>
                            <button type="submit" class="form-btn form-btn--primary">
                                💾 Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer footer--minimal">
        <div class="footer__container">
            <p class="footer__copyright">
                &copy; 2024 DormQuest by Nyzer. Tous droits réservés.
            </p>
        </div>
    </footer>

    <script src="js/profil.js"></script>
</body>
</html>