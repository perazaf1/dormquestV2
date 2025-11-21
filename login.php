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
                    setcookie('remember_token', $token, time() + 86400*30, '/', '', false, true);
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
<title>Connexion - DormQuest</title>
<style>
body {
  margin:0; padding:0; font-family:Arial,Helvetica,sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh;
}
.login-box {
  background:white; width:360px; padding:30px; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.1);
}
.login-box h2 { text-align:center; margin-bottom:20px; }
.login-box input { width:100%; padding:12px; margin:8px 0; border-radius:5px; border:1px solid #ccc; }
.login-box button { width:100%; padding:12px; margin-top:10px; border:none; border-radius:5px; background:#007bff; color:white; cursor:pointer; font-size:16px; }
.login-box button:hover { background:#005fcc; }
.login-box .footer { text-align:center; margin-top:10px; font-size:14px; }
.login-box .footer a { color:#007bff; text-decoration:none; }
.login-box .footer a:hover { text-decoration:underline; }
.alert { margin:10px 0; padding:10px; border-radius:5px; }
.alert-error { background:#f8d7da; color:#842029; }
.alert-success { background:#d1e7dd; color:#0f5132; }
</style>
</head>
<body>

<div class="login-box">
<h2>Connexion DormQuest</h2>

<?php if(!empty($errors)): ?>
<div class="alert alert-error">
  <?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
</div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" action="login.php">
<input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($email); ?>">
<input type="password" name="password" placeholder="Mot de passe" required>
<label><input type="checkbox" name="remember_me" <?php echo $remember_me ? 'checked' : ''; ?>> Se souvenir de moi</label>
<button type="submit">Se connecter</button>
</form>

<div class="footer">
Pas de compte ? <a href="register.php">Créer un compte</a>
</div>
</div>

</body>
</html>
