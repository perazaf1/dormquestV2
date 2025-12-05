<?php
// dashboard-loueur.php - Tableau de bord loueur
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
    <title>Mon tableau de bord - DormQuest</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Contenu du dashboard loueur à ajouter ici -->

    <?php include 'includes/footer.php'; ?>
</body>
</html>
