<?php
// ============================================================================
// API DE CRÉATION DE CANDIDATURE
// Fichier : api/postuler.php
//
// Ce script permet à un étudiant de postuler à une annonce
// ============================================================================

session_start();

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Définir le type de contenu JSON
header('Content-Type: application/json');

// ============================================================================
// ÉTAPE 1 : VÉRIFICATION DE L'AUTHENTIFICATION
// ============================================================================
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour postuler']);
    exit;
}

// ============================================================================
// ÉTAPE 2 : VÉRIFICATION DU RÔLE (ÉTUDIANT UNIQUEMENT)
// ============================================================================
if (!is_etudiant()) {
    echo json_encode(['success' => false, 'error' => 'Seuls les étudiants peuvent postuler']);
    exit;
}

// ============================================================================
// ÉTAPE 3 : VÉRIFICATION DE LA MÉTHODE HTTP
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// ============================================================================
// ÉTAPE 4 : RÉCUPÉRATION DES DONNÉES JSON
// ============================================================================
$input = json_decode(file_get_contents('php://input'), true);

$annonceId = isset($input['annonce_id']) ? (int)$input['annonce_id'] : 0;
$csrfToken = $input['csrf_token'] ?? null;

// ============================================================================
// ÉTAPE 5 : VALIDATION DES DONNÉES
// ============================================================================
if ($annonceId <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID d\'annonce invalide']);
    exit;
}

// Vérification CSRF (optionnelle pour compatibilité)
if ($csrfToken !== null && !verify_csrf_token($csrfToken)) {
    echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
    exit;
}

// ============================================================================
// ÉTAPE 6 : VÉRIFICATIONS MÉTIER
// ============================================================================
try {
    $etudiantId = get_user_id();

    // Vérifier que l'annonce existe et est active
    $annonce = get_annonce_by_id($pdo, $annonceId);

    if (!$annonce) {
        echo json_encode(['success' => false, 'error' => 'Annonce introuvable']);
        exit;
    }

    if ($annonce['statut'] !== 'active') {
        echo json_encode(['success' => false, 'error' => 'Cette annonce n\'est plus disponible']);
        exit;
    }

    // Vérifier si l'étudiant n'a pas déjà postulé
    if (has_candidature($pdo, $etudiantId, $annonceId)) {
        echo json_encode(['success' => false, 'error' => 'Vous avez déjà postulé à cette annonce']);
        exit;
    }

    // ============================================================================
    // ÉTAPE 7 : CRÉATION DE LA CANDIDATURE
    // ============================================================================
    $candidatureId = create_candidature($pdo, $etudiantId, $annonceId);

    if ($candidatureId) {
        echo json_encode([
            'success' => true,
            'message' => 'Votre candidature a été envoyée avec succès',
            'candidature_id' => $candidatureId
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la création de la candidature']);
    }

} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}

// ============================================================================
// RÉSUMÉ DU FONCTIONNEMENT :
// ============================================================================
//
// FLUX D'EXÉCUTION :
// 1. Vérifier que l'utilisateur est connecté et est un étudiant
// 2. Vérifier que c'est une requête POST avec des données JSON
// 3. Valider l'ID de l'annonce
// 4. Vérifier le token CSRF (optionnel)
// 5. Vérifier que l'annonce existe et est active
// 6. Vérifier que l'étudiant n'a pas déjà postulé
// 7. Créer la candidature dans la base de données
// 8. Retourner une réponse JSON avec le résultat
//
// SÉCURITÉ :
// ✅ Vérification de l'authentification
// ✅ Vérification du rôle (étudiant uniquement)
// ✅ Validation des données entrantes
// ✅ Protection contre les doublons
// ✅ Vérification de l'existence de l'annonce
// ✅ Gestion des erreurs avec try-catch
// ============================================================================
?>
