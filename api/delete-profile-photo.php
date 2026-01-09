<?php
// api/delete-profile-photo.php - Suppression de la photo de profil
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit();
}

$csrf = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'Token CSRF invalide']);
    exit();
}

$user_id = get_user_id();

try {
    // Récupérer le chemin de la photo actuelle
    $photoPath = get_user_photo_by_id($pdo, $user_id);

    if (!$photoPath) {
        http_response_code(400);
        echo json_encode(['error' => 'Aucune photo à supprimer']);
        exit();
    }

    $fullPath = __DIR__ . '/../' . $photoPath;

    // Supprimer le fichier du serveur si existe
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }

    // Supprimer de la base de données
    delete_user_photo($pdo, $user_id);

    // Rafraîchir la session
    refresh_session($pdo);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur BD: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}

?>
