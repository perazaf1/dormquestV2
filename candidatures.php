<?php
// candidatures.php - Mes candidatures (�tudiant)
session_start();
require_once __DIR__ . '/includes/db.php';
require_once 'includes/auth.php';

// Exiger que l'utilisateur soit un étudiant
require_etudiant();

// Récupérer l'ID de l'étudiant connecté
$etudiantId = get_user_id();

// Récupérer les candidatures de l'étudiant
try {
    $stmt = $pdo->prepare(
        "SELECT c.*, a.titre, a.ville, a.prixMensuel, a.idLoueur, u.prenom AS loueur_prenom, u.nom AS loueur_nom
         FROM candidatures c
         JOIN annonces a ON c.idAnnonce = a.id
         JOIN utilisateurs u ON a.idLoueur = u.id
         WHERE c.idEtudiant = ?
         ORDER BY c.dateEnvoi DESC"
    );
    $stmt->execute([$etudiantId]);
    $candidatures = $stmt->fetchAll();
} catch (PDOException $e) {
    $candidatures = [];
    $error_db = 'Erreur lors de la récupération des candidatures.';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes candidatures - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/candidatures.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <main class="container">
        <h1>Mes candidatures</h1>

        <?php if (isset($error_db)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_db); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php if (empty($candidatures)): ?>
            <p>Vous n'avez aucune candidature pour le moment.</p>
        <?php else: ?>
            <div class="candidatures-list">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Annonce</th>
                            <th>Ville</th>
                            <th>Prix</th>
                            <th>Loueur</th>
                            <th>Message</th>
                            <th>Statut</th>
                            <th>Envoyée</th>
                            <th>Réponse</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidatures as $c): ?>
                            <tr>
                                <td><a href="annonce.php?id=<?php echo (int)$c['idAnnonce']; ?>"><?php echo htmlspecialchars($c['titre']); ?></a></td>
                                <td><?php echo htmlspecialchars($c['ville']); ?></td>
                                <td><?php echo number_format($c['prixMensuel'], 2, ',', ' '); ?> €</td>
                                <td><?php echo htmlspecialchars(trim($c['loueur_prenom'] . ' ' . $c['loueur_nom'])); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($c['message'])); ?></td>
                                <td><?php echo htmlspecialchars($c['statut']); ?></td>
                                <td><?php echo htmlspecialchars($c['dateEnvoi']); ?></td>
                                <td><?php echo $c['dateReponse'] ? htmlspecialchars($c['dateReponse']) : '-'; ?></td>
                                <td>
                                    <?php if ($c['statut'] === 'en_attente'): ?>
                                        <form method="post" action="api/candidature-action.php" class="candidature-form" data-candidature-id="<?php echo (int)$c['id']; ?>" data-annonce-title="<?php echo htmlspecialchars($c['titre']); ?>">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                            <input type="hidden" name="redirect" value="candidatures.php">
                                            <button type="submit" class="btn btn-warning">Annuler</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
    
    <!-- Script JavaScript pour la gestion AJAX des candidatures -->
    <script src="js/candidatures.js"></script>
</body>
</html>
