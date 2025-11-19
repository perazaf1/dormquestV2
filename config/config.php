<?php
// includes/config.php - Configuration globale du site

// Empêcher l'accès direct à ce fichier
if (!defined('ACCESS_ALLOWED')) {
    die('Accès direct interdit');
}

// ==========================================
// CONFIGURATION DE LA BASE DE DONNÉES
// ==========================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'dormquest');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ==========================================
// CONFIGURATION DU SITE
// ==========================================
define('SITE_NAME', 'DormQuest');
define('SITE_SLOGAN', 'Trouvez le logement parfait pour vos études !');
define('SITE_URL', 'http://localhost/dormquest');
define('SITE_EMAIL', 'contact@dormquest.fr');

// ==========================================
// CONFIGURATION DES CHEMINS
// ==========================================
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . '/profiles');
define('ANNONCE_UPLOAD_PATH', UPLOAD_PATH . '/annonces');

// Créer les dossiers s'ils n'existent pas
if (!is_dir(PROFILE_UPLOAD_PATH)) {
    mkdir(PROFILE_UPLOAD_PATH, 0777, true);
}
if (!is_dir(ANNONCE_UPLOAD_PATH)) {
    mkdir(ANNONCE_UPLOAD_PATH, 0777, true);
}

// ==========================================
// CONFIGURATION DES UPLOADS
// ==========================================
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// ==========================================
// CONFIGURATION DES SESSIONS
// ==========================================
define('SESSION_TIMEOUT', 3600); // 1 heure en secondes
define('REMEMBER_ME_DURATION', 30 * 24 * 3600); // 30 jours

// ==========================================
// CONFIGURATION DE LA SÉCURITÉ
// ==========================================
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 15 * 60); // 15 minutes

// ==========================================
// CONFIGURATION DES ANNONCES
// ==========================================
define('ANNONCES_PER_PAGE', 12);
define('MAX_PHOTOS_PER_ANNONCE', 8);

// Types de logements disponibles
define('TYPES_LOGEMENT', [
    'studio' => 'Studio',
    'colocation' => 'Colocation',
    'residence_etudiante' => 'Résidence étudiante',
    'chambre_habitant' => 'Chambre chez l\'habitant'
]);

// Étiquettes énergétiques
define('ETIQUETTES_ENERGIE', ['A', 'B', 'C', 'D', 'E', 'F', 'G']);

// ==========================================
// CONFIGURATION DES TYPES DE LOUEURS
// ==========================================
define('TYPES_LOUEUR', [
    'particulier' => 'Particulier',
    'agence' => 'Agence immobilière',
    'organisme' => 'Organisme',
    'crous' => 'CROUS'
]);

// ==========================================
// CONFIGURATION DES STATUTS
// ==========================================
define('STATUTS_ANNONCE', [
    'active' => 'Active',
    'archivee' => 'Archivée'
]);

define('STATUTS_CANDIDATURE', [
    'en_attente' => 'En attente',
    'acceptee' => 'Acceptée',
    'refusee' => 'Refusée',
    'annulee' => 'Annulée'
]);

// ==========================================
// CONFIGURATION DES EMAILS
// ==========================================
define('EMAIL_FROM', 'noreply@dormquest.fr');
define('EMAIL_FROM_NAME', 'DormQuest');

// ==========================================
// MODE DEBUG (À DÉSACTIVER EN PRODUCTION !)
// ==========================================
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ==========================================
// TIMEZONE
// ==========================================
date_default_timezone_set('Europe/Paris');

// ==========================================
// FONCTIONS UTILITAIRES GLOBALES
// ==========================================

/**
 * Génère l'URL complète d'une page
 */
function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Redirige vers une page
 */
function redirect($path = '') {
    header('Location: ' . url($path));
    exit();
}

/**
 * Sécurise une chaîne pour l'affichage HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 */
function has_role($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Vérifie si l'utilisateur est un étudiant
 */
function is_etudiant() {
    return has_role('etudiant');
}

/**
 * Vérifie si l'utilisateur est un loueur
 */
function is_loueur() {
    return has_role('loueur');
}

/**
 * Formate un prix en euros
 */
function format_prix($prix) {
    return number_format($prix, 2, ',', ' ') . ' €';
}

/**
 * Formate une date au format français
 */
function format_date($date) {
    if (empty($date)) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Formate une date et heure au format français
 */
function format_datetime($datetime) {
    if (empty($datetime)) return '';
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date('d/m/Y à H:i', $timestamp);
}

/**
 * Génère un token CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Génère un nom de fichier unique
 */
function generate_unique_filename($extension) {
    return uniqid('', true) . '_' . time() . '.' . $extension;
}

/**
 * Vérifie si un fichier est une image valide
 */
function is_valid_image($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
        return false;
    }
    
    return true;
}

/**
 * Tronque un texte à une longueur donnée
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Vérifie si une valeur est dans un tableau de valeurs autorisées
 */
function is_valid_value($value, $allowed_values) {
    return in_array($value, $allowed_values, true);
}
?>