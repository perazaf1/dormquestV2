<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';

$errors = [];
$success = '';
$validToken = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';

// Variables pour le header
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

// Vérifier la validité du token
if ($token !== '') {
    try {
        $hashedToken = hash('sha256', $token);
        $stmt = $pdo->prepare("
            SELECT pr.user_id, pr.expires_at, u.email 
            FROM password_resets pr 
            JOIN utilisateurs u ON pr.user_id = u.id 
            WHERE pr.token = ? AND pr.expires_at > NOW()
        ");
        $stmt->execute([$hashedToken]);
        $reset = $stmt->fetch();

        if ($reset) {
            $validToken = true;
        } else {
            $errors[] = "Ce lien est invalide ou a expiré. Veuillez refaire une demande.";
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur interne : " . $e->getMessage();
    }
} else {
    $errors[] = "Token manquant.";
}

// Traitement du formulaire de nouveau mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    // Validation du mot de passe
    if ($password === '') {
        $errors[] = "Veuillez entrer un mot de passe.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
    }

    if ($password !== $passwordConfirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        try {
            // Mettre à jour le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET motDePasse = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $reset['user_id']]);

            // Supprimer le token utilisé
            $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$reset['user_id']]);

            $success = "✅ Votre mot de passe a été modifié avec succès !";
            $validToken = false; // Cacher le formulaire après succès
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Réinitialiser le mot de passe - DormQuest</title>
<link rel="stylesheet" href="css/style.css">
<style>
body {
  margin:0; padding:0; font-family:Arial,Helvetica,sans-serif; background:#f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh;
}
.login-box {
  background:white; width:360px; padding:30px; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.1);
}
.login-box h2 { text-align:center; margin-bottom:10px; }
.login-box p { text-align:center; color:#666; font-size:14px; margin-bottom:20px; }
.login-box input { width:100%; padding:12px; margin:8px 0; border-radius:5px; border:1px solid #ccc; box-sizing:border-box; }
.login-box button { width:100%; padding:12px; margin-top:10px; border:none; border-radius:5px; background:#007bff; color:white; cursor:pointer; font-size:16px; }
.login-box button:hover { background:#005fcc; }
.login-box .footer { text-align:center; margin-top:15px; font-size:14px; }
.login-box .footer a { color:#007bff; text-decoration:none; }
.login-box .footer a:hover { text-decoration:underline; }
.alert { margin:10px 0; padding:10px; border-radius:5px; font-size:14px; }
.alert-error { background:#f8d7da; color:#842029; }
.alert-success { background:#d1e7dd; color:#0f5132; }
.password-rules { font-size:12px; color:#666; margin:5px 0 15px 0; }
.password-rules li { margin:3px 0; }
</style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="login-box">
<h2>🔑 Nouveau mot de passe</h2>

<?php if(!empty($errors)): ?>
<div class="alert alert-error">
  <?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
</div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<div class="footer">
<a href="login.php">→ Se connecter</a>
</div>
<?php endif; ?> 

<?php if($validToken && !$success): ?>
<p>Choisissez votre nouveau mot de passe.</p>

<form method="POST" action="">
<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
<input type="password" name="password" placeholder="Nouveau mot de passe" required>
<input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required>

<ul class="password-rules">
  <li>Au moins 8 caractères</li>
  <li>Au moins une majuscule</li>
  <li>Au moins un chiffre</li>
</ul>

<button type="submit">Réinitialiser le mot de passe</button>
</form>
<?php endif; ?>

<?php if(!$validToken && !$success): ?>
<div class="footer">
<a href="mot-de-passe-oublie.php">← Refaire une demande</a>
</div>
<?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>