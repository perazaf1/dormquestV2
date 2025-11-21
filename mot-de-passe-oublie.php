<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/includes/db.php';

$errors = [];
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validation
    if ($email === '') {
        $errors[] = "Veuillez saisir votre email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format de l'email incorrect.";
    }

    if (empty($errors)) {
        try {
            // Vérifier si l'email existe
            $stmt = $pdo->prepare("SELECT id, prenom, email FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Générer un token sécurisé
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Supprimer les anciens tokens pour cet utilisateur
                $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);

                // Insérer le nouveau token
                $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], hash('sha256', $token), $expiry]);

                // Construire le lien de réinitialisation
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reinitialiser-mdp.php?token=" . $token;

                // Envoyer l'email
                $to = $user['email'];
                $subject = "DormQuest - Réinitialisation de votre mot de passe";
                $message = "
                <html>
                <head>
                    <title>Réinitialisation de mot de passe</title>
                </head>
                <body>
                    <h2>Bonjour " . htmlspecialchars($user['prenom']) . ",</h2>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe sur DormQuest.</p>
                    <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                    <p><a href='" . $resetLink . "' style='background:#007bff; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Réinitialiser mon mot de passe</a></p>
                    <p>Ou copiez ce lien dans votre navigateur :<br>" . $resetLink . "</p>
                    <p><strong>Ce lien expire dans 1 heure.</strong></p>
                    <p>Si vous n'avez pas fait cette demande, ignorez cet email.</p>
                    <br>
                    <p>L'équipe DormQuest</p>
                </body>
                </html>
                ";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: noreply@dormquest.com\r\n";

                if (mail($to, $subject, $message, $headers)) {
                    $success = "✅ Un email de réinitialisation a été envoyé à votre adresse.";
                } else {
                    $errors[] = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
                }
            } else {
                // Message identique pour éviter l'énumération des emails
                $success = "✅ Si cette adresse existe dans notre base, un email de réinitialisation a été envoyé.";
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
<title>Mot de passe oublié - DormQuest</title>
<link rel="stylesheet" href="css/styles.css">
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
</style>
</head>
<body>

<div class="login-box">
<h2>🔐 Mot de passe oublié</h2>
<p>Entrez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>

<?php if(!empty($errors)): ?>
<div class="alert alert-error">
  <?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
</div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" action="">
<input type="email" name="email" placeholder="Votre adresse email" required value="<?php echo htmlspecialchars($email); ?>">
<button type="submit">Envoyer le lien</button>
</form>

<div class="footer">
<a href="login.php">← Retour à la connexion</a>
</div>
</div>

</body>
</html>