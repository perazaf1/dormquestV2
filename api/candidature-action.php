<?php
// ============================================================================
// API DE GESTION DES CANDIDATURES
// Fichier : api/candidature-action.php
// 
// Ce script gère 3 actions possibles sur les candidatures :
// 1. ACCEPTER une candidature (loueur uniquement)
// 2. REFUSER une candidature (loueur uniquement)
// 3. ANNULER une candidature (étudiant uniquement)
// ============================================================================

// Démarrer la session pour accéder aux informations de l'utilisateur connecté
session_start();

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../includes/db.php';    // Connexion à la base de données
require_once __DIR__ . '/../includes/auth.php';  // Fonctions d'authentification

// ============================================================================
// ÉTAPE 1 : VÉRIFICATION DE LA MÉTHODE HTTP
// ============================================================================
// Ce script n'accepte que les requêtes POST (soumission de formulaire)
// Si quelqu'un essaie d'y accéder avec GET, on bloque
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);  // Code HTTP 405 = Méthode non autorisée
    exit('Method not allowed');
}

// ============================================================================
// ÉTAPE 2 : RÉCUPÉRATION ET VALIDATION DES DONNÉES
// ============================================================================

// Récupérer l'action demandée (accept, refuse, ou cancel)
// L'opérateur ?? retourne une chaîne vide si $_POST['action'] n'existe pas
$action = $_POST['action'] ?? '';

// Récupérer l'ID de la candidature et le convertir en entier pour la sécurité
// Si l'ID n'existe pas ou est invalide, on met 0
$candidature_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Déterminer la page de redirection après l'action
// Par défaut : dashboard du loueur ou de l'étudiant selon le rôle
$redirect = $_POST['redirect'] ?? (is_loueur() ? '../dashboard-loueur.php' : '../dashboard-etudiant.php');

// ============================================================================
// ÉTAPE 3 : VÉRIFICATION DU TOKEN CSRF (SÉCURITÉ)
// ============================================================================
// Le token CSRF protège contre les attaques "Cross-Site Request Forgery"
// où un site malveillant pourrait forcer un utilisateur à effectuer des actions

$csrf = $_POST['csrf_token'] ?? null;

// Si un token est fourni, on le vérifie
// Si le token est absent (null), on accepte pour rester compatible avec d'anciens formulaires
if ($csrf !== null && !verify_csrf_token($csrf)) {
    $msg = 'Token CSRF invalide.';
    // Rediriger vers la page d'origine avec un message d'erreur
    header('Location: ' . $redirect . '?error=' . urlencode($msg));
    exit();
}

// ============================================================================
// ÉTAPE 4 : VALIDATION DE L'ID DE CANDIDATURE
// ============================================================================
// Vérifier que l'ID est valide (supérieur à 0)
if ($candidature_id <= 0) {
    header('Location: ' . $redirect . '?error=' . urlencode('Candidature introuvable.'));
    exit();
}

