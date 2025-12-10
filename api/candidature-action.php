<?php
// api/candidature-action.php - Gère acceptation / refus / annulation de candidatures
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$action = $_POST['action'] ?? '';
$candidature_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$redirect = $_POST['redirect'] ?? (is_loueur() ? '../dashboard-loueur.php' : '../dashboard-etudiant.php');

// CSRF check (optional: allow if token not provided to remain compatible)
$csrf = $_POST['csrf_token'] ?? null;
if ($csrf !== null && !verify_csrf_token($csrf)) {
    $msg = 'Token CSRF invalide.';
    header('Location: ' . $redirect . '?error=' . urlencode($msg));
    exit();
}

if ($candidature_id <= 0) {
    header('Location: ' . $redirect . '?error=' . urlencode('Candidature introuvable.'));
    exit();
}

try {
    if ($action === 'accept' || $action === 'refuse') {
        // Only loueur can accept/refuse and must own the annonce
        if (!is_loueur()) {
            header('Location: ' . $redirect . '?error=' . urlencode('Accès refusé.'));
            exit();
        }

        $stmt = $pdo->prepare("SELECT c.*, a.idLoueur FROM candidatures c JOIN annonces a ON c.idAnnonce = a.id WHERE c.id = ?");
        $stmt->execute([$candidature_id]);
        $c = $stmt->fetch();

        if (!$c) {
            header('Location: ' . $redirect . '?error=' . urlencode('Candidature introuvable.'));
            exit();
        }

        if ($c['idLoueur'] != get_user_id()) {
            header('Location: ' . $redirect . '?error=' . urlencode('Vous n\'êtes pas autorisé à gérer cette candidature.'));
            exit();
        }

        $newStatus = ($action === 'accept') ? 'acceptee' : 'refusee';
        $stmt = $pdo->prepare("UPDATE candidatures SET statut = ?, dateReponse = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $candidature_id]);

        header('Location: ' . $redirect . '?success=' . urlencode('Candidature mise à jour.'));
        exit();

    } elseif ($action === 'cancel') {
        // Only the student who sent the candidature can cancel
        if (!is_etudiant()) {
            header('Location: ' . $redirect . '?error=' . urlencode('Accès refusé.'));
            exit();
        }

        $stmt = $pdo->prepare("SELECT * FROM candidatures WHERE id = ? AND idEtudiant = ?");
        $stmt->execute([$candidature_id, get_user_id()]);
        $c = $stmt->fetch();

        if (!$c) {
            header('Location: ' . $redirect . '?error=' . urlencode('Candidature introuvable ou accès non autorisé.'));
            exit();
        }

        $stmt = $pdo->prepare("UPDATE candidatures SET statut = 'annulee', dateReponse = NOW() WHERE id = ?");
        $stmt->execute([$candidature_id]);

        header('Location: ' . $redirect . '?success=' . urlencode('Candidature annulée.'));
        exit();

    } else {
        header('Location: ' . $redirect . '?error=' . urlencode('Action inconnue.'));
        exit();
    }
} catch (PDOException $e) {
    header('Location: ' . $redirect . '?error=' . urlencode('Erreur serveur : ' . $e->getMessage()));
    exit();
}
