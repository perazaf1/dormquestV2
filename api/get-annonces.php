<?php
/**
 * API - Récupérer les annonces avec filtres
 * Retourne les annonces actives avec leurs photos et critères
 */
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

try {
    // Récupérer les filtres depuis les paramètres GET
    $filters = [];

    if (isset($_GET['ville']) && !empty($_GET['ville'])) {
        $filters['ville'] = $_GET['ville'];
    }

    if (isset($_GET['typeLogement']) && !empty($_GET['typeLogement'])) {
        $filters['typeLogement'] = $_GET['typeLogement'];
    }

    if (isset($_GET['prixMin']) && is_numeric($_GET['prixMin'])) {
        $filters['prixMin'] = intval($_GET['prixMin']);
    }

    if (isset($_GET['prixMax']) && is_numeric($_GET['prixMax'])) {
        $filters['prixMax'] = intval($_GET['prixMax']);
    }

    // Récupérer les annonces avec les filtres
    $annonces = search_annonces($pdo, $filters);

    // Pour chaque annonce, récupérer les photos et critères
    foreach ($annonces as &$annonce) {
        // Récupérer les photos
        $annonce['photos'] = get_photos_annonce($pdo, $annonce['id']);

        // Récupérer les critères
        $annonce['criteres'] = get_criteres_annonce($pdo, $annonce['id']);

        // Vérifier si l'annonce est dans les favoris (si l'utilisateur est connecté et est étudiant)
        if (is_logged_in() && get_user_role() === 'etudiant') {
            $annonce['isFavori'] = is_favori($pdo, get_user_id(), $annonce['id']);
        } else {
            $annonce['isFavori'] = false;
        }

        // Vérifier si l'utilisateur a déjà postulé (si étudiant connecté)
        if (is_logged_in() && get_user_role() === 'etudiant') {
            $annonce['hasApplied'] = has_candidature($pdo, get_user_id(), $annonce['id']);
        } else {
            $annonce['hasApplied'] = false;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $annonces,
        'count' => count($annonces)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la récupération des annonces',
        'message' => $e->getMessage()
    ]);
}
