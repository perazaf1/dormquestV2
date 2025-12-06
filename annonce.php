<?php
// annonce.php - D�tail d'une annonce
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
    <title>D�tail de l'annonce - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Contenu de la page d�tail annonce � ajouter ici -->

    <?php include 'includes/footer.php'; ?>
</body>
</html>
