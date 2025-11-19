<?php
// includes/auth.php - Gestion de l'authentification et des sessions

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * @param string $role
 * @return bool
 */
function has_role($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Vérifie si l'utilisateur est un étudiant
 * @return bool
 */
function is_etudiant() {
    return has_role('etudiant');
}

/**
 * Vérifie si l'utilisateur est un loueur
 * @return bool
 */
function is_loueur() {
    return has_role('loueur');
}

/**
 * Récupère l'ID de l'utilisateur connecté
 * @return int|null
 */
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Récupère le rôle de l'utilisateur connecté
 * @return string|null
 */
function get_user_role() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Récupère le prénom de l'utilisateur connecté
 * @return string|null
 */
function get_user_prenom() {
    return $_SESSION['user_prenom'] ?? null;
}

/**
 * Récupère le nom complet de l'utilisateur connecté
 * @return string
 */
function get_user_fullname() {
    $prenom = $_SESSION['user_prenom'] ?? '';
    $nom = $_SESSION['user_nom'] ?? '';
    return trim($prenom . ' ' . $nom);
}

/**
 * Récupère la photo de profil de l'utilisateur connecté
 * @return string URL de la photo ou photo par défaut
 */
function get_user_photo() {
    if (isset($_SESSION['user_photo']) && !empty($_SESSION['user_photo'])) {
        return $_SESSION['user_photo'];
    }
    // Photo par défaut
    return 'img/default-avatar.png';
}

/**
 * Exige que l'utilisateur soit connecté
 * Redirige vers login.php si non connecté
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

/**
 * Exige que l'utilisateur ait un rôle spécifique
 * @param string $role
 * @param string $redirect_url URL de redirection si mauvais rôle
 */
function require_role($role, $redirect_url = 'index.php') {
    require_login();
    
    if (!has_role($role)) {
        header('Location: ' . $redirect_url);
        exit();
    }
}

/**
 * Exige que l'utilisateur soit un étudiant
 */
function require_etudiant() {
    require_role('etudiant', 'dashboard-loueur.php');
}

/**
 * Exige que l'utilisateur soit un loueur
 */
function require_loueur() {
    require_role('loueur', 'dashboard-etudiant.php');
}

/**
 * Déconnecte l'utilisateur
 */
function logout() {
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Détruire le cookie de session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Détruire le cookie "Se souvenir de moi"
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Détruire la session
    session_destroy();
}

/**
 * Vérifie si la session est expirée
 * @param int $timeout Durée d'inactivité maximale en secondes (défaut: 1 heure)
 * @return bool
 */
function is_session_expired($timeout = 3600) {
    if (isset($_SESSION['login_time'])) {
        $inactive_time = time() - $_SESSION['login_time'];
        
        if ($inactive_time > $timeout) {
            return true;
        }
        
        // Mettre à jour le timestamp d'activité
        $_SESSION['login_time'] = time();
    }
    
    return false;
}

/**
 * Vérifie la session et déconnecte si expirée
 */
function check_session_timeout() {
    if (is_logged_in() && is_session_expired()) {
        logout();
        header('Location: login.php?error=session_expired');
        exit();
    }
}

/**
 * Récupère les informations complètes de l'utilisateur depuis la BDD
 * @param PDO $pdo
 * @return array|false
 */
function get_user_info($pdo) {
    if (!is_logged_in()) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM utilisateurs WHERE id = ?
        ");
        $stmt->execute([get_user_id()]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Met à jour les informations de session depuis la BDD
 * Utile après modification du profil
 * @param PDO $pdo
 */
function refresh_session($pdo) {
    $user = get_user_info($pdo);
    
    if ($user) {
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_photo'] = $user['photoDeProfil'];
    }
}

/**
 * Vérifie le token CSRF
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Génère un token CSRF
 * @return string
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Affiche un champ caché pour le token CSRF
 */
function csrf_field() {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Redirige vers la page appropriée selon le rôle
 */
function redirect_by_role() {
    if (is_etudiant()) {
        header('Location: dashboard-etudiant.php');
    } elseif (is_loueur()) {
        header('Location: dashboard-loueur.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

// Vérifier automatiquement l'expiration de session
check_session_timeout();
?>