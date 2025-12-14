<?php
// register.php - Page d'inscription DormQuest
session_start();

// Si déjà connecté, redirection vers la page d'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Connexion à la base de données (chemin absolu basé sur ce fichier)
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';
// Variables pour pré-remplir le formulaire en cas d'erreur
$errors = [];
$success = '';
$prenom = '';
$nom = '';
$email = '';
$role = isset($_GET['type']) ? $_GET['type'] : 'etudiant';

// Variables pour le header
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

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

                // Helper: check if a column exists in `utilisateurs`
                $columnExists = function($col) use ($pdo) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = ?");
                    $stmt->execute([$col]);
                    return (bool) $stmt->fetchColumn();
                };

                // Build insert dynamically depending on existing columns
                $cols = ['prenom', 'nom', 'email', 'motDePasse', 'role'];
                $placeholders = ['?', '?', '?', '?', '?'];
                $values = [$prenom, $nom, $email, $password_hash, $role];

                // photo column (camelCase or snake_case)
                if ($photo_path) {
                    if ($columnExists('photoDeProfil')) {
                        $cols[] = 'photoDeProfil';
                    } elseif ($columnExists('photo_de_profil')) {
                        $cols[] = 'photo_de_profil';
                    }
                    $placeholders[] = '?';
                    $values[] = $photo_path;
                }

                if ($role === 'etudiant') {
                    // ville column could be 'villeRecherche' or 'ville_recherche'
                    if ($columnExists('villeRecherche')) {
                        $cols[] = 'villeRecherche';
                        $placeholders[] = '?';
                        $values[] = $ville_recherche;
                    } elseif ($columnExists('ville_recherche')) {
                        $cols[] = 'ville_recherche';
                        $placeholders[] = '?';
                        $values[] = $ville_recherche;
                    }

                    if ($columnExists('budget')) {
                        $cols[] = 'budget';
                        $placeholders[] = '?';
                        $values[] = $budget;
                    }
                } elseif ($role === 'loueur') {
                    if ($columnExists('telephone')) {
                        $cols[] = 'telephone';
                        $placeholders[] = '?';
                        $values[] = $telephone;
                    }

                    if ($columnExists('typeLoueur')) {
                        $cols[] = 'typeLoueur';
                        $placeholders[] = '?';
                        $values[] = $type_loueur;
                    } elseif ($columnExists('type_loueur')) {
                        $cols[] = 'type_loueur';
                        $placeholders[] = '?';
                        $values[] = $type_loueur;
                    }
                }

                // dateInscription will use DB default CURRENT_TIMESTAMP

                // Build final SQL
                $placeholders_sql = [];
                foreach ($placeholders as $ph) {
                    $placeholders_sql[] = '?';
                }

                $sql = "INSERT INTO utilisateurs (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders_sql) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($values);
                
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
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>Créer un compte</h2>
                <p>Rejoignez DormQuest et trouvez votre logement idéal</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                <div><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="login-form" enctype="multipart/form-data">

                <div class="form-group">
                    <label class="form-label">Je suis :</label>
                    <div style="display:flex;gap:10px;">
                        <label style="display:flex;align-items:center;gap:6px;">
                            <input type="radio" name="role" value="etudiant"
                                <?php echo ($role === 'etudiant') ? 'checked' : ''; ?>>
                            <span>Étudiant</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;">
                            <input type="radio" name="role" value="loueur"
                                <?php echo ($role === 'loueur') ? 'checked' : ''; ?>>
                            <span>Loueur</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom" required
                        value="<?php echo htmlspecialchars($prenom); ?>">
                </div>

                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($nom); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="votre.email@exemple.com">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <span class="password-toggle"><i class="fa-regular fa-eye-slash"></i></span>
                    <small>Minimum 8 caractères</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="photo">Photo de profil (facultatif)</label>
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg">
                </div>

                <div id="etudiant-fields" style="display: <?php echo ($role === 'etudiant') ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label for="ville_recherche">Ville de recherche *</label>
                        <input type="text" id="ville_recherche" name="ville_recherche"
                            value="<?php echo isset($_POST['ville_recherche']) ? htmlspecialchars($_POST['ville_recherche']) : ''; ?>"
                            placeholder="Ex: Paris, Lyon">
                    </div>
                    <div class="form-group">
                        <label for="budget">Budget mensuel (€) *</label>
                        <input type="number" id="budget" name="budget"
                            value="<?php echo isset($_POST['budget']) ? htmlspecialchars($_POST['budget']) : ''; ?>"
                            min="0" step="50">
                    </div>
                </div>

                <div id="loueur-fields" style="display: <?php echo ($role === 'loueur') ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label for="type_loueur">Type de loueur *</label>
                        <select id="type_loueur" name="type_loueur">
                            <option value="">-- Sélectionner --</option>
                            <option value="particulier"
                                <?php echo (isset($_POST['type_loueur']) && $_POST['type_loueur'] === 'particulier') ? 'selected' : ''; ?>>
                                Particulier</option>
                            <option value="agence"
                                <?php echo (isset($_POST['type_loueur']) && $_POST['type_loueur'] === 'agence') ? 'selected' : ''; ?>>
                                Agence immobilière</option>
                            <option value="organisme"
                                <?php echo (isset($_POST['type_loueur']) && $_POST['type_loueur'] === 'organisme') ? 'selected' : ''; ?>>
                                Organisme</option>
                            <option value="crous"
                                <?php echo (isset($_POST['type_loueur']) && $_POST['type_loueur'] === 'crous') ? 'selected' : ''; ?>>
                                CROUS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone *</label>
                        <input type="tel" id="telephone" name="telephone"
                            value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>"
                            placeholder="0612345678">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Créer mon compte</button>

                <div class="login-footer">
                    <p>Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="js/register.js"></script>
</body>

</html>