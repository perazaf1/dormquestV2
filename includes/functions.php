<?php
// ============================================================================
// FONCTIONS DE BASE DE DONNÉES RÉUTILISABLES
// Fichier : includes/functions.php
//
// Ce fichier centralise toutes les requêtes SQL fréquemment utilisées
// pour éviter la duplication de code et faciliter la maintenance.
// ============================================================================

// ============================================================================
// SECTION 1 : FONCTIONS UTILISATEURS
// ============================================================================

/**
 * Récupère un utilisateur par son ID
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $id ID de l'utilisateur
 * @return array|false Données de l'utilisateur ou false si non trouvé
 */
function get_user_by_id($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Récupère un utilisateur par son email
 *
 * @param PDO $pdo Connexion à la base de données
 * @param string $email Email de l'utilisateur
 * @return array|false Données de l'utilisateur ou false si non trouvé
 */
function get_user_by_email($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * Vérifie si un email existe déjà dans la base de données
 *
 * @param PDO $pdo Connexion à la base de données
 * @param string $email Email à vérifier
 * @param int|null $excludeUserId ID utilisateur à exclure (pour mise à jour profil)
 * @return bool true si l'email existe, false sinon
 */
function email_exists($pdo, $email, $excludeUserId = null) {
    if ($excludeUserId) {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt->execute([$email, $excludeUserId]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
    }
    return $stmt->fetch() !== false;
}

/**
 * Récupère la photo de profil d'un utilisateur depuis la BDD
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $userId ID de l'utilisateur
 * @return string|null Chemin de la photo ou null
 */
function get_user_photo_by_id($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT photoDeProfil FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result ? $result['photoDeProfil'] : null;
}

/**
 * Met à jour la photo de profil d'un utilisateur
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $userId ID de l'utilisateur
 * @param string $photoPath Chemin de la nouvelle photo
 * @return bool true si succès, false sinon
 */
function update_user_photo($pdo, $userId, $photoPath) {
    $stmt = $pdo->prepare("UPDATE utilisateurs SET photoDeProfil = ? WHERE id = ?");
    return $stmt->execute([$photoPath, $userId]);
}

/**
 * Supprime la photo de profil d'un utilisateur (met à NULL)
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $userId ID de l'utilisateur
 * @return bool true si succès, false sinon
 */
function delete_user_photo($pdo, $userId) {
    $stmt = $pdo->prepare("UPDATE utilisateurs SET photoDeProfil = NULL WHERE id = ?");
    return $stmt->execute([$userId]);
}

// ============================================================================
// SECTION 2 : FONCTIONS ANNONCES
// ============================================================================

/**
 * Récupère toutes les annonces actives avec les informations du loueur
 *
 * @param PDO $pdo Connexion à la base de données
 * @return array Liste des annonces actives
 */
function get_all_annonces_actives($pdo) {
    $stmt = $pdo->prepare("
        SELECT a.*, u.prenom, u.nom, u.photoDeProfil, u.typeLoueur
        FROM annonces a
        JOIN utilisateurs u ON a.idLoueur = u.id
        WHERE a.statut = 'active'
        ORDER BY a.dateCreation DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Récupère une annonce par son ID avec les informations du loueur
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $id ID de l'annonce
 * @return array|false Données de l'annonce ou false si non trouvée
 */
function get_annonce_by_id($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT a.*, u.prenom, u.nom, u.email, u.telephone, u.photoDeProfil, u.typeLoueur
        FROM annonces a
        JOIN utilisateurs u ON a.idLoueur = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Récupère toutes les annonces d'un loueur spécifique
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $loueurId ID du loueur
 * @param string|null $statut Filtrer par statut ('active', 'archivee') ou null pour tous
 * @return array Liste des annonces du loueur
 */
function get_annonces_by_loueur($pdo, $loueurId, $statut = null) {
    if ($statut) {
        $stmt = $pdo->prepare("
            SELECT * FROM annonces
            WHERE idLoueur = ? AND statut = ?
            ORDER BY dateCreation DESC
        ");
        $stmt->execute([$loueurId, $statut]);
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM annonces
            WHERE idLoueur = ?
            ORDER BY dateCreation DESC
        ");
        $stmt->execute([$loueurId]);
    }
    return $stmt->fetchAll();
}

/**
 * Compte le nombre d'annonces d'un loueur
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $loueurId ID du loueur
 * @param string|null $statut Filtrer par statut ou null pour tous
 * @return int Nombre d'annonces
 */
function count_annonces_by_loueur($pdo, $loueurId, $statut = null) {
    if ($statut) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM annonces WHERE idLoueur = ? AND statut = ?");
        $stmt->execute([$loueurId, $statut]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM annonces WHERE idLoueur = ?");
        $stmt->execute([$loueurId]);
    }
    return (int) $stmt->fetchColumn();
}

/**
 * Recherche des annonces avec filtres
 *
 * @param PDO $pdo Connexion à la base de données
 * @param array $filters Tableau de filtres (ville, typeLogement, prixMin, prixMax, etc.)
 * @return array Liste des annonces correspondantes
 */
function search_annonces($pdo, $filters = []) {
    $sql = "
        SELECT a.*, u.prenom, u.nom, u.photoDeProfil, u.typeLoueur
        FROM annonces a
        JOIN utilisateurs u ON a.idLoueur = u.id
        WHERE a.statut = 'active'
    ";

    $params = [];

    // Filtre par ville
    if (!empty($filters['ville'])) {
        $sql .= " AND a.ville LIKE ?";
        $params[] = '%' . $filters['ville'] . '%';
    }

    // Filtre par type de logement
    if (!empty($filters['typeLogement'])) {
        $sql .= " AND a.typeLogement = ?";
        $params[] = $filters['typeLogement'];
    }

    // Filtre par prix minimum
    if (!empty($filters['prixMin'])) {
        $sql .= " AND a.prixMensuel >= ?";
        $params[] = $filters['prixMin'];
    }

    // Filtre par prix maximum
    if (!empty($filters['prixMax'])) {
        $sql .= " AND a.prixMensuel <= ?";
        $params[] = $filters['prixMax'];
    }

    $sql .= " ORDER BY a.dateCreation DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Change le statut d'une annonce
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $annonceId ID de l'annonce
 * @param string $statut Nouveau statut ('active' ou 'archivee')
 * @return bool true si succès, false sinon
 */
function update_annonce_statut($pdo, $annonceId, $statut) {
    $stmt = $pdo->prepare("UPDATE annonces SET statut = ? WHERE id = ?");
    return $stmt->execute([$statut, $annonceId]);
}

/**
 * Supprime une annonce
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $annonceId ID de l'annonce
 * @return bool true si succès, false sinon
 */
function delete_annonce($pdo, $annonceId) {
    $stmt = $pdo->prepare("DELETE FROM annonces WHERE id = ?");
    return $stmt->execute([$annonceId]);
}

/**
 * Met à jour les informations d'une annonce
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $annonceId ID de l'annonce
 * @param array $data Données à mettre à jour
 * @return bool true si succès, false sinon
 */
function update_annonce($pdo, $annonceId, $data) {
    $stmt = $pdo->prepare("
        UPDATE annonces SET
            titre = :titre,
            description = :description,
            adresse = :adresse,
            ville = :ville,
            typeLogement = :typeLogement,
            prixMensuel = :prixMensuel,
            superficie = :superficie
        WHERE id = :id
    ");

    return $stmt->execute([
        ':titre' => $data['titre'],
        ':description' => $data['description'],
        ':adresse' => $data['adresse'],
        ':ville' => $data['ville'],
        ':typeLogement' => $data['typeLogement'],
        ':prixMensuel' => $data['prixMensuel'],
        ':superficie' => $data['superficie'],
        ':id' => $annonceId
    ]);
}

// ============================================================================
// SECTION 3 : FONCTIONS PHOTOS ANNONCES
// ============================================================================

/**
 * Récupère toutes les photos d'une annonce
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $annonceId ID de l'annonce
 * @return array Liste des chemins de photos
 */
function get_photos_annonce($pdo, $annonceId) {
    $stmt = $pdo->prepare("
        SELECT id, cheminPhoto FROM photos_annonces
        WHERE idAnnonce = ?
        ORDER BY id
    ");
    $stmt->execute([$annonceId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère toutes les photos d'une annonce avec leurs IDs
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $annonceId ID de l'annonce
 * @return array Liste des photos avec id et cheminPhoto
 */
function get_photos_annonce_with_ids($pdo, $annonceId) {
    $stmt = $pdo->prepare("
        SELECT id, cheminPhoto FROM photos_annonces
        WHERE idAnnonce = ?
        ORDER BY id
    ");
    $stmt->execute([$annonceId]);
    return $stmt->fetchAll();
}

/**
 * Compte le nombre de photos d'une annonce
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $annonceId ID de l'annonce
 * @return int Nombre de photos
 */
function count_photos_annonce($pdo, $annonceId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM photos_annonces WHERE idAnnonce = ?");
    $stmt->execute([$annonceId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Ajoute une photo à une annonce
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $annonceId ID de l'annonce
 * @param string $cheminPhoto Chemin de la photo
 * @return bool true si succès, false sinon
 */
function add_photo_annonce($pdo, $annonceId, $cheminPhoto) {
    $stmt = $pdo->prepare("INSERT INTO photos_annonces (idAnnonce, cheminPhoto) VALUES (?, ?)");
    return $stmt->execute([$annonceId, $cheminPhoto]);
}

/**
 * Supprime une photo spécifique
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $photoId ID de la photo
 * @return string|false Chemin de la photo supprimée ou false
 */
function delete_photo_annonce($pdo, $photoId) {
    // Récupérer le chemin avant de supprimer
    $stmt = $pdo->prepare("SELECT cheminPhoto FROM photos_annonces WHERE id = ?");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();

    if ($photo) {
        $stmt = $pdo->prepare("DELETE FROM photos_annonces WHERE id = ?");
        if ($stmt->execute([$photoId])) {
            return $photo['cheminPhoto'];
        }
    }
    return false;
}

// ============================================================================
// SECTION 4 : FONCTIONS CRITÈRES LOGEMENT
// ============================================================================

/**
 * Récupère les critères d'une annonce
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $annonceId ID de l'annonce
 * @return array|false Critères du logement ou false
 */
function get_criteres_annonce($pdo, $annonceId) {
    $stmt = $pdo->prepare("SELECT * FROM criteres_logement WHERE idAnnonce = ?");
    $stmt->execute([$annonceId]);
    return $stmt->fetch();
}

/**
 * Met à jour les critères d'une annonce
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $annonceId ID de l'annonce
 * @param array $criteres Critères à mettre à jour
 * @return bool true si succès, false sinon
 */
function update_criteres_annonce($pdo, $annonceId, $criteres) {
    $stmt = $pdo->prepare("
        UPDATE criteres_logement SET
            accesPMR = :accesPMR,
            meuble = :meuble,
            eligibleAPL = :eligibleAPL,
            parkingDisponible = :parkingDisponible,
            animauxAcceptes = :animauxAcceptes
        WHERE idAnnonce = :idAnnonce
    ");

    return $stmt->execute([
        ':accesPMR' => $criteres['accesPMR'],
        ':meuble' => $criteres['meuble'],
        ':eligibleAPL' => $criteres['eligibleAPL'],
        ':parkingDisponible' => $criteres['parkingDisponible'],
        ':animauxAcceptes' => $criteres['animauxAcceptes'],
        ':idAnnonce' => $annonceId
    ]);
}

// ============================================================================
// SECTION 5 : FONCTIONS FAVORIS
// ============================================================================

/**
 * Vérifie si un étudiant a mis une annonce en favori
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $etudiantId ID de l'étudiant
 * @param int $annonceId ID de l'annonce
 * @return bool true si en favori, false sinon
 */
function is_favori($pdo, $etudiantId, $annonceId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM favoris
        WHERE idEtudiant = ? AND idAnnonce = ?
    ");
    $stmt->execute([$etudiantId, $annonceId]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Récupère tous les favoris d'un étudiant avec détails des annonces
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $etudiantId ID de l'étudiant
 * @return array Liste des annonces favorites
 */
function get_favoris_by_etudiant($pdo, $etudiantId) {
    $stmt = $pdo->prepare("
        SELECT a.*, u.prenom, u.nom, u.photoDeProfil, u.typeLoueur, f.dateAjout
        FROM favoris f
        JOIN annonces a ON f.idAnnonce = a.id
        JOIN utilisateurs u ON a.idLoueur = u.id
        WHERE f.idEtudiant = ?
        ORDER BY f.dateAjout DESC
    ");
    $stmt->execute([$etudiantId]);
    return $stmt->fetchAll();
}

/**
 * Compte le nombre de favoris d'un étudiant
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $etudiantId ID de l'étudiant
 * @return int Nombre de favoris
 */
function count_favoris_by_etudiant($pdo, $etudiantId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE idEtudiant = ?");
    $stmt->execute([$etudiantId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Ajoute une annonce aux favoris
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $etudiantId ID de l'étudiant
 * @param int $annonceId ID de l'annonce
 * @return bool true si succès, false sinon
 */
function add_favori($pdo, $etudiantId, $annonceId) {
    $stmt = $pdo->prepare("INSERT INTO favoris (idEtudiant, idAnnonce) VALUES (?, ?)");
    return $stmt->execute([$etudiantId, $annonceId]);
}

/**
 * Retire une annonce des favoris
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $etudiantId ID de l'étudiant
 * @param int $annonceId ID de l'annonce
 * @return bool true si succès, false sinon
 */
function remove_favori($pdo, $etudiantId, $annonceId) {
    $stmt = $pdo->prepare("DELETE FROM favoris WHERE idEtudiant = ? AND idAnnonce = ?");
    return $stmt->execute([$etudiantId, $annonceId]);
}

// ============================================================================
// SECTION 6 : FONCTIONS CANDIDATURES
// ============================================================================

/**
 * Récupère toutes les candidatures d'un étudiant
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $etudiantId ID de l'étudiant
 * @return array Liste des candidatures
 */
function get_candidatures_by_etudiant($pdo, $etudiantId) {
    $stmt = $pdo->prepare("
        SELECT c.*, a.titre, a.ville, a.prixMensuel, a.typeLogement,
               u.prenom as loueur_prenom, u.nom as loueur_nom
        FROM candidatures c
        JOIN annonces a ON c.idAnnonce = a.id
        JOIN utilisateurs u ON a.idLoueur = u.id
        WHERE c.idEtudiant = ?
        ORDER BY c.dateEnvoi DESC
    ");
    $stmt->execute([$etudiantId]);
    return $stmt->fetchAll();
}

/**
 * Récupère toutes les candidatures pour les annonces d'un loueur
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $loueurId ID du loueur
 * @return array Liste des candidatures
 */
function get_candidatures_by_loueur($pdo, $loueurId) {
    $stmt = $pdo->prepare("
        SELECT c.*, a.titre, a.ville,
               e.prenom as etudiant_prenom, e.nom as etudiant_nom,
               e.email as etudiant_email, e.telephone as etudiant_telephone
        FROM candidatures c
        JOIN annonces a ON c.idAnnonce = a.id
        JOIN utilisateurs e ON c.idEtudiant = e.id
        WHERE a.idLoueur = ?
        ORDER BY c.dateEnvoi DESC
    ");
    $stmt->execute([$loueurId]);
    return $stmt->fetchAll();
}

/**
 * Compte les candidatures d'un étudiant par statut
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $etudiantId ID de l'étudiant
 * @param string|null $statut Statut spécifique ou null pour tous
 * @return int Nombre de candidatures
 */
function count_candidatures_by_etudiant($pdo, $etudiantId, $statut = null) {
    if ($statut) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidatures WHERE idEtudiant = ? AND statut = ?");
        $stmt->execute([$etudiantId, $statut]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidatures WHERE idEtudiant = ?");
        $stmt->execute([$etudiantId]);
    }
    return (int) $stmt->fetchColumn();
}

/**
 * Vérifie si un étudiant a déjà candidaté pour une annonce
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $etudiantId ID de l'étudiant
 * @param int $annonceId ID de l'annonce
 * @return bool true si candidature existe, false sinon
 */
function has_candidature($pdo, $etudiantId, $annonceId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM candidatures
        WHERE idEtudiant = ? AND idAnnonce = ?
    ");
    $stmt->execute([$etudiantId, $annonceId]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Change le statut d'une candidature
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $candidatureId ID de la candidature
 * @param string $statut Nouveau statut
 * @return bool true si succès, false sinon
 */
function update_candidature_statut($pdo, $candidatureId, $statut) {
    $stmt = $pdo->prepare("UPDATE candidatures SET statut = ? WHERE id = ?");
    return $stmt->execute([$statut, $candidatureId]);
}

/**
 * Crée une nouvelle candidature
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $etudiantId ID de l'étudiant
 * @param int $annonceId ID de l'annonce
 * @return int|false ID de la candidature créée ou false en cas d'échec
 */
function create_candidature($pdo, $etudiantId, $annonceId) {
    $stmt = $pdo->prepare("
        INSERT INTO candidatures (idEtudiant, idAnnonce, statut, dateEnvoi)
        VALUES (?, ?, 'en_attente', NOW())
    ");

    if ($stmt->execute([$etudiantId, $annonceId])) {
        return $pdo->lastInsertId();
    }

    return false;
}

// ============================================================================
// SECTION 7 : FONCTIONS NOTIFICATIONS
// ============================================================================

/**
 * S'assure que la table notifications existe dans la base de données
 * Crée la table si elle n'existe pas déjà
 *
 * @param PDO $pdo Connexion à la base de données
 * @return void
 */
function ensure_notifications_table_exists($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            idUtilisateur INT NOT NULL,
            titre VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type ENUM('contact','candidature','favori','annonce','autre') DEFAULT 'autre',
            idAnnonce INT NULL,
            idCandidature INT NULL,
            donneesJson LONGTEXT NULL,
            dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
            lue BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (idUtilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (idAnnonce) REFERENCES annonces(id) ON DELETE CASCADE,
            INDEX idx_utilisateur (idUtilisateur),
            INDEX idx_lue (lue),
            INDEX idx_type (type)
        )
    ");
}

/**
 * Crée une notification pour un utilisateur
 *
 * @param PDO $pdo Connexion à la base de données
 * @param int $idUtilisateur ID de l'utilisateur qui recevra la notification
 * @param string $titre Titre de la notification
 * @param string $message Message de la notification
 * @param string $type Type de notification ('contact', 'candidature', 'favori', 'annonce', 'autre')
 * @param int|null $idAnnonce ID de l'annonce concernée (optionnel)
 * @param int|null $idCandidature ID de la candidature concernée (optionnel)
 * @param array|null $donneesJson Données supplémentaires au format tableau (optionnel)
 * @return int|false ID de la notification créée ou false en cas d'échec
 */
function create_notification($pdo, $idUtilisateur, $titre, $message, $type = 'autre', $idAnnonce = null, $idCandidature = null, $donneesJson = null) {
    // S'assurer que la table existe
    ensure_notifications_table_exists($pdo);

    // Encoder les données JSON si un tableau est fourni
    $jsonString = $donneesJson ? json_encode($donneesJson) : null;

    // Préparer la requête d'insertion
    $stmt = $pdo->prepare("
        INSERT INTO notifications (idUtilisateur, titre, message, type, idAnnonce, idCandidature, donneesJson)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    // Exécuter la requête
    if ($stmt->execute([$idUtilisateur, $titre, $message, $type, $idAnnonce, $idCandidature, $jsonString])) {
        return $pdo->lastInsertId();
    }

    return false;
}

// ============================================================================
// RÉSUMÉ DE CE FICHIER :
// ============================================================================
// Ce fichier functions.php centralise toutes les requêtes SQL du projet.
//
// AVANTAGES :
// ✅ Pas de duplication de code (DRY - Don't Repeat Yourself)
// ✅ Maintenance facile : modifier une requête = modifier 1 seule fonction
// ✅ Réutilisable partout : pages, APIs, scripts
// ✅ Code plus lisible : get_user_by_id($pdo, 5) vs 3 lignes de SQL
// ✅ Facilite le travail en équipe : fonctions claires et documentées
//
// UTILISATION :
// require_once 'includes/functions.php';
// $user = get_user_by_id($pdo, 123);
// $annonces = get_all_annonces_actives($pdo);
// ============================================================================
?>
