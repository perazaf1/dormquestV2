<?php
// dashboard-loueur.php - Tableau de bord loueur
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Exiger la connexion et le rôle loueur
require_login();
if (!is_loueur()) {
    header('Location: index.php');
    exit();
}

$isLoggedIn = is_logged_in();
$userType = get_user_role();
$user_id = get_user_id();

// Récupérer les annonces du loueur
try {
    $stmt = $pdo->prepare("SELECT * FROM annonces WHERE idLoueur = ? ORDER BY dateCreation DESC");
    $stmt->execute([$user_id]);
    $annonces = $stmt->fetchAll();

    // Récupérer candidatures récentes reçues pour les annonces du loueur
    $stmt2 = $pdo->prepare(
        "SELECT c.*, u.prenom AS etu_prenom, u.nom AS etu_nom, a.titre AS annonce_titre, a.id AS annonce_id
         FROM candidatures c
         JOIN utilisateurs u ON c.idEtudiant = u.id
         JOIN annonces a ON c.idAnnonce = a.id
         WHERE a.idLoueur = ?
         ORDER BY c.dateEnvoi DESC
         LIMIT 20"
    );
    $stmt2->execute([$user_id]);
    $candidatures_recues = $stmt2->fetchAll();
    
    // Récupérer activités récentes : favoris ajoutés pour les annonces du loueur
    $stmt3 = $pdo->prepare(
        "SELECT f.*, a.titre AS annonce_titre, u.prenom AS etu_prenom, u.nom AS etu_nom
         FROM favoris f
         JOIN annonces a ON f.idAnnonce = a.id
         JOIN utilisateurs u ON f.idEtudiant = u.id
         WHERE a.idLoueur = ?
         ORDER BY f.dateAjout DESC
         LIMIT 10"
    );
    $stmt3->execute([$user_id]);
    $favoris_activity = $stmt3->fetchAll();
} catch (PDOException $e) {
    $annonces = [];
    $candidatures_recues = [];
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
                <p>Gérez vos annonces et candidatures reçues.</p>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success" style="margin-bottom:12px;"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-error" style="margin-bottom:12px;"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <section class="dashboard-main">
                    <h2>Vos logements ajoutés récemment</h2>
                    <p class="muted">Les dernières annonces que vous avez publiées</p>
                    <?php if (empty($annonces)): ?>
                        <p>Vous n'avez pas encore publié d'annonce.</p>
                    <?php else: ?>
                        <div class="annonces-list">
                            <?php foreach ($annonces as $a): ?>
                                <article class="annonce-card">
                                    <h3><a href="annonce.php?id=<?php echo (int)$a['id']; ?>"><?php echo htmlspecialchars($a['titre']); ?></a></h3>
                                    <div class="muted"><?php echo htmlspecialchars($a['ville']); ?> — <?php echo number_format($a['prixMensuel'],0,',',' '); ?> €</div>
                                    <div class="muted" style="font-size:0.85rem;margin-top:6px;">Publiée le <?php echo htmlspecialchars($a['dateCreation']); ?></div>
                                    <p class="annonce-actions">
                                        <a href="edit-annonce.php?id=<?php echo (int)$a['id']; ?>">Modifier</a> |
                                        <a href="annonce.php?id=<?php echo (int)$a['id']; ?>">Voir</a>
                                    </p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h2 style="margin-top:2rem;">Candidatures récentes</h2>
                    <p class="muted">Les dernières candidatures envoyées par des étudiants</p>
                    <?php if (empty($candidatures_recues)): ?>
                        <p>Aucune candidature reçue pour le moment.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Annonce</th>
                                    <th>Message</th>
                                    <th>Statut</th>
                                    <th>Envoyée le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidatures_recues as $c): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($c['etu_prenom'] . ' ' . $c['etu_nom']); ?></td>
                                        <td><a href="annonce.php?id=<?php echo (int)$c['annonce_id']; ?>"><?php echo htmlspecialchars($c['annonce_titre']); ?></a></td>
                                        <td>
                                            <?php $preview = htmlspecialchars(substr($c['message'] ?? '', 0, 80)); ?>
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <span class="muted"><?php echo $preview; ?></span>
                                                <?php if (!empty($c['message'])): ?>
                                                    <button type="button" class="btn-link view-message" data-student="<?php echo htmlspecialchars($c['etu_prenom'] . ' ' . $c['etu_nom'], ENT_QUOTES); ?>" data-annonce="<?php echo htmlspecialchars($c['annonce_titre'], ENT_QUOTES); ?>" data-message="<?php echo htmlspecialchars($c['message'], ENT_QUOTES); ?>">Voir</button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['statut']); ?></td>
                                        <td><?php echo htmlspecialchars($c['dateEnvoi']); ?></td>
                                        <td>
                                            <?php if ($c['statut'] === 'en_attente'): ?>
                                                <form method="POST" action="api/candidature-action.php" style="display:inline-block;margin-right:6px;" onsubmit="return confirmAction(this)">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                                    <input type="hidden" name="action" value="accept">
                                                    <input type="hidden" name="redirect" value="../dashboard-loueur.php">
                                                    <button type="submit" class="btn-accept">Accepter</button>
                                                </form>
                                                <form method="POST" action="api/candidature-action.php" style="display:inline-block;" onsubmit="return confirmAction(this)">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                                    <input type="hidden" name="action" value="refuse">
                                                    <input type="hidden" name="redirect" value="../dashboard-loueur.php">
                                                    <button type="submit" class="btn-refuse">Refuser</button>
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
                        <h3>Activités récentes</h3>
                        <?php if (empty($favoris_activity)): ?>
                            <p>Aucune activité récente.</p>
                        <?php else: ?>
                            <ul class="fav-list">
                                <?php foreach ($favoris_activity as $fa): ?>
                                    <li>
                                        <div><strong><?php echo htmlspecialchars($fa['etu_prenom'] . ' ' . $fa['etu_nom']); ?></strong>
                                            <div class="muted">a ajouté aux favoris : <a href="annonce.php?id=<?php echo (int)$fa['idAnnonce']; ?>"><?php echo htmlspecialchars($fa['annonce_titre']); ?></a></div>
                                            <div class="muted" style="font-size:0.85rem;margin-top:6px;">Le <?php echo htmlspecialchars($fa['dateAjout']); ?></div>
                                        </div>
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

    <!-- Message modal -->
    <div id="messageModal" class="modal" aria-hidden="true">
        <div class="modal__content">
            <button class="modal__close" aria-label="Fermer">✕</button>
            <h3 class="modal__title">Message</h3>
            <div class="modal__body"></div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>
