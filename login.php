<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_role'] === 'etudiant' ? 'dashboard-etudiant.php' : 'dashboard-loueur.php'));
    exit();
}

// Connexion BDD
require_once __DIR__ . '/includes/db.php';

// Initialisation
$errors = [];
$success = '';
$email = '';
$remember_me = false;

// Variables pour le header
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

if (isset($_GET['success']) && $_GET['success'] === 'registered') {
    $success = "🎉 Inscription réussie ! Vous pouvez maintenant vous connecter.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    if ($email === '') {
        $errors[] = "Veuillez saisir votre email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format de l'email incorrect.";
    }

    if ($password === '') {
        $errors[] = "Veuillez entrer votre mot de passe.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, prenom, nom, email, motDePasse, role FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['motDePasse'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['login_time'] = time();

                $pdo->prepare("UPDATE utilisateurs SET derniereConnexion = NOW() WHERE id = ?")->execute([$user['id']]);

                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + 86400 * 30, '/', '', false, true);
                    // Stockage token à implémenter
                }

                header('Location: ' . ($user['role'] === 'etudiant' ? 'dashboard-etudiant.php' : 'dashboard-loueur.php'));
                exit();
            } else {
                $errors[] = "❌ Identifiants invalides.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur interne : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <title>Connexion - DormQuest</title>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>Connexion DormQuest</h2>
                <p>Accédez à votre compte</p>
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

            <form method="POST" action="login.php" class="login-form">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email"
                           id="email"
                           name="email"
                           placeholder="votre.email@exemple.com"
                           required
                           value="<?php echo htmlspecialchars($email); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Entrez votre mot de passe"
                           required>
                    <span class="password-toggle">👁️‍🗨️</span>
                </div>

                <label class="remember-me">
                    <input type="checkbox"
                           name="remember_me"
                           <?php echo $remember_me ? 'checked' : ''; ?>>
                    <span>Se souvenir de moi</span>
                </label>
                <div class="forget-password">
                    <a href="reinitialiser-mdp.php">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-submit">Se connecter</button>
            </form>

            <div class="login-footer">
                <p>Pas de compte ? <a href="register.php">Créer un compte</a></p>
            </div>
        </div>
    </div>

    <script src="js/login.js"></script>
</body>

</html>