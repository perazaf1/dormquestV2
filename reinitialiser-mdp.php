<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';

$errors = [];
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$userId = null;

// Variables pour le header
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

// Vérifier le token
if ($token) {
    try {
        $hashedToken = hash('sha256', $token);
        $stmt = $pdo->prepare("
            SELECT user_id, expires_at 
            FROM password_resets 
            WHERE token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$hashedToken]);
        $reset = $stmt->fetch();

        if ($reset) {
            $validToken = true;
            $userId = $reset['user_id'];
        } else {
            $errors[] = "Ce lien de réinitialisation est invalide ou a expiré.";
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur lors de la vérification du token.";
    }
} else {
    $errors[] = "Token manquant.";
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation
    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        try {
            // Mettre à jour le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET motDePasse = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            // Supprimer tous les tokens de réinitialisation pour cet utilisateur
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$userId]);

            $success = "✅ Votre mot de passe a été réinitialisé avec succès !";
            $validToken = false; // Empêcher une nouvelle soumission
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la réinitialisation : " . $e->getMessage();
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
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <?php if ($success): ?>
                <div class="login-header">
                    <div class="success-icon" style="font-size: 64px; margin-bottom: 20px;">✅</div>
                    <h2>Mot de passe réinitialisé</h2>
                    <p style="color: var(--color-success);">Votre mot de passe a été modifié avec succès !</p>
                </div>
                
                <div class="alert alert--success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                
                <div class="login-footer">
                    <p><a href="login.php">← Retour à la connexion</a></p>
                </div>
            <?php else: ?>
                <div class="login-header">
                    <h2>🔐 Nouveau mot de passe</h2>
                    <p>Choisissez un nouveau mot de passe sécurisé pour votre compte.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert--error">
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($validToken): ?>
                    <form method="POST" action="" class="login-form">
                        <div class="form-group">
                            <label for="password">Nouveau mot de passe *</label>
                            <input type="password" id="password" name="password" required minlength="8" 
                                   placeholder="Minimum 8 caractères">
                            <small class="form-hint">Utilisez au moins 8 caractères</small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Confirmer le mot de passe *</label>
                            <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                                   placeholder="Ressaisissez votre mot de passe">
                        </div>

                        <button type="submit" class="btn-submit">
                            🔒 Réinitialiser le mot de passe
                        </button>
                    </form>
                <?php endif; ?>

                <div class="login-footer">
                    <p><a href="login.php">← Retour à la connexion</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
