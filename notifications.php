<?php
// notifications.php - Page pour voir les notifications
session_start();

require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';

require_login();

$userId = get_user_id();

try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE idUtilisateur = ? ORDER BY dateCreation DESC LIMIT 50");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE idUtilisateur = ? AND lue = FALSE");
    $stmt->execute([$userId]);
    $nbNonLues = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    $notifications = [];
    $nbNonLues = 0;
    $error = 'Erreur lors de la récupération des notifications.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="notifications-header">
            <h1>Mes notifications</h1>
            <?php if ($nbNonLues > 0): ?>
                <span class="badge-unread"><?php echo $nbNonLues; ?> non lue<?php echo $nbNonLues > 1 ? 's' : ''; ?></span>
            <?php endif; ?>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <i class="fa-regular fa-bell" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>Aucune notification pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $notif): ?>
                    <?php 
                    $dateNotif = new DateTime($notif['dateCreation']);
                    $dateFormatee = $dateNotif->format('d/m/Y H:i');
                    ?>
                    <div class="notification-item <?php echo !$notif['lue'] ? 'unread' : ''; ?>" id="notif-<?php echo $notif['id']; ?>" data-notif-id="<?php echo $notif['id']; ?>">
                        <div class="notification-icon">
                            <?php if ($notif['type'] === 'contact'): ?>
                                <i class="fa-regular fa-envelope"></i>
                            <?php elseif ($notif['type'] === 'candidature'): ?>
                                <i class="fa-regular fa-file-lines"></i>
                            <?php elseif ($notif['type'] === 'favori'): ?>
                                <i class="fa-solid fa-heart"></i>
                            <?php else: ?>
                                <i class="fa-regular fa-bell"></i>
                            <?php endif; ?>
                        </div>
                        <div class="notification-content">
                            <h3 class="notification-title"><?php echo htmlspecialchars($notif['titre']); ?></h3>
                            <p class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                            
                            <?php if ($notif['type'] === 'contact' && !empty($notif['donneesJson'])): ?>
                                <?php $donnees = json_decode($notif['donneesJson'], true); ?>
                                <div class="notification-details">
                                    <p><strong>De :</strong> <?php echo htmlspecialchars($donnees['nom'] ?? ''); ?> (<?php echo htmlspecialchars($donnees['email'] ?? ''); ?>)</p>
                                    <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($donnees['telephone'] ?? ''); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="notification-footer">
                                <span class="notification-date">
                                    <i class="fa-regular fa-clock"></i>
                                    <?php echo $dateFormatee; ?>
                                </span>
                                <?php if (!$notif['lue']): ?>
                                    <button class="btn-mark-read" data-notif-id="<?php echo $notif['id']; ?>">
                                        <i class="fa-solid fa-check"></i> Marquer comme lue
                                    </button>
                                <?php endif; ?>
                                <button class="btn-delete-notif" data-notif-id="<?php echo $notif['id']; ?>">
                                    <i class="fa-solid fa-trash-can"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="js/notifications.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
