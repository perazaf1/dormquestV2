<?php
// favoris.php - Mes favoris (étudiant)
session_start();

require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';

// Exiger que l'utilisateur soit un étudiant
require_etudiant();

// Récupérer les favoris de l'étudiant
$etudiantId = get_user_id();
$message_success = isset($_GET['success']) ? $_GET['success'] : '';

try {
    $stmt = $pdo->prepare(
        "SELECT f.id AS idFavori, f.dateAjout, a.id AS idAnnonce, a.titre, a.ville, a.prixMensuel, a.typeLogement,
                (SELECT cheminPhoto FROM photos_annonces p WHERE p.idAnnonce = a.id ORDER BY p.ordre ASC, p.id ASC LIMIT 1) AS photo
         FROM favoris f
         JOIN annonces a ON f.idAnnonce = a.id
         WHERE f.idEtudiant = ?
         ORDER BY f.dateAjout DESC"
    );
    $stmt->execute([$etudiantId]);
    $favoris = $stmt->fetchAll();
} catch (PDOException $e) {
    $favoris = [];
    $error_db = 'Erreur lors de la récupération des favoris.';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes favoris - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/favoris.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main class="container">
        <h1>Mes favoris</h1>

        <?php if ($message_success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message_success); ?></div>
        <?php endif; ?>

        <?php if (isset($error_db)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_db); ?></div>
        <?php endif; ?>

        <?php if (empty($favoris)): ?>
            <div class="empty-state">
                <i class="fa-regular fa-heart" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>Vous n'avez aucun favori pour le moment.</p>
                <p style="color: #666; font-size: 0.9rem;">Parcourez les <a href="annonces.php" style="color: #0b66c3;">annonces</a> et ajoutez vos logements préférés en cliquant sur le cœur.</p>
            </div>
        <?php else: ?>
            <div class="candidatures-list">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Annonce</th>
                            <th>Ville</th>
                            <th>Prix</th>
                            <th>Ajouté le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($favoris as $f): ?>
                            <?php 
                            // Formater la date en français
                            $dateAjout = new DateTime($f['dateAjout']);
                            $dateFormatee = $dateAjout->format('d/m/Y');
                            
                            // Formater le type de logement
                            $typesLabels = [
                                'studio' => 'Studio',
                                'colocation' => 'Colocation',
                                'residence_etudiante' => 'Résidence étudiante',
                                'chambre_habitant' => 'Chez l\'habitant'
                            ];
                            $typeLabel = $typesLabels[$f['typeLogement']] ?? ucfirst($f['typeLogement']);
                            ?>
                            <tr id="fav-row-<?php echo (int)$f['idFavori']; ?>">
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <?php if (!empty($f['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($f['photo']); ?>" alt="" style="width:70px;height:50px;object-fit:cover;margin-right:12px;border-radius:4px;">
                                        <?php else: ?>
                                            <div style="width:70px;height:50px;background:#f0f0f0;margin-right:12px;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                                                <i class="fa-regular fa-image" style="color:#999;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <a href="annonce.php?id=<?php echo (int)$f['idAnnonce']; ?>" style="font-weight:500;"><?php echo htmlspecialchars($f['titre']); ?></a>
                                            <div style="font-size:0.85rem;color:#666;margin-top:2px;"><?php echo $typeLabel; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($f['ville']); ?></td>
                                <td style="font-weight:500;color:#2c5282;"><?php echo number_format($f['prixMensuel'], 0, ',', ' '); ?> €<span style="font-size:0.85rem;color:#666;">/mois</span></td>
                                <td><?php echo $dateFormatee; ?></td>
                                <td>
                                    <button class="btn btn-warning remove-fav" data-annonce-id="<?php echo (int)$f['idAnnonce']; ?>" data-fav-id="<?php echo (int)$f['idFavori']; ?>">
                                        <i class="fa-solid fa-trash-can"></i> Retirer
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <script src="js/favoris.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
