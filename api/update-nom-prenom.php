<?php
// api/update-nom-prenom.php - Sauvegarde rapide du nom et prénom pour l'utilisateur connecté
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/auth.php';
require_once '../includes/db.php';

// Vérifier la session
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit();
}

// Vérifier le token CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'Token CSRF invalide']);
    exit();
}

$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');

if ($prenom === '' || $nom === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Le nom et le prénom sont obligatoires']);
    exit();
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE utilisateurs SET prenom = ?, nom = ? WHERE id = ?");
    $stmt->execute([$prenom, $nom, get_user_id()]);
    $pdo->commit();

    // Mettre à jour la session si besoin
    refresh_session($pdo);

    echo json_encode(['success' => true, 'prenom' => $prenom, 'nom' => $nom]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo json_encode(['error' => 'Erreur BD: ' . $e->getMessage()]);
}
