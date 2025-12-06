<?php
// create-annonce.php - Cr�er une annonce (loueur)
session_start();

// V�rifier si l'utilisateur est connect�
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cr�er une annonce - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/stylecss">
    <link rel="stylesheet" href="css/forms.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Contenu de la page cr�ation d'annonce � ajouter ici -->

    <?php include 'includes/footer.php'; ?>
</body>
</html>
