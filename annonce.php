<?php
// annonce.php - D�tail d'une annonce
session_start();
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';
// V�rifier si l'utilisateur est connect�
$isLoggedIn = isset($_SESSION['user_id']);
$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de l'annonce - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Contenu de la page détail annonce à ajouter ici -->

    <?php include 'includes/footer.php'; ?>
</body>

</html>