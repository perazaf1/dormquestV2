<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';

$errors = [];
$success = '';
$email = '';
$secret_question = '';
$showQuestionForm = false;

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

    // Helper pour vérifier si une colonne existe
    $columnExists = function($col) use ($pdo) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = ?");
        $stmt->execute([$col]);
        return (bool) $stmt->fetchColumn();
    };

    if (empty($errors)) {
        try {
            // Vérifier si l'email existe
            $stmt = $pdo->prepare("SELECT id, prenom, email FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Si l'utilisateur existe et qu'il y a une question secrète configurée, on la récupère
            if ($user && $columnExists('secret_question')) {
                $stmt = $pdo->prepare("SELECT secret_question, secret_answer_hash FROM utilisateurs WHERE id = ?");
                $stmt->execute([$user['id']]);
                $row = $stmt->fetch();
                $secret_question = $row['secret_question'] ?? '';

                // Si l'utilisateur a soumis la réponse, on la vérifie
                if (isset($_POST['secret_answer'])) {
                    $secret_answer = trim($_POST['secret_answer'] ?? '');
                    $hash = $row['secret_answer_hash'] ?? '';

                    if ($hash && password_verify($secret_answer, $hash)) {
                        // Générer token et rediriger vers la page de réinitialisation
                        $token = bin2hex(random_bytes(32));
                        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                        $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
                        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                        $stmt->execute([$user['id'], hash('sha256', $token), $expiry]);

                        $baseUrl = 'http://' . $_SERVER['HTTP_HOST'];
                        $scriptPath = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
                        $resetLink = $baseUrl . $scriptPath . "/reinitialiser-mdp.php?token=" . $token;

                        header('Location: ' . $resetLink);
                        exit();
                    } else {
                        $errors[] = "Réponse incorrecte à la question secrète.";
                        $showQuestionForm = true;
                    }
                } else {
                    // Montrer la question pour que l'utilisateur y réponde
                    $showQuestionForm = true;
                }
            } else {
                $errors[] = "Réinitialisation impossible : aucun compte valide avec question secrète trouvé.";
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
                <p>Entrez votre email et nous vous redirigerons vers votre question de sécurité.</p>
            </div>

            <?php if(!empty($errors)): ?>
            <div class="alert alert--error">
                <?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="alert alert--success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($showQuestionForm): ?>
                <form method="POST" action="" class="login-form">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="hidden" name="action" value="verify_answer">
                    <div class="form-group">
                        <label>Question secrète</label>
                        <div style="background:#f8f9fa;padding:10px;border-radius:4px;"><?php echo htmlspecialchars($secret_question); ?></div>
                    </div>
                    <div class="form-group">
                        <label for="secret_answer">Votre réponse</label>
                        <input type="text" id="secret_answer" name="secret_answer" required placeholder="Votre réponse">
                    </div>
                    <button type="submit" class="btn-submit">Vérifier la réponse</button>
                </form>
            <?php else: ?>
                <form method="POST" action="" class="login-form">
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" placeholder="votre.email@exemple.com" required
                            value="<?php echo htmlspecialchars($email); ?>">
                    </div>

                    <button type="submit" class="btn-submit">Répondre à ma question secrète</button>
                </form>
            <?php endif; ?>

            <div class="login-footer">
                <p><a href="login.php">← Retour à la connexion</a></p>
            </div>
        </div>
    </div>
</body>

</html>