<?php
/**
 * API pour supprimer une notification
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

try {
    $notifId = isset($_POST['notif_id']) ? (int)$_POST['notif_id'] : 0;
    $userId = get_user_id();

    if ($notifId <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID invalide']);
        exit;
    }

    // Vérifier que la notification appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT id FROM notifications WHERE id = ? AND idUtilisateur = ?");
    $stmt->execute([$notifId, $userId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Notification non trouvée']);
        exit;
    }

    // Supprimer la notification
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND idUtilisateur = ?");
    $stmt->execute([$notifId, $userId]);

    echo json_encode(['success' => true, 'message' => 'Notification supprimée']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
