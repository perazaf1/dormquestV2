<?php
// logout.php - Déconnexion et redirection vers la page d'accueil
session_start();

// Si un utilisateur est connecté, on peut mettre à jour éventuellement la BDD
if (isset($_SESSION['user_id'])) {
	// Mettre à jour la dernière déconnexion est optionnel. Si la BDD est disponible,
	// on peut la charger. Ce n'est pas obligatoire pour la déconnexion.
	if (file_exists(__DIR__ . '/includes/db.php')) {
		try {
			require_once __DIR__ . '/includes/db.php';
			// Optionnel : mettre à jour une colonne 'derniereConnexion' si souhaité
			$stmt = $pdo->prepare("UPDATE utilisateurs SET derniereConnexion = NOW() WHERE id = ?");
			$stmt->execute([$_SESSION['user_id']]);
		} catch (Exception $e) {
			// Ne pas interrompre la déconnexion si la mise à jour échoue
		}
	}
}

// Clear all session variables
$_SESSION = [];

// If session uses cookies, clear the session cookie
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}

// Destroy the session
session_destroy();

// Clear application-specific cookies (remember token)
setcookie('remember_token', '', time() - 42000, '/');

// Redirect to homepage
header('Location: index.php');
exit();
