<?php
/**
 * API pour traiter les messages de contact
 * Crée une notification pour l'admin "test"
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    // Récupérer les données du formulaire
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Valider les données
    if (empty($nom) || empty($email) || empty($telephone) || empty($message)) {
        echo json_encode([
            'success' => false,
            'error' => 'Tous les champs sont requis'
        ]);
        exit;
    }

    // Valider l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'error' => 'Email invalide'
        ]);
        exit;
    }

    // Trouver l'utilisateur admin "test"
    $stmt = $pdo->prepare("
        SELECT id FROM utilisateurs 
        WHERE email = 'test@gmail.com'
        LIMIT 1
    ");
    $stmt->execute();
    $admin = $stmt->fetch();

    if (!$admin) {
        echo json_encode([
            'success' => false,
            'error' => 'Administrateur non trouvé'
        ]);
        exit;
    }

    $adminId = $admin['id'];

    // Créer la notification pour l'admin
    $titre = "Nouveau message de contact de " . htmlspecialchars($nom);
    $donneesJson = json_encode([
        'nom' => $nom,
        'email' => $email,
        'telephone' => $telephone,
        'message' => $message,
        'dateReception' => date('Y-m-d H:i:s')
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO notifications (idUtilisateur, titre, message, type, donneesJson)
        VALUES (?, ?, ?, 'contact', ?)
    ");

    $stmt->execute([
        $adminId,
        $titre,
        $message,
        $donneesJson
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Message envoyé avec succès. Nous vous répondrons dès que possible.'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>
