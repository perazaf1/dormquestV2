<?php
// index.php - Landing Page DormQuest
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
    <meta name="description" content="DormQuest - Trouvez le logement parfait pour vos études ! Plateforme de mise en relation entre étudiants et loueurs.">
    <title>DormQuest - Trouvez le logement parfait pour vos études !</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/dashboard-loueur.css.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Contenu de la page à ajouter ici -->

    <?php include 'includes/footer.php'; ?>
</body>
</html>
