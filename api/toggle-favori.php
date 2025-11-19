<?php
// api/toggle-favori.php - API pour gérer les favoris
session_start();
header('Content-Type: application/json');

require_once '../includes/auth.php';
require_once '../includes/db.php';

// Vérifier que l'utilisateur est connecté et est un étudiant
if (!is_logged_in() || !is_etudiant()) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté en tant qu\'étudiant'
    ]);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['annonce_id']) || !isset($input['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Données manquantes'
    ]);
    exit();
}

$annonce_id = intval($input['annonce_id']);
$action = $input['action']; // 'add' ou 'remove'
$etudiant_id = get_user_id();

try {
    if ($action === 'add') {
        // Ajouter aux favoris
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO favoris (idEtudiant, idAnnonce, dateAjout) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$etudiant_id, $annonce_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Annonce ajoutée aux favoris',
            'action' => 'added'
        ]);
        
    } elseif ($action === 'remove') {
        // Retirer des favoris
        $stmt = $pdo->prepare("
            DELETE FROM favoris 
            WHERE idEtudiant = ? AND idAnnonce = ?
        ");
        $stmt->execute([$etudiant_id, $annonce_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Annonce retirée des favoris',
            'action' => 'removed'
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Action invalide'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
?>