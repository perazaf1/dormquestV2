<?php
/**
 * register.php - Page d'inscription DormQuest
 *
 * Cette page permet aux utilisateurs (étudiants ou loueurs) de créer un compte.
 * Elle gère la validation des données, l'upload de photo de profil, et l'insertion en base de données.
 */

// Démarre la session PHP pour pouvoir gérer l'authentification
session_start();

// Vérification de sécurité : si l'utilisateur est déjà connecté,
// il n'a pas besoin de s'inscrire, on le redirige vers l'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Inclusion des fichiers de configuration nécessaires
require_once __DIR__ . '/includes/db.php';  // Connexion PDO à la base de données ($pdo)
require_once 'includes/auth.php';           // Fonctions d'authentification
/**
 * Fonction utilitaire : Vérifie si une colonne existe dans la table 'utilisateurs'
 *
 * Cette fonction interroge les métadonnées de MySQL (INFORMATION_SCHEMA) pour vérifier
 * si une colonne spécifique existe dans la table utilisateurs.
 *
 * @param string $col Nom de la colonne à vérifier
 * @return bool True si la colonne existe, false sinon
 */
$columnExists = function($col) use ($pdo) {
    // Requête sur INFORMATION_SCHEMA qui contient les métadonnées de toutes les tables
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = ?");
    $stmt->execute([$col]);
    return (bool) $stmt->fetchColumn();  // Convertit le COUNT en booléen
};

/**
 * Fonction utilitaire : Assure l'existence des colonnes pour les questions secrètes
 *
 * Cette fonction crée automatiquement les colonnes 'secret_question' et 'secret_answer_hash'
 * dans la table utilisateurs si elles n'existent pas encore. Cela permet une migration
 * automatique du schéma de base de données.
 *
 * @return null|string Retourne null en cas de succès, ou un message d'erreur en cas d'échec
 */
$ensureSecretColumnsExist = function() use ($pdo, $columnExists) {
    $toAdd = [];  // Liste des colonnes à ajouter

    // Vérification de la colonne pour stocker la question
    if (!$columnExists('secret_question')) {
        $toAdd[] = "ADD COLUMN secret_question VARCHAR(255) NULL";
    }

    // Vérification de la colonne pour stocker le hash de la réponse
    if (!$columnExists('secret_answer_hash')) {
        $toAdd[] = "ADD COLUMN secret_answer_hash VARCHAR(255) NULL";
    }

    // Si toutes les colonnes existent déjà, aucune action nécessaire
    if (empty($toAdd)) {
        return null;
    }

    try {
        // Exécution de la commande ALTER TABLE pour ajouter les colonnes manquantes
        $sql = "ALTER TABLE utilisateurs " . implode(', ', $toAdd);
        $pdo->exec($sql);
        return null;  // Succès
    } catch (PDOException $e) {
        // En cas d'erreur SQL, retourne le message d'erreur
        return $e->getMessage();
    }
};
// ============================================================================
// INITIALISATION DES VARIABLES DU FORMULAIRE
// ============================================================================

// Tableau pour stocker les messages d'erreur de validation
$errors = [];

// Message de succès (actuellement non utilisé dans ce fichier)
$success = '';

// Variables pour pré-remplir les champs du formulaire en cas d'erreur de validation
// Cela améliore l'expérience utilisateur en évitant de ressaisir toutes les données
$prenom = '';
$nom = '';
$email = '';

// Détermine le rôle par défaut : 'etudiant' ou 'loueur'
// Peut être passé via l'URL (ex: register.php?type=loueur)
$role = isset($_GET['type']) ? $_GET['type'] : 'etudiant';

// Variables utilisées pour le header (si inclus)
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

// ============================================================================
// TRAITEMENT DU FORMULAIRE D'INSCRIPTION
// ============================================================================

