<?php
// register.php - Page d'inscription DormQuest
session_start();

// Si déjà connecté, redirection selon le rôle
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'etudiant') {
        header('Location: dashboard-etudiant.php');
        exit();
    } elseif ($_SESSION['user_role'] === 'loueur') {
        header('Location: dashboard-loueur.php');
        exit();
    }
}

// Connexion à la base de données (chemin absolu basé sur ce fichier)
require_once __DIR__ . '/includes/db.php';

// Variables pour pré-remplir le formulaire en cas d'erreur
$errors = [];
$success = '';
$prenom = '';
$nom = '';
$email = '';
$role = isset($_GET['type']) ? $_GET['type'] : 'etudiant';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? 'etudiant';
    
    // Validation des champs communs
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }
    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Validation spécifique selon le rôle
    if ($role === 'etudiant') {
        $ville_recherche = trim($_POST['ville_recherche'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        
        if (empty($ville_recherche)) {
            $errors[] = "La ville de recherche est obligatoire.";
        }
        if (empty($budget) || !is_numeric($budget) || $budget <= 0) {
            $errors[] = "Le budget doit être un nombre positif.";
        }
    } elseif ($role === 'loueur') {
        $type_loueur = $_POST['type_loueur'] ?? '';
        $telephone = trim($_POST['telephone'] ?? '');
        
        if (empty($type_loueur)) {
            $errors[] = "Le type de loueur est obligatoire.";
        }
        if (empty($telephone)) {
            $errors[] = "Le numéro de téléphone est obligatoire.";
        } elseif (!preg_match('/^[0-9]{10}$/', str_replace(' ', '', $telephone))) {
            $errors[] = "Le numéro de téléphone doit contenir 10 chiffres.";
        }
    }
    
    // Gestion de l'upload de photo (optionnel)
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
    
    // Si pas d'erreurs, traitement de l'inscription
    if (empty($errors)) {
        try {
            // Vérification email unique
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé.";
            } else {
                // Hash du mot de passe
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertion dans la table utilisateurs (adaptée à ta structure)
                if ($role === 'etudiant') {
                    $stmt = $pdo->prepare("
                        INSERT INTO utilisateurs 
                        (prenom, nom, email, motDePasse, role, photoDeProfil, villeRecherche, budget, dateInscription) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $prenom, 
                        $nom, 
                        $email, 
                        $password_hash, 
                        $role, 
                        $photo_path,
                        $ville_recherche,
                        $budget
                    ]);
                } elseif ($role === 'loueur') {
                    $stmt = $pdo->prepare("
                        INSERT INTO utilisateurs 
                        (prenom, nom, email, motDePasse, role, photoDeProfil, telephone, typeLoueur, dateInscription) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $prenom, 
                        $nom, 
                        $email, 
                        $password_hash, 
                        $role, 
                        $photo_path,
                        $telephone,
                        $type_loueur
                    ]);
                }
                
                // Redirection vers la page de connexion avec message de succès
                header('Location: login.php?success=registered');
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - DormQuest</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
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
                <a href="index.php" class="header__nav-link">Accueil</a>
                <a href="login.php" class="header__btn header__btn--login">Connexion</a>
            </nav>
        </div>
    </header>

    <!-- Formulaire d'inscription -->
    <main class="form-page">
        <div class="form-container">
            <div class="form-header">
                <h1 class="form-header__title">Créer un compte</h1>
                <p class="form-header__subtitle">Rejoignez DormQuest et trouvez votre logement idéal</p>
            </div>

            <!-- Messages d'erreur -->
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

            <!-- Message de succès -->
            <?php if ($success): ?>
                <div class="alert alert--success">
                    <strong>✅ <?php echo htmlspecialchars($success); ?></strong>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="form" enctype="multipart/form-data">
                
                <!-- Choix du rôle -->
                <div class="form-group form-group--role">
                    <label class="form-label">Je suis :</label>
                    <div class="form-role">
                        <label class="form-role__option">
                            <input type="radio" name="role" value="etudiant" 
                                   <?php echo ($role === 'etudiant') ? 'checked' : ''; ?> 
                                   class="form-role__input" required>
                            <span class="form-role__card">
                                <span class="form-role__icon">🎓</span>
                                <span class="form-role__text">Étudiant</span>
                                <span class="form-role__desc">Je cherche un logement</span>
                            </span>
                        </label>
                        <label class="form-role__option">
                            <input type="radio" name="role" value="loueur" 
                                   <?php echo ($role === 'loueur') ? 'checked' : ''; ?> 
                                   class="form-role__input" required>
                            <span class="form-role__card">
                                <span class="form-role__icon">🏠</span>
                                <span class="form-role__text">Loueur</span>
                                <span class="form-role__desc">Je propose un logement</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Informations générales -->
                <div class="form-section">
                    <h2 class="form-section__title">Informations personnelles</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prenom" class="form-label">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" 
                                   value="<?php echo htmlspecialchars($prenom); ?>" 
                                   class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text" id="nom" name="nom" 
                                   value="<?php echo htmlspecialchars($nom); ?>" 
                                   class="form-input" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email); ?>" 
                               class="form-input" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" id="password" name="password" 
                                   class="form-input" required minlength="8">
                            <small class="form-hint">Minimum 8 caractères</small>
                        </div>
                        <div class="form-group">
                            <label for="password_confirm" class="form-label">Confirmer le mot de passe *</label>
                            <input type="password" id="password_confirm" name="password_confirm" 
                                   class="form-input" required minlength="8">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="photo" class="form-label">Photo de profil (facultatif)</label>
                        <input type="file" id="photo" name="photo" 
                               class="form-input form-input--file" 
                               accept="image/jpeg,image/png,image/jpg">
                        <small class="form-hint">Formats acceptés : JPG, PNG (Max 2MB)</small>
                    </div>
                </div>

                <!-- Champs spécifiques étudiant -->
                <div class="form-section form-section--etudiant" id="etudiant-fields" 
                     style="display: <?php echo ($role === 'etudiant') ? 'block' : 'none'; ?>;">
                    <h2 class="form-section__title">Informations de recherche</h2>
                    
                    <div class="form-group">
                        <label for="ville_recherche" class="form-label">Ville de recherche *</label>
                        <input type="text" id="ville_recherche" name="ville_recherche" 
                               value="<?php echo isset($_POST['ville_recherche']) ? htmlspecialchars($_POST['ville_recherche']) : ''; ?>" 
                               class="form-input" placeholder="Ex: Paris, Lyon, Toulouse...">
                    </div>

                    <div class="form-group">
                        <label for="budget" class="form-label">Budget mensuel (€) *</label>
                        <input type="number" id="budget" name="budget" 
                               value="<?php echo isset($_POST['budget']) ? htmlspecialchars($_POST['budget']) : ''; ?>" 
                               class="form-input" min="0" step="50" placeholder="Ex: 500">
                    </div>
                </div>

                <!-- Champs spécifiques loueur -->
                <div class="form-section form-section--loueur" id="loueur-fields" 
                     style="display: <?php echo ($role === 'loueur') ? 'block' : 'none'; ?>;">
                    <h2 class="form-section__title">Informations professionnelles</h2>
                    
                    <div class="form-group">
                        <label for="type_loueur" class="form-label">Type de loueur *</label>
                        <select id="type_loueur" name="type_loueur" class="form-input">
                            <option value="">-- Sélectionner --</option>
                            <option value="particulier" <?php echo (isset($_POST['type_loueur']) && $_POST['type_loueur'] === 'particulier') ? 'selected' : ''; ?>>Particulier</option>
                            <option value="agence" <?php echo (isset($_POST['type_loueur']) && $_POST['type_loueur'] === 'agence') ? 'selected' : ''; ?>>Agence immobilière</option>
                            <option value="organisme" <?php echo (isset($_POST['type_loueur']) && $_POST['type_loueur'] === 'organisme') ? 'selected' : ''; ?>>Organisme</option>
                            <option value="crous" <?php echo (isset($_POST['type_loueur']) && $_POST['type_loueur'] === 'crous') ? 'selected' : ''; ?>>CROUS</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="telephone" class="form-label">Téléphone *</label>
                        <input type="tel" id="telephone" name="telephone" 
                               value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>" 
                               class="form-input" placeholder="Ex: 0612345678">
                        <small class="form-hint">10 chiffres sans espaces</small>
                    </div>
                </div>

                <!-- Bouton de soumission -->
                <div class="form-actions">
                    <button type="submit" class="form-btn form-btn--primary">
                        Créer mon compte
                    </button>
                </div>

                <p class="form-footer">
                    Vous avez déjà un compte ? 
                    <a href="login.php" class="form-link">Se connecter</a>
                </p>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer footer--minimal">
        <div class="footer__container">
            <p class="footer__copyright">
                &copy; 2025 DormQuest by Nyzer. Tous droits réservés.
            </p>
        </div>
    </footer>

    <script src="js/register.js"></script>
</body>
</html>