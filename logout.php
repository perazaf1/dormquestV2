<?php
// ============================================================================
// SCRIPT DE DÉCONNEXION
// Fichier : logout.php
// 
// Ce script effectue une déconnexion complète et sécurisée :
// 1. Enregistre la déconnexion dans la base de données (optionnel)
// 2. Supprime toutes les variables de session
// 3. Détruit le cookie de session
// 4. Supprime le cookie "Se souvenir de moi"
// 5. Redirige vers la page d'accueil
// ============================================================================

// Démarrer la session pour pouvoir accéder aux données de l'utilisateur
session_start();

// Inclure les fichiers nécessaires
require_once __DIR__ . '/includes/db.php';    // Connexion à la base de données
require_once 'includes/auth.php';             // Fonctions d'authentification

// ============================================================================
// ÉTAPE 1 : MISE À JOUR DE LA BASE DE DONNÉES (OPTIONNEL)
// ============================================================================
// Si un utilisateur est connecté, on peut enregistrer l'heure de déconnexion
// Cette étape est optionnelle mais utile pour :
// - Suivre l'activité des utilisateurs
// - Afficher "Dernière connexion" sur le profil
// - Analyser les statistiques d'utilisation

if (isset($_SESSION['user_id'])) {
    // Vérifier que le fichier de connexion à la BDD existe
    // (au cas où il y aurait un problème de configuration)
    if (file_exists(__DIR__ . '/includes/db.php')) {
        try {
            // Bloc try-catch pour éviter qu'une erreur BDD bloque la déconnexion
            require_once __DIR__ . '/includes/db.php';
            
            // Mettre à jour la colonne 'derniereConnexion' avec l'heure actuelle
            // NOW() est une fonction MySQL qui donne la date/heure du moment
            $stmt = $pdo->prepare("UPDATE utilisateurs SET derniereConnexion = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
        } catch (Exception $e) {
            // Si la mise à jour échoue (BDD inaccessible, etc.), on ne fait rien
            // IMPORTANT : On ne doit JAMAIS empêcher un utilisateur de se déconnecter
            // même si la BDD ne répond pas
        }
    }
}

// ============================================================================
// ÉTAPE 2 : SUPPRIMER TOUTES LES VARIABLES DE SESSION
// ============================================================================
// $_SESSION = []; vide complètement le tableau des variables de session
// Cela supprime : user_id, user_role, user_name, csrf_token, etc.
// C'est plus sûr que de faire unset($_SESSION['user_id']) car on s'assure
// qu'aucune donnée sensible ne reste en mémoire
$_SESSION = [];

// ============================================================================
// ÉTAPE 3 : SUPPRIMER LE COOKIE DE SESSION
// ============================================================================
// Les sessions PHP utilisent souvent un cookie (nommé PHPSESSID par défaut)
// pour identifier la session de l'utilisateur entre les pages
// Il faut aussi supprimer ce cookie pour une déconnexion complète

// Vérifier si PHP utilise des cookies pour les sessions
if (ini_get("session.use_cookies")) {
    // Récupérer les paramètres du cookie de session
    $params = session_get_cookie_params();
    
    // Supprimer le cookie en le remettant à une date passée
    // time() - 42000 = il y a environ 12 heures (date dans le passé)
    // Le navigateur va automatiquement supprimer un cookie expiré
    setcookie(
        session_name(),           // Nom du cookie (généralement PHPSESSID)
        '',                       // Valeur vide
        time() - 42000,          // Date d'expiration dans le passé
        $params['path'],         // Chemin (généralement '/')
        $params['domain'],       // Domaine du cookie
        $params['secure'],       // HTTPS uniquement ?
        $params['httponly']      // Accessible uniquement via HTTP (pas JavaScript)
    );
}

// ============================================================================
// ÉTAPE 4 : DÉTRUIRE LA SESSION CÔTÉ SERVEUR
// ============================================================================
// session_destroy() supprime le fichier de session sur le serveur
// Cela libère la mémoire et garantit qu'aucune donnée ne reste stockée
session_destroy();

// ============================================================================
// ÉTAPE 5 : SUPPRIMER LE COOKIE "SE SOUVENIR DE MOI"
// ============================================================================
// Si l'utilisateur avait coché "Se souvenir de moi" lors de la connexion,
// un cookie spécial (remember_token) a été créé pour le reconnecter automatiquement
// On doit aussi supprimer ce cookie pour une déconnexion complète

setcookie(
    'remember_token',    // Nom du cookie de mémorisation
    '',                  // Valeur vide
    time() - 42000,     // Date d'expiration dans le passé
    '/'                 // Chemin = tout le site
);

// ============================================================================
// ÉTAPE 6 : REDIRECTION VERS LA PAGE D'ACCUEIL
// ============================================================================
// Après la déconnexion, on redirige l'utilisateur vers la page d'accueil
header('Location: index.php');
exit(); // Arrêter l'exécution du script pour éviter tout code supplémentaire

// ============================================================================
// RÉSUMÉ DU PROCESSUS DE DÉCONNEXION :
// ============================================================================
// 
// POURQUOI TOUTES CES ÉTAPES ?
// Une déconnexion sécurisée doit nettoyer TOUTES les traces de la session :
// 
// 1. 📊 BDD : Enregistrer l'heure de déconnexion (statistiques)
// 2. 🧹 Session PHP : Vider $_SESSION (données en mémoire)
// 3. 🍪 Cookie session : Supprimer PHPSESSID (identification navigateur)
// 4. 💾 Fichier session : Détruire le fichier sur le serveur
// 5. 🔑 Cookie remember : Supprimer le token "Se souvenir de moi"
// 6. ↩️ Redirection : Renvoyer vers la page d'accueil
// 
// SÉCURITÉ :
// ✅ Impossible de rester connecté après logout
// ✅ Aucune donnée sensible ne reste en mémoire
// ✅ Les cookies sont correctement supprimés
// ✅ Même en cas d'erreur BDD, la déconnexion fonctionne
// 
// BONNES PRATIQUES :
// - Toujours vider $_SESSION avant session_destroy()
// - Supprimer les cookies avant la redirection
// - Utiliser try-catch pour les opérations BDD non critiques
// - Ne jamais bloquer une déconnexion à cause d'une erreur technique
// ============================================================================
?>