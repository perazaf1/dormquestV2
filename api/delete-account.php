<?php
/**
 * API - Supprimer le compte utilisateur
 */

// Démarrer la session
session_start();

// Headers
header('Content-Type: application/json; charset=utf-8');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

// Inclure la DB
require_once '../includes/db.php';

try {
    $userId = $_SESSION['user_id'];
    
    // Commencer une transaction
    $pdo->beginTransaction();
    
    // 1. Supprimer toutes les candidatures de l'utilisateur (s'il est étudiant) - si la table existe
    try {
        $stmt = $pdo->prepare("DELETE FROM candidatures WHERE idEtudiant = ?");
        $stmt->execute([$userId]);
    } catch (Exception $e) {
        // Table n'existe pas, continuer
    }
    
    // 2. Supprimer toutes les annonces de l'utilisateur (s'il est loueur)
    try {
        $stmt = $pdo->prepare("SELECT id FROM annonces WHERE idLoueur = ?");
        $stmt->execute([$userId]);
        $annonces = $stmt->fetchAll();
        
        foreach ($annonces as $annonce) {
            // Supprimer les photos de l'annonce
            try {
                $stmtPhotos = $pdo->prepare("SELECT photoPath FROM photos_annonces WHERE idAnnonce = ?");
                $stmtPhotos->execute([$annonce['id']]);
                $photos = $stmtPhotos->fetchAll();
                
                foreach ($photos as $photo) {
                    $uploadDir = __DIR__ . '/../uploads/annonces/';
                    $filePath = $uploadDir . basename($photo['photoPath']);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                // Supprimer les photos de la base de données
                $stmtDelPhotos = $pdo->prepare("DELETE FROM photos_annonces WHERE idAnnonce = ?");
                $stmtDelPhotos->execute([$annonce['id']]);
            } catch (Exception $e) {
                // Photos n'existent pas, continuer
            }
        }
        
        // Supprimer les annonces
        $stmt = $pdo->prepare("DELETE FROM annonces WHERE idLoueur = ?");
        $stmt->execute([$userId]);
    } catch (Exception $e) {
        // Table n'existe pas, continuer
    }
    
    // 3. Supprimer les favoris de l'utilisateur - si la table existe
    try {
        $stmt = $pdo->prepare("DELETE FROM favoris WHERE idEtudiant = ?");
        $stmt->execute([$userId]);
    } catch (Exception $e) {
        // Table n'existe pas, continuer
    }
    
    // 4. Supprimer la photo de profil si elle existe
    $stmt = $pdo->prepare("SELECT photoDeProfil FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user && $user['photoDeProfil']) {
        $uploadDir = __DIR__ . '/../uploads/profiles/';
        $filePath = $uploadDir . basename($user['photoDeProfil']);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // 5. Supprimer le compte utilisateur
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    
    // Confirmer la transaction
    $pdo->commit();
    
    // Détruire la session
    session_destroy();
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Compte supprimé avec succès']);
    exit;
    
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    exit;
}
?>
