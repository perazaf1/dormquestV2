<?php
// dashboard-etudiant.php - Tableau de bord étudiant
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Exiger la connexion
require_login();

$isLoggedIn = is_logged_in();
$userType = get_user_role();
$user_id = get_user_id();

// Récupérer les candidatures de l'étudiant
try {
    $stmt = $pdo->prepare(
        "SELECT c.*, a.titre, a.ville, a.prixMensuel, u.prenom AS loueur_prenom, u.nom AS loueur_nom
         FROM candidatures c
         JOIN annonces a ON c.idAnnonce = a.id
         JOIN utilisateurs u ON a.idLoueur = u.id
         WHERE c.idEtudiant = ?
         ORDER BY c.dateEnvoi DESC"
    );
    $stmt->execute([$user_id]);
    $candidatures = $stmt->fetchAll();

    // Favoris récents
    $stmt2 = $pdo->prepare(
        "SELECT f.*, a.titre, a.ville, a.prixMensuel
         FROM favoris f
         JOIN annonces a ON f.idAnnonce = a.id
         WHERE f.idEtudiant = ?
         ORDER BY f.dateAjout DESC LIMIT 8"
    );
    $stmt2->execute([$user_id]);
    $favoris = $stmt2->fetchAll();
} catch (PDOException $e) {
    $candidatures = [];
    $favoris = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon tableau de bord - DormQuest</title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="dashboard-page">
        <div class="dashboard-container">
            <header class="dashboard-header">
                <h1>Bienvenue, <?php echo htmlspecialchars(get_user_prenom()); ?></h1>
                <p>Voici un aperçu de vos activités récentes.</p>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success" style="margin-bottom:12px;"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-error" style="margin-bottom:12px;"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <section class="dashboard-main">
                    <h2>Mes candidatures</h2>
                    <?php if (empty($candidatures)): ?>
                        <p>Vous n'avez encore envoyé aucune candidature.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Annonce</th>
                                    <th>Loueur</th>
                                    <th>Ville</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Envoyée le</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidatures as $c): ?>
                                    <tr>
                                        <td><a href="annonce.php?id=<?php echo (int)$c['idAnnonce']; ?>"><?php echo htmlspecialchars($c['titre']); ?></a></td>
                                        <td><?php echo htmlspecialchars($c['loueur_prenom'] . ' ' . $c['loueur_nom']); ?></td>
                                        <td><?php echo htmlspecialchars($c['ville']); ?></td>
                                        <td><?php echo number_format($c['prixMensuel'], 2, ',', ' ') . ' €'; ?></td>
                                        <td>
                                            <?php
                                                $st = $c['statut'];
                                                if ($st === 'acceptee') echo '<span class="badge badge--success">Acceptée</span>';
                                                elseif ($st === 'refusee') echo '<span class="badge badge--danger">Refusée</span>';
                                                elseif ($st === 'annulee') echo '<span class="badge badge--muted">Annulée</span>';
                                                else echo '<span class="badge badge--pending">En attente</span>';
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['dateEnvoi']); ?></td>
                                        <td>
                                            <?php if ($c['statut'] !== 'annulee' && $c['statut'] !== 'acceptee' && $c['statut'] !== 'refusee'): ?>
                                                <form method="POST" action="api/candidature-action.php" class="inline-form" onsubmit="return confirmCancel()">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="redirect" value="../dashboard-etudiant.php">
                                                    <button type="submit" class="btn-cancel">Annuler</button>
                                                </form>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>

                <aside class="dashboard-aside">
                    <div class="card">
                        <h3>Favoris récents</h3>
                        <?php if (empty($favoris)): ?>
                            <p>Aucun favori pour le moment.</p>
                        <?php else: ?>
                            <ul class="fav-list">
                                <?php foreach ($favoris as $f): ?>
                                    <li>
                                        <a href="annonce.php?id=<?php echo (int)$f['idAnnonce']; ?>"><?php echo htmlspecialchars($f['titre']); ?></a>
                                        <div class="muted"><?php echo htmlspecialchars($f['ville']); ?> — <?php echo number_format($f['prixMensuel'],0,',',' '); ?>€</div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/dashboard.js"></script>
</body>
</html>
