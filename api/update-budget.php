<?php
// api/update-budget.php - Sauvegarde rapide du budget mensuel pour l'utilisateur connecté
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/auth.php';
require_once '../includes/db.php';

// Vérifier la session
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit();
}

// Vérifier le token CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'Token CSRF invalide']);
    exit();
}

$budget_raw = trim($_POST['budget'] ?? '');
if ($budget_raw === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Budget vide']);
    exit();
}

// Normaliser le budget (virgule ou point)
$budget_norm = str_replace(',', '.', $budget_raw);
if (!is_numeric($budget_norm)) {
    http_response_code(400);
    echo json_encode(['error' => 'Budget invalide']);
    exit();
}

$budget = floatval($budget_norm);
if ($budget < 300) {
    http_response_code(400);
    echo json_encode(['error' => 'Le budget doit être au minimum 300']);
    exit();
}

$budget = number_format($budget, 2, '.', '');

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE utilisateurs SET budget = ? WHERE id = ?");
    $stmt->execute([$budget, get_user_id()]);
    $pdo->commit();

    // Mettre à jour la session si besoin
    refresh_session($pdo);

    echo json_encode(['success' => true, 'budget' => $budget]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }

    // Si la colonne `budget` est absente, tenter de la créer automatiquement
    $msg = $e->getMessage();
    if (stripos($msg, "Unknown column 'budget'") !== false || stripos($msg, 'Unknown column: 1054') !== false) {
        try {
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN budget DECIMAL(10,2) NULL");
            // Retenter la mise à jour après ajout de colonne
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE utilisateurs SET budget = ? WHERE id = ?");
            $stmt->execute([$budget, get_user_id()]);
            $pdo->commit();

            refresh_session($pdo);
            echo json_encode(['success' => true, 'budget' => $budget, 'note' => 'colonne_budget_ajoutee']);
            exit();
        } catch (PDOException $e2) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            http_response_code(500);
            echo json_encode(['error' => 'Erreur BD (ajout colonne): ' . $e2->getMessage()]);
            exit();
        }
    }

    http_response_code(500);
    echo json_encode(['error' => 'Erreur BD: ' . $msg]);
}
