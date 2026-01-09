<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Vérifier l'authentification
if (!is_logged_in() || get_user_role() !== 'loueur') {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

// Vérifier le token CSRF
if (!verify_csrf_token($input['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
    exit;
}

$photoId = intval($input['photo_id'] ?? 0);

if ($photoId <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de photo invalide']);
    exit;
}

try {
    // Récupérer la photo avec l'annonce associée
    $stmt = $pdo->prepare("
        SELECT p.*, a.idLoueur
        FROM photos_annonces p
        JOIN annonces a ON p.idAnnonce = a.id
        WHERE p.id = ?
    ");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();

    if (!$photo) {
        echo json_encode(['success' => false, 'error' => 'Photo non trouvée']);
        exit;
    }

    // Vérifier que la photo appartient au loueur connecté
    if ($photo['idLoueur'] != get_user_id()) {
        echo json_encode(['success' => false, 'error' => 'Non autorisé à supprimer cette photo']);
        exit;
    }

    // Supprimer la photo de la base de données
    $cheminPhoto = delete_photo_annonce($pdo, $photoId);

    if ($cheminPhoto) {
        // Supprimer le fichier physique
        $filePath = __DIR__ . '/../' . $cheminPhoto;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        echo json_encode(['success' => true, 'message' => 'Photo supprimée avec succès']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur de base de données : ' . $e->getMessage()]);
}
