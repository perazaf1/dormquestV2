<?php
// ============================================================================
// FICHIER DE CONFIGURATION GLOBAL
// Fichier : includes/config.php
// 
// Ce fichier centralise TOUTES les configurations du site DormQuest :
// - Paramètres de connexion à la base de données
// - Chemins des dossiers
// - Limites et restrictions (taille fichiers, timeouts, etc.)
// - Fonctions utilitaires réutilisables partout dans le projet
// ============================================================================

// ============================================================================
// SÉCURITÉ : Empêcher l'accès direct
// ============================================================================
// Si quelqu'un essaie d'accéder directement à ce fichier via l'URL
// (ex: http://localhost/dormquest/includes/config.php), on bloque l'accès
// Cette constante doit être définie dans les pages qui incluent ce fichier
if (!defined('ACCESS_ALLOWED')) {
    die('Accès direct interdit');
}

// ============================================================================
// SECTION 1 : CONFIGURATION DE LA BASE DE DONNÉES
// ============================================================================
// Ces constantes définissent comment se connecter à MySQL/MariaDB
// Les constantes (define) sont des valeurs fixes qu'on ne peut pas modifier

define('DB_HOST', 'localhost');        // Adresse du serveur de base de données
define('DB_NAME', 'dormquest');        // Nom de la base de données à utiliser

// ============================================================================
// SECTION : CONFIGURATION EMAIL
// ============================================================================
// Configuration pour l'envoi d'emails (réinitialisation de mot de passe, etc.)
define('MAIL_FROM', 'noreply@dormquest.com');
define('MAIL_FROM_NAME', 'DormQuest');
define('SITE_URL', 'http://localhost/dormquestV2'); // URL de base du site (sans slash à la fin)
define('DB_USER', 'root');             // Nom d'utilisateur MySQL (root = admin local)
define('DB_PASS', '');                 // Mot de passe (vide en local avec XAMPP/WAMP)
define('DB_CHARSET', 'utf8mb4');       // Encodage pour supporter tous les caractères (emojis inclus)

// ============================================================================
// SECTION 2 : INFORMATIONS DU SITE
// ============================================================================
// Ces informations sont utilisées dans les pages (titre, emails, etc.)

define('SITE_NAME', 'DormQuest');                                      // Nom du site
define('SITE_SLOGAN', 'Trouvez le logement parfait pour vos études !'); // Slogan affiché
define('SITE_URL', 'http://localhost/dormquest');                       // URL de base du site
define('SITE_EMAIL', 'contact@dormquest.fr');                           // Email de contact

// ============================================================================
// SECTION 3 : CHEMINS DES DOSSIERS
// ============================================================================
// Ces chemins permettent de localiser les fichiers uploadés (photos de profil, annonces)

define('ROOT_PATH', dirname(__DIR__));                    // Dossier racine du projet
define('UPLOAD_PATH', ROOT_PATH . '/uploads');            // Dossier principal des uploads
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . '/profiles'); // Photos de profil des utilisateurs
define('ANNONCE_UPLOAD_PATH', UPLOAD_PATH . '/annonces'); // Photos des annonces de logement

// Créer automatiquement les dossiers s'ils n'existent pas encore
// 0777 = permissions complètes (lecture, écriture, exécution)
// true = créer tous les sous-dossiers nécessaires
if (!is_dir(PROFILE_UPLOAD_PATH)) {
    mkdir(PROFILE_UPLOAD_PATH, 0777, true);
}
if (!is_dir(ANNONCE_UPLOAD_PATH)) {
    mkdir(ANNONCE_UPLOAD_PATH, 0777, true);
}

// ============================================================================
// SECTION 4 : LIMITES POUR LES UPLOADS DE FICHIERS
// ============================================================================

define('MAX_FILE_SIZE', 2 * 1024 * 1024); // Taille max : 2 Mo (en octets)

