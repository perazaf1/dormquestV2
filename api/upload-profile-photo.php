<?php
// api/upload-profile-photo.php - Upload et persistance de la photo de profil
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

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

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Fichier manquant ou erreur d\'upload']);
    exit();
}

$file = $_FILES['photo'];
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
$max_size = 2 * 1024 * 1024; // 2MB

if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Type de fichier non autorisé']);
    exit();
}

if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['error' => 'Fichier trop volumineux']);
    exit();
}

$upload_dir = __DIR__ . '/../uploads/profiles/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true) && !is_dir($upload_dir)) {
        http_response_code(500);
        echo json_encode(['error' => 'Impossible de créer le dossier d\'upload']);
        exit();
    }
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$unique = uniqid('profile_', true) . '.' . $ext;
$target = $upload_dir . $unique;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    http_response_code(500);
    echo json_encode(['error' => 'Échec lors de l\'enregistrement du fichier']);
    exit();
}

// Chemin public relatif depuis la racine du projet
$public_path = 'uploads/profiles/' . $unique;

try {
    $stmt = $pdo->prepare("UPDATE utilisateurs SET photoDeProfil = ? WHERE id = ?");
    $stmt->execute([$public_path, get_user_id()]);

    // Mettre à jour la session si nécessaire
    refresh_session($pdo);

    echo json_encode(['success' => true, 'path' => $public_path]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur BD: ' . $e->getMessage()]);
}

?>