// Vérifie si le formulaire a été soumis via la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ------------------------------------------------------------------------
    // RÉCUPÉRATION ET NETTOYAGE DES DONNÉES DU FORMULAIRE
    // ------------------------------------------------------------------------

    // trim() retire les espaces en début et fin de chaîne
    // L'opérateur ?? (null coalescing) retourne '' si la clé n'existe pas dans $_POST
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';  // Pas de trim sur les mots de passe (espaces peuvent être intentionnels)
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? 'etudiant';

    // Données pour la question secrète (récupération mot de passe oublié)
    $secret_question = trim($_POST['secret_question'] ?? '');
    $secret_question_custom = trim($_POST['secret_question_custom'] ?? '');  // Question personnalisée
    $secret_answer = trim($_POST['secret_answer'] ?? '');

    // ------------------------------------------------------------------------
    // VALIDATION DES CHAMPS COMMUNS (obligatoires pour tous les utilisateurs)
    // ------------------------------------------------------------------------
    // Validation du prénom
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire.";
    }

    // Validation du nom
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }

    // Validation de l'email
    if (empty($email)) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var() avec FILTER_VALIDATE_EMAIL vérifie le format de l'email
        $errors[] = "L'email n'est pas valide.";
    }

    // Validation du mot de passe
    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($password) < 8) {
        // Sécurité : impose un mot de passe d'au moins 8 caractères
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    // Vérification de la confirmation du mot de passe
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // ------------------------------------------------------------------------
    // VALIDATION DE LA QUESTION SECRÈTE (pour récupération de mot de passe)
    // ------------------------------------------------------------------------

    // Tente de créer les colonnes de question secrète dans la BDD si elles n'existent pas
    $ensureErr = $ensureSecretColumnsExist();
    if ($ensureErr !== null) {
        // Si l'ajout des colonnes a échoué, affiche l'erreur SQL
        $errors[] = "Le serveur n'est pas configuré pour les questions secrètes : " . $ensureErr;
    } else {
        // Détermine la question effective : prédéfinie ou personnalisée
        $effective_secret_question = ($secret_question === 'Autre')
            ? $secret_question_custom  // Si "Autre", utilise le champ personnalisé
            : $secret_question;        // Sinon, utilise la question du menu déroulant

        // Validation de la question secrète
        if (empty($effective_secret_question)) {
            $errors[] = "La question secrète est obligatoire.";
        }

        // Validation de la réponse secrète
        if (empty($secret_answer)) {
            $errors[] = "La réponse à la question secrète est obligatoire.";
        }
    }

    // ------------------------------------------------------------------------
    // VALIDATION SPÉCIFIQUE SELON LE RÔLE (étudiant ou loueur)
    // ------------------------------------------------------------------------
    // Validation pour les ÉTUDIANTS
    if ($role === 'etudiant') {
        $ville_recherche = trim($_POST['ville_recherche'] ?? '');
        $budget = trim($_POST['budget'] ?? '');

        // Vérification de la ville de recherche
        if (empty($ville_recherche)) {
            $errors[] = "La ville de recherche est obligatoire.";
        }

        // Vérification du budget : doit être un nombre positif
        if (empty($budget) || !is_numeric($budget) || $budget <= 0) {
            $errors[] = "Le budget doit être un nombre positif.";
        }
    }
    // Validation pour les LOUEURS
    elseif ($role === 'loueur') {
        $type_loueur = $_POST['type_loueur'] ?? '';
        $telephone = trim($_POST['telephone'] ?? '');

        // Vérification du type de loueur (particulier, agence, organisme, crous)
        if (empty($type_loueur)) {
            $errors[] = "Le type de loueur est obligatoire.";
        }

        // Vérification du numéro de téléphone
        if (empty($telephone)) {
            $errors[] = "Le numéro de téléphone est obligatoire.";
        } elseif (!preg_match('/^[0-9]{10}$/', str_replace(' ', '', $telephone))) {
            // Regex : vérifie exactement 10 chiffres (après suppression des espaces)
            $errors[] = "Le numéro de téléphone doit contenir 10 chiffres.";
        }
    }
    
    // ------------------------------------------------------------------------
    // GESTION DE L'UPLOAD DE PHOTO DE PROFIL (optionnel)
    // ------------------------------------------------------------------------

    $photo_path = null;  // Chemin où sera stockée la photo

    // Vérifie si un fichier a été uploadé avec succès
    // UPLOAD_ERR_OK signifie qu'il n'y a pas eu d'erreur lors de l'upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

        // Types MIME autorisés pour les photos
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2 Mo (en octets)

        // Validation du type de fichier
        if (!in_array($_FILES['photo']['type'], $allowed_types)) {
            $errors[] = "Format de photo non autorisé. Utilisez JPG, JPEG ou PNG.";
        }
        // Validation de la taille du fichier
        elseif ($_FILES['photo']['size'] > $max_size) {
            $errors[] = "La photo ne doit pas dépasser 2MB.";
        }
        // Si la validation passe, traite l'upload
        else {
            $upload_dir = 'uploads/profiles/';

            // Crée le dossier de destination s'il n'existe pas
            // 0777 = permissions complètes (lecture/écriture/exécution pour tous)
            // true = création récursive (crée aussi les dossiers parents)
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Génère un nom de fichier unique pour éviter les conflits
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $unique_name = uniqid('profile_', true) . '.' . $file_extension;
            $photo_path = $upload_dir . $unique_name;

            // Déplace le fichier du dossier temporaire vers sa destination finale
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                $errors[] = "Erreur lors de l'upload de la photo.";
                $photo_path = null;  // Réinitialise en cas d'échec
            }
        }
    }
    
    // ------------------------------------------------------------------------
    // INSERTION EN BASE DE DONNÉES (si aucune erreur de validation)
    // ------------------------------------------------------------------------

    if (empty($errors)) {
        try {
            // Vérification de l'unicité de l'email dans la base de données
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                // L'email existe déjà, ajout d'une erreur
                $errors[] = "Cet email est déjà utilisé.";
            } else {
                // Hachage sécurisé du mot de passe avec bcrypt
                // PASSWORD_DEFAULT utilise actuellement bcrypt (coût par défaut)
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Redéfinition de la fonction columnExists (note: déjà définie plus haut)
                // Cette redéfinition est nécessaire car la fonction précédente n'est pas dans le même scope
                $columnExists = function($col) use ($pdo) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = ?");
                    $stmt->execute([$col]);
                    return (bool) $stmt->fetchColumn();
                };

                // ----------------------------------------------------------------
                // CONSTRUCTION DYNAMIQUE DE LA REQUÊTE SQL INSERT
                // ----------------------------------------------------------------
                // Cette approche permet de gérer différentes versions du schéma BDD
                // (camelCase vs snake_case, colonnes optionnelles, etc.)

                // Colonnes obligatoires pour tous les utilisateurs
                $cols = ['prenom', 'nom', 'email', 'motDePasse', 'role'];
                $placeholders = ['?', '?', '?', '?', '?'];
                $values = [$prenom, $nom, $email, $password_hash, $role];

                // Ajout de la colonne photo si une photo a été uploadée
                // Gère deux noms possibles : camelCase et snake_case
                if ($photo_path) {
                    if ($columnExists('photoDeProfil')) {
                        $cols[] = 'photoDeProfil';  // Nom en camelCase
                    } elseif ($columnExists('photo_de_profil')) {
                        $cols[] = 'photo_de_profil';  // Nom en snake_case
                    }
                    $placeholders[] = '?';
                    $values[] = $photo_path;
                }

                // Colonnes spécifiques pour les ÉTUDIANTS
                if ($role === 'etudiant') {
                    // Gestion de la ville de recherche (deux noms possibles)
                    if ($columnExists('villeRecherche')) {
                        $cols[] = 'villeRecherche';
                        $placeholders[] = '?';
                        $values[] = $ville_recherche;
                    } elseif ($columnExists('ville_recherche')) {
                        $cols[] = 'ville_recherche';
                        $placeholders[] = '?';
                        $values[] = $ville_recherche;
                    }

                    // Ajout du budget
                    if ($columnExists('budget')) {
                        $cols[] = 'budget';
                        $placeholders[] = '?';
                        $values[] = $budget;
                    }
                }
                // Colonnes spécifiques pour les LOUEURS
                elseif ($role === 'loueur') {
                    // Ajout du téléphone
                    if ($columnExists('telephone')) {
                        $cols[] = 'telephone';
                        $placeholders[] = '?';
                        $values[] = $telephone;
                    }

                    // Gestion du type de loueur (deux noms possibles)
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

                // Note : dateInscription n'est pas ajoutée ici car elle utilise
                // la valeur par défaut CURRENT_TIMESTAMP définie dans le schéma de la table

                // Ajout de la question secrète si les données sont présentes
                if (!empty($effective_secret_question) && !empty($secret_answer)) {
                    // Vérifie que les colonnes existent dans la table
                    if ($columnExists('secret_question') && $columnExists('secret_answer_hash')) {
                        // Ajoute la question
                        $cols[] = 'secret_question';
                        $placeholders[] = '?';
                        $values[] = $effective_secret_question;

                        // Ajoute le hash de la réponse (sécurisé comme le mot de passe)
                        $cols[] = 'secret_answer_hash';
                        $placeholders[] = '?';
                        $values[] = password_hash($secret_answer, PASSWORD_DEFAULT);
                    }
                }

                // ----------------------------------------------------------------
                // CONSTRUCTION ET EXÉCUTION DE LA REQUÊTE SQL FINALE
                // ----------------------------------------------------------------

                // Génère les placeholders SQL (tous des '?')
                $placeholders_sql = [];
                foreach ($placeholders as $ph) {
                    $placeholders_sql[] = '?';
                }

                // Construit la requête INSERT complète
                // Exemple: INSERT INTO utilisateurs (prenom, nom, email, motDePasse, role, photoDeProfil, villeRecherche, budget, secret_question, secret_answer_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                $sql = "INSERT INTO utilisateurs (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders_sql) . ")";

                // Prépare et exécute la requête avec les valeurs
                $stmt = $pdo->prepare($sql);
                $stmt->execute($values);

                // Succès : redirection vers la page de connexion avec un message de succès
                // Le paramètre GET 'success=registered' permettra d'afficher un message de confirmation
                header('Location: login.php?success=registered');
                exit();  // Important : arrête l'exécution du script après la redirection
            }
        } catch (PDOException $e) {
            // Capture toutes les erreurs SQL (contraintes, erreurs de connexion, etc.)
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
    <!-- Conteneur principal de la page d'inscription -->
    <div class="login-container">
        <div class="login-box">
            <!-- En-tête du formulaire -->
            <div class="login-header">
                <h2>Créer un compte</h2>
                <p>Rejoignez DormQuest et trouvez votre logement idéal</p>
            </div>

            <!-- Affichage des messages d'erreur (s'il y en a) -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                <!-- htmlspecialchars() protège contre les attaques XSS -->
                <div><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Affichage du message de succès (s'il y en a un) -->
            <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!--
                Formulaire d'inscription
                - method="POST" : envoie les données de manière sécurisée
                - enctype="multipart/form-data" : nécessaire pour l'upload de fichiers (photo)
            -->
            <form method="POST" action="register.php" class="login-form" enctype="multipart/form-data">

                <!-- Sélection du rôle : Étudiant ou Loueur -->
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

                <!-- Champ prénom -->
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <!-- value pré-rempli avec htmlspecialchars() pour protection XSS -->
                    <input type="text" id="prenom" name="prenom" required
                        value="<?php echo htmlspecialchars($prenom); ?>">
                </div>

                <!-- Champ nom -->
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($nom); ?>">
                </div>

                <!-- Champ email -->
                <div class="form-group">
                    <label for="email">Email *</label>
                    <!-- type="email" ajoute une validation basique côté client -->
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="votre.email@exemple.com">
                </div>

                <!-- Champ mot de passe -->
                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <!-- minlength="8" valide côté client, mais validation serveur est nécessaire -->
                    <input type="password" id="password" name="password" required minlength="8">
                    <span class="password-toggle"><i class="fa-regular fa-eye-slash"></i></span>
                    <small>Minimum 8 caractères</small>
                </div>

                <!-- Confirmation du mot de passe -->
                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
                </div>

                <!-- Upload de photo de profil (optionnel) -->
                <div class="form-group">
                    <label for="photo">Photo de profil (facultatif)</label>
                    <!-- accept limite les types de fichiers côté client -->
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg">
                </div>

                <!-- Question secrète pour récupération de mot de passe -->
                <div class="form-group">
                    <label for="secret_question">Question secrète *</label>
                    <select id="secret_question" name="secret_question" required>
                        <option value="">-- Choisir une question --</option>
                        <!-- Chaque option est pré-sélectionnée si elle a été choisie précédemment -->
                        <option value="Quel est le nom de jeune fille de votre mère?" <?php echo (isset($_POST['secret_question']) && $_POST['secret_question'] === "Quel est le nom de jeune fille de votre mère?") ? 'selected' : ''; ?>>Quel est le nom de jeune fille de votre mère?</option>
                        <option value="Quel est le nom de votre premier animal?" <?php echo (isset($_POST['secret_question']) && $_POST['secret_question'] === "Quel est le nom de votre premier animal?") ? 'selected' : ''; ?>>Quel est le nom de votre premier animal?</option>
                        <option value="Quel est le code postal de votre ville de naissance?" <?php echo (isset($_POST['secret_question']) && $_POST['secret_question'] === "Quel est le code postal de votre ville de naissance?") ? 'selected' : ''; ?>>Quel est le code postal de votre ville de naissance?</option>
                        <option value="Quel est le nom de votre école primaire?" <?php echo (isset($_POST['secret_question']) && $_POST['secret_question'] === "Quel est le nom de votre école primaire?") ? 'selected' : ''; ?>>Quel est le nom de votre école primaire?</option>
                        <!-- Option "Autre" permet de saisir une question personnalisée -->
                        <option value="Autre" <?php echo (isset($_POST['secret_question']) && $_POST['secret_question'] === "Autre") ? 'selected' : ''; ?>>Autre (écrire ma propre question)</option>
                    </select>
                </div>

                <!-- Champ conditionnel : affiché uniquement si "Autre" est sélectionné -->
                <div class="form-group" id="secret-question-custom-group" style="display: <?php echo (isset($_POST['secret_question']) && $_POST['secret_question'] === 'Autre') ? 'block' : 'none'; ?>;">
                    <label for="secret_question_custom">Votre question secrète *</label>
                    <input type="text" id="secret_question_custom" name="secret_question_custom" value="<?php echo isset($_POST['secret_question_custom']) ? htmlspecialchars($_POST['secret_question_custom']) : ''; ?>" placeholder="Saisissez votre question">
                </div>

                <!-- Réponse à la question secrète -->
                <div class="form-group">
                    <label for="secret_answer">Réponse à la question secrète *</label>
                    <input type="text" id="secret_answer" name="secret_answer" required value="<?php echo isset($_POST['secret_answer']) ? htmlspecialchars($_POST['secret_answer']) : ''; ?>" placeholder="Votre réponse">
                </div>

                <!-- ======================================== -->
                <!-- CHAMPS SPÉCIFIQUES POUR LES ÉTUDIANTS -->
                <!-- ======================================== -->
                <!-- Ces champs sont affichés/cachés dynamiquement via JavaScript selon le rôle sélectionné -->
                <div id="etudiant-fields" style="display: <?php echo ($role === 'etudiant') ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label for="ville_recherche">Ville de recherche *</label>
                        <input type="text" id="ville_recherche" name="ville_recherche"
                            value="<?php echo isset($_POST['ville_recherche']) ? htmlspecialchars($_POST['ville_recherche']) : ''; ?>"
                            placeholder="Ex: Paris, Lyon">
                    </div>
                    <div class="form-group">
                        <label for="budget">Budget mensuel (€) *</label>
                        <!-- type="number" avec min et step pour une meilleure UX -->
                        <input type="number" id="budget" name="budget"
                            value="<?php echo isset($_POST['budget']) ? htmlspecialchars($_POST['budget']) : ''; ?>"
                            min="0" step="50">
                    </div>
                </div>

                <!-- ======================================== -->
                <!-- CHAMPS SPÉCIFIQUES POUR LES LOUEURS -->
                <!-- ======================================== -->
                <div id="loueur-fields" style="display: <?php echo ($role === 'loueur') ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label for="type_loueur">Type de loueur *</label>
                        <select id="type_loueur" name="type_loueur">
                            <option value="">-- Sélectionner --</option>
                            <!-- Les 4 types de loueurs possibles -->
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
                        <!-- type="tel" pour un clavier numérique sur mobile -->
                        <input type="tel" id="telephone" name="telephone"
                            value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>"
                            placeholder="0612345678">
                    </div>
                </div>

                <!-- Bouton de soumission du formulaire -->
                <button type="submit" class="btn-submit">Créer mon compte</button>

                <!-- Lien vers la page de connexion -->
                <div class="login-footer">
                    <p>Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Script JavaScript pour gérer l'affichage dynamique des champs selon le rôle -->
    <script src="js/register.js"></script>
</body>

</html>