// Types MIME autorisés (identifiants techniques des formats d'image)
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Extensions de fichiers autorisées (ce que l'utilisateur voit)
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// ============================================================================
// SECTION 5 : CONFIGURATION DES SESSIONS ET SÉCURITÉ
// ============================================================================

define('SESSION_TIMEOUT', 3600);                // Durée session : 1 heure (3600 secondes)
define('REMEMBER_ME_DURATION', 30 * 24 * 3600); // "Se souvenir de moi" : 30 jours

// Règles de sécurité pour les mots de passe et connexions
define('PASSWORD_MIN_LENGTH', 8);        // Minimum 8 caractères pour les mots de passe
define('MAX_LOGIN_ATTEMPTS', 5);         // Bloquer après 5 tentatives de connexion échouées
define('LOGIN_TIMEOUT', 15 * 60);        // Durée du blocage : 15 minutes

// ============================================================================
// SECTION 6 : CONFIGURATION DES ANNONCES
// ============================================================================

define('ANNONCES_PER_PAGE', 12);        // Nombre d'annonces affichées par page (pagination)
define('MAX_PHOTOS_PER_ANNONCE', 8);    // Maximum 8 photos par annonce de logement

// Types de logements disponibles sur le site
// Format : 'clé_technique' => 'Libellé affiché'
define('TYPES_LOGEMENT', [
    'studio' => 'Studio',
    'colocation' => 'Colocation',
    'residence_etudiante' => 'Résidence étudiante',
    'chambre_habitant' => 'Chambre chez l\'habitant'
]);

// Étiquettes énergétiques (performance énergétique du logement)
// A = très économe, G = très énergivore
define('ETIQUETTES_ENERGIE', ['A', 'B', 'C', 'D', 'E', 'F', 'G']);

// ============================================================================
// SECTION 7 : TYPES DE LOUEURS
// ============================================================================
// Catégories de propriétaires qui peuvent publier des annonces

define('TYPES_LOUEUR', [
    'particulier' => 'Particulier',            // Propriétaire individuel
    'agence' => 'Agence immobilière',           // Agence professionnelle
    'organisme' => 'Organisme',                 // Association, fondation, etc.
    'crous' => 'CROUS'                          // Centre Régional des Œuvres Universitaires
]);

// ============================================================================
// SECTION 8 : STATUTS (ÉTATS) DES ÉLÉMENTS
// ============================================================================

// Statuts des annonces de logement
define('STATUTS_ANNONCE', [
    'active' => 'Active',        // Annonce visible et disponible
    'archivee' => 'Archivée'     // Annonce masquée (louée ou retirée)
]);

// Statuts des candidatures des étudiants
define('STATUTS_CANDIDATURE', [
    'en_attente' => 'En attente', // Candidature envoyée, en cours d'examen
    'acceptee' => 'Acceptée',      // Candidature retenue par le loueur
    'refusee' => 'Refusée',        // Candidature rejetée
    'annulee' => 'Annulée'         // Candidature annulée par l'étudiant
]);

// ============================================================================
// SECTION 9 : CONFIGURATION DES EMAILS
// ============================================================================
// Paramètres pour l'envoi d'emails automatiques (notifications, confirmations)

define('EMAIL_FROM', 'noreply@dormquest.fr');  // Adresse d'expéditeur
define('EMAIL_FROM_NAME', 'DormQuest');        // Nom affiché comme expéditeur

// ============================================================================
// SECTION 10 : MODE DEBUG (DÉVELOPPEMENT)
// ============================================================================
// ATTENTION : À DÉSACTIVER EN PRODUCTION !

define('DEBUG_MODE', true); // true = afficher les erreurs, false = masquer

if (DEBUG_MODE) {
    // En mode développement : afficher toutes les erreurs pour déboguer
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // En production : masquer les erreurs pour la sécurité
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ============================================================================
// SECTION 11 : FUSEAU HORAIRE
// ============================================================================
// Définir le fuseau horaire pour que toutes les dates soient cohérentes
date_default_timezone_set('Europe/Paris');

// ============================================================================
// SECTION 12 : FONCTIONS UTILITAIRES GLOBALES
// ============================================================================
// Ces fonctions sont disponibles dans tout le projet une fois config.php inclus

/**
 * Génère une URL complète à partir d'un chemin relatif
 * 
 * Exemple : url('annonces/voir.php?id=5') 
 * Retourne : http://localhost/dormquest/annonces/voir.php?id=5
 */
function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Redirige l'utilisateur vers une autre page
 * 
 * Exemple : redirect('login.php') redirige vers la page de connexion
 * exit() arrête l'exécution pour éviter que du code s'exécute après la redirection
 */
function redirect($path = '') {
    header('Location: ' . url($path));
    exit();
}

/**
 * Sécurise une chaîne de caractères avant de l'afficher en HTML
 * 
 * Empêche les attaques XSS (injection de code malveillant)
 * Exemple : e('<script>alert("hack")</script>') 
 * Affiche le texte brut au lieu d'exécuter le script
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Formate un prix en euros avec le bon séparateur français
 * 
 * Exemple : format_prix(450.50) retourne "450,50 €"
 * number_format(nombre, décimales, séparateur_décimal, séparateur_milliers)
 */
function format_prix($prix) {
    return number_format($prix, 2, ',', ' ') . ' €';
}

/**
 * Formate une date au format français (jour/mois/année)
 * 
 * Exemple : format_date('2024-12-11') retourne "11/12/2024"
 */
function format_date($date) {
    if (empty($date)) return ''; // Si pas de date, retourner vide
    
    // Convertir en timestamp si ce n'est pas déjà un nombre
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    
    return date('d/m/Y', $timestamp);
}

/**
 * Formate une date avec l'heure au format français
 * 
 * Exemple : format_datetime('2024-12-11 14:30:00') retourne "11/12/2024 à 14:30"
 */
function format_datetime($datetime) {
    if (empty($datetime)) return '';
    
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    
    return date('d/m/Y à H:i', $timestamp);
}

/**
 * Génère un nom de fichier unique pour éviter les conflits
 * 
 * Exemple : generate_unique_filename('jpg') 
 * Retourne : "674a2c8f3d1e7_1732896911.jpg"
 * 
 * uniqid('', true) = ID unique basé sur l'horloge
 * time() = timestamp actuel pour plus d'unicité
 */
function generate_unique_filename($extension) {
    return uniqid('', true) . '_' . time() . '.' . $extension;
}

/**
 * Vérifie qu'un fichier uploadé est une image valide
 * 
 * Effectue plusieurs vérifications de sécurité :
 * 1. Pas d'erreur lors de l'upload
 * 2. Taille inférieure à la limite autorisée
 * 3. Type MIME autorisé (vérification côté serveur du type de fichier)
 * 4. Extension autorisée (jpg, jpeg, png)
 * 
 * Retourne true si toutes les vérifications passent, false sinon
 */
function is_valid_image($file) {
    // Vérifier qu'il n'y a pas eu d'erreur pendant l'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Vérifier que le fichier ne dépasse pas la taille maximale
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Vérifier le type MIME (type technique du fichier)
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    // Extraire et vérifier l'extension du fichier
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
        return false;
    }
    
    // Toutes les vérifications sont OK
    return true;
}

/**
 * Tronque (coupe) un texte trop long et ajoute "..." à la fin
 * 
 * Exemple : truncate("Ceci est un texte très long", 10)
 * Retourne : "Ceci est u..."
 * 
 * mb_strlen et mb_substr supportent les caractères accentués et emojis
 */
function truncate($text, $length = 100, $suffix = '...') {
    // Si le texte est déjà assez court, le retourner tel quel
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    // Couper le texte et ajouter le suffixe (...)
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Vérifie si une valeur fait partie d'un ensemble de valeurs autorisées
 * 
 * Utile pour valider les données de formulaires
 * Exemple : is_valid_value('studio', ['studio', 'colocation'])
 * Retourne : true
 * 
 * Le paramètre true dans in_array active la comparaison stricte (type + valeur)
 */
function is_valid_value($value, $allowed_values) {
    return in_array($value, $allowed_values, true);
}

// ============================================================================
// RÉSUMÉ DE CE FICHIER :
// ============================================================================
// Ce fichier config.php est le "cerveau" de la configuration du site.
// 
// Il contient :
// ✅ Tous les paramètres techniques (BDD, chemins, limites)
// ✅ Les valeurs fixes utilisées dans tout le projet (types, statuts)
// ✅ Des fonctions utilitaires réutilisables partout
// 
// AVANTAGES :
// - Centralisation : changer une config = modifier un seul endroit
// - Cohérence : toutes les pages utilisent les mêmes valeurs
// - Sécurité : fonctions de validation et protection incluses
// - Maintenabilité : facile à maintenir et à comprendre
// ============================================================================
?>