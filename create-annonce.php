<?php
// create-annonce.php - Créer une annonce (loueur)
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';
session_start();

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une annonce - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/stylecss">
    
    <link rel="stylesheet" href="css/forms.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Contenu de la page création d'annonce à ajouter ici -->

    <?php include 'includes/footer.php'; ?>
</body>
</html>