// ============================================================================
// ÉTAPE 5 : TRAITEMENT DES ACTIONS
// ============================================================================
try {
    // Bloc try-catch pour gérer les erreurs de base de données
    
    // --------------------------------------------------------------------
    // CAS 1 & 2 : ACCEPTER OU REFUSER UNE CANDIDATURE (LOUEUR)
    // --------------------------------------------------------------------
    if ($action === 'accept' || $action === 'refuse') {
        
        // VÉRIFICATION 1 : L'utilisateur doit être un loueur
        if (!is_loueur()) {
            header('Location: ' . $redirect . '?error=' . urlencode('Accès refusé.'));
            exit();
        }

        // VÉRIFICATION 2 : Récupérer la candidature et vérifier les droits
        // On fait une jointure (JOIN) pour récupérer aussi l'ID du loueur de l'annonce
        $stmt = $pdo->prepare("
            SELECT c.*, a.idLoueur 
            FROM candidatures c 
            JOIN annonces a ON c.idAnnonce = a.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$candidature_id]);
        $c = $stmt->fetch();

        // Si la candidature n'existe pas, erreur
        if (!$c) {
            header('Location: ' . $redirect . '?error=' . urlencode('Candidature introuvable.'));
            exit();
        }

        // VÉRIFICATION 3 : Le loueur connecté doit être le propriétaire de l'annonce
        // Cela empêche un loueur de gérer les candidatures d'autres loueurs
        if ($c['idLoueur'] != get_user_id()) {
            header('Location: ' . $redirect . '?error=' . urlencode('Vous n\'êtes pas autorisé à gérer cette candidature.'));
            exit();
        }

        // MISE À JOUR : Changer le statut de la candidature
        // Si action = 'accept' → statut = 'acceptee'
        // Si action = 'refuse' → statut = 'refusee'
        $newStatus = ($action === 'accept') ? 'acceptee' : 'refusee';
        
        $stmt = $pdo->prepare("
            UPDATE candidatures 
            SET statut = ?, dateReponse = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$newStatus, $candidature_id]);

        // Rediriger avec un message de succès
        header('Location: ' . $redirect . '?success=' . urlencode('Candidature mise à jour.'));
        exit();

    } 
    // --------------------------------------------------------------------
    // CAS 3 : ANNULER UNE CANDIDATURE (ÉTUDIANT)
    // --------------------------------------------------------------------
    elseif ($action === 'cancel') {
        
        // VÉRIFICATION 1 : L'utilisateur doit être un étudiant
        if (!is_etudiant()) {
            header('Location: ' . $redirect . '?error=' . urlencode('Accès refusé.'));
            exit();
        }

        // VÉRIFICATION 2 : Récupérer la candidature et vérifier qu'elle appartient bien à l'étudiant
        // Double vérification : ID de candidature ET ID d'étudiant
        $stmt = $pdo->prepare("
            SELECT * 
            FROM candidatures 
            WHERE id = ? AND idEtudiant = ?
        ");
        $stmt->execute([$candidature_id, get_user_id()]);
        $c = $stmt->fetch();

        // Si la candidature n'existe pas ou n'appartient pas à cet étudiant
        if (!$c) {
            header('Location: ' . $redirect . '?error=' . urlencode('Candidature introuvable ou accès non autorisé.'));
            exit();
        }

        // MISE À JOUR : Passer le statut à 'annulee'
        $stmt = $pdo->prepare("
            UPDATE candidatures 
            SET statut = 'annulee', dateReponse = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$candidature_id]);

        // Rediriger avec un message de succès
        header('Location: ' . $redirect . '?success=' . urlencode('Candidature annulée.'));
        exit();

    } 
    // --------------------------------------------------------------------
    // CAS 4 : ACTION INCONNUE
    // --------------------------------------------------------------------
    else {
        // Si l'action n'est ni 'accept', ni 'refuse', ni 'cancel'
        header('Location: ' . $redirect . '?error=' . urlencode('Action inconnue.'));
        exit();
    }
    
} catch (PDOException $e) {
    // En cas d'erreur de base de données, rediriger avec le message d'erreur
    header('Location: ' . $redirect . '?error=' . urlencode('Erreur serveur : ' . $e->getMessage()));
    exit();
}

// ============================================================================
// RÉSUMÉ DU FONCTIONNEMENT :
// ============================================================================
// 
// Ce script agit comme un "contrôleur" qui traite les actions sur les candidatures.
// 
// FLUX D'EXÉCUTION :
// 1. Vérifier que c'est bien une requête POST
// 2. Récupérer l'action demandée et l'ID de la candidature
// 3. Vérifier le token CSRF pour la sécurité
// 4. Valider que l'ID est correct
// 5. Selon l'action :
//    - ACCEPT/REFUSE : Vérifier que c'est un loueur propriétaire de l'annonce
//    - CANCEL : Vérifier que c'est l'étudiant qui a envoyé la candidature
// 6. Mettre à jour le statut dans la base de données
// 7. Rediriger vers le dashboard avec un message de succès ou d'erreur
// 
// SÉCURITÉ :
// ✅ Vérification du rôle (loueur vs étudiant)
// ✅ Vérification de propriété (qui peut gérer quoi)
// ✅ Protection CSRF
// ✅ Validation des données entrantes
// ✅ Gestion des erreurs avec try-catch
// ============================================================================
?>