<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';

$errors = [];
$success = '';
$email = '';

// Variables pour le header
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

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
                $baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
                $scriptPath = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
                $resetLink = $baseUrl . $scriptPath . "/reinitialiser-mdp.php?token=" . $token;

                // Envoyer l'email
                $to = $user['email'];
                $subject = "DormQuest - Réinitialisation de votre mot de passe";
                
                // Template HTML de l'email
                $message = "
                <!DOCTYPE html>
                <html lang='fr'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Réinitialisation de mot de passe</title>
                </head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #f8f9fa; padding: 30px; border-radius: 10px;'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <h1 style='color: #007bff; margin: 0;'>🏠 DormQuest</h1>
                        </div>
                        
                        <div style='background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                            <h2 style='color: #333; margin-top: 0;'>Bonjour " . htmlspecialchars($user['prenom']) . ",</h2>
                            
                            <p>Vous avez demandé la réinitialisation de votre mot de passe sur <strong>DormQuest</strong>.</p>
                            
                            <p>Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>
                            
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='" . $resetLink . "' 
                                   style='display: inline-block; background: #007bff; color: white; padding: 14px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;'>
                                    🔒 Réinitialiser mon mot de passe
                                </a>
                            </div>
                            
                            <p style='font-size: 14px; color: #666;'>Ou copiez-collez ce lien dans votre navigateur :</p>
                            <p style='background: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 13px;'>
                                <a href='" . $resetLink . "' style='color: #007bff;'>" . $resetLink . "</a>
                            </p>
                            
                            <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 20px 0; border-radius: 4px;'>
                                <p style='margin: 0; font-size: 14px;'>
                                    ⚠️ <strong>Important :</strong> Ce lien expire dans <strong>1 heure</strong>.
                                </p>
                            </div>
                            
                            <p style='font-size: 14px; color: #666;'>
                                Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email. Votre mot de passe restera inchangé.
                            </p>
                        </div>
                        
                        <div style='text-align: center; margin-top: 20px; font-size: 13px; color: #666;'>
                            <p>Cordialement,<br><strong>L'équipe DormQuest</strong></p>
                            <p style='margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;'>
                                © 2025 DormQuest - Votre plateforme de logement étudiant
                            </p>
                        </div>
                    </div>
                </body>
                </html>
                ";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: DormQuest <noreply@dormquest.com>\r\n";
                $headers .= "Reply-To: support@dormquest.com\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

                // Tentative d'envoi de l'email
                $emailSent = @mail($to, $subject, $message, $headers);
                
                if ($emailSent) {
                    $success = "✅ Un email de réinitialisation a été envoyé à votre adresse.";
                } else {
                    // En développement local, l'email ne sera pas envoyé mais on affiche quand même le lien
                    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
                        $success = "⚠️ Mode développement : L'email ne peut pas être envoyé localement.<br><br>
                                   <strong>Lien de réinitialisation :</strong><br>
                                   <a href='" . $resetLink . "' target='_blank' style='word-break: break-all;'>" . $resetLink . "</a>";
                    } else {
                        $errors[] = "Erreur lors de l'envoi de l'email. Veuillez réessayer ou contacter le support.";
                    }
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
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>🔐 Mot de passe oublié</h2>
                <p>Entrez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
            </div>

            <?php if(!empty($errors)): ?>
            <div class="alert alert--error">
                <?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="alert alert--success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" placeholder="votre.email@exemple.com" required
                        value="<?php echo htmlspecialchars($email); ?>">
                </div>

                <button type="submit" class="btn-submit"> Envoyer le lien de réinitialisation</button>
            </form>

            <div class="login-footer">
                <p><a href="login.php">← Retour à la connexion</a></p>
            </div>
        </div>
    </div>
</body>

</html>