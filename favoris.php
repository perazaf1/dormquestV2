<?php
// favoris.php - Mes favoris (�tudiant)
session_start();

require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';

// Exiger que l'utilisateur soit un étudiant
require_etudiant();

// Récupérer les favoris de l'étudiant
$etudiantId = get_user_id();
try {
    $stmt = $pdo->prepare(
        "SELECT f.id AS idFavori, f.dateAjout, a.id AS idAnnonce, a.titre, a.ville, a.prixMensuel,
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
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main class="container">
        <h1>Mes favoris</h1>

        <?php if (isset($error_db)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_db); ?></div>
        <?php endif; ?>

        <?php if (empty($favoris)): ?>
            <p>Vous n'avez aucun favori pour le moment.</p>
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
                            <tr id="fav-row-<?php echo (int)$f['idFavori']; ?>">
                                <td>
                                    <?php if (!empty($f['photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($f['photo']); ?>" alt="" style="width:70px;height:50px;object-fit:cover;margin-right:8px;vertical-align:middle;border-radius:4px;">
                                    <?php endif; ?>
                                    <a href="annonce.php?id=<?php echo (int)$f['idAnnonce']; ?>"><?php echo htmlspecialchars($f['titre']); ?></a>
                                </td>
                                <td><?php echo htmlspecialchars($f['ville']); ?></td>
                                <td><?php echo number_format($f['prixMensuel'], 2, ',', ' '); ?> €</td>
                                <td><?php echo htmlspecialchars($f['dateAjout']); ?></td>
                                <td>
                                    <button class="btn btn-warning remove-fav" data-annonce-id="<?php echo (int)$f['idAnnonce']; ?>" data-fav-id="<?php echo (int)$f['idFavori']; ?>">Retirer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <script>
    // Attacher les handlers pour retirer un favori via l'API
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.remove-fav').forEach(function(btn) {
            btn.addEventListener('click', function () {
                if (!confirm('Retirer cette annonce de vos favoris ?')) return;

                var annonceId = parseInt(this.dataset.annonceId, 10);
                var favId = parseInt(this.dataset.favId, 10);
                var row = document.getElementById('fav-row-' + favId);

                fetch('api/toggle-favori.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ annonce_id: annonceId, action: 'remove' })
                }).then(function(resp) { return resp.json(); })
                .then(function(json) {
                    if (json.success) {
                        // retirer la ligne du tableau
                        if (row) row.parentNode.removeChild(row);
                    } else {
                        alert('Erreur : ' + (json.message || 'Impossible de retirer le favori'));
                    }
                }).catch(function(err){
                    console.error(err);
                    alert('Erreur réseau lors de la suppression.');
                });
            });
        });
    });
    </script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
