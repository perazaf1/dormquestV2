-- ==========================================
-- DormQuest - Améliorations de la base de données
-- À exécuter APRÈS avoir créé les tables existantes
-- ==========================================

USE dormquest;

-- ==========================================
-- 1. AJOUT D'INDEX SUPPLÉMENTAIRES
-- ==========================================

-- Index pour améliorer les recherches par rôle
ALTER TABLE utilisateurs ADD INDEX idx_role (role);

-- Index pour améliorer les recherches par email (si pas déjà présent)
-- L'email est UNIQUE donc déjà indexé, mais on peut ajouter un index composite
ALTER TABLE utilisateurs ADD INDEX idx_email_role (email, role);

-- Index pour les recherches de logements par ville et budget
ALTER TABLE annonces ADD INDEX idx_ville_prix (ville, prixMensuel);

-- Index pour les annonces actives
ALTER TABLE annonces ADD INDEX idx_statut (statut);

-- ==========================================
-- 2. AJOUT DE CONTRAINTES DE VÉRIFICATION
-- ==========================================

-- S'assurer que le budget est positif pour les étudiants
ALTER TABLE utilisateurs 
ADD CONSTRAINT chk_budget_positif 
CHECK (role != 'etudiant' OR budget > 0);

-- S'assurer que le prix mensuel est positif
ALTER TABLE annonces 
ADD CONSTRAINT chk_prix_positif 
CHECK (prixMensuel > 0);

-- S'assurer que la superficie est positive
ALTER TABLE annonces 
ADD CONSTRAINT chk_superficie_positive 
CHECK (superficie IS NULL OR superficie > 0);

-- ==========================================
-- 3. AJOUT DE TRIGGERS UTILES
-- ==========================================

-- Trigger : Mettre à jour automatiquement dateModification des annonces
DELIMITER //
CREATE TRIGGER before_annonce_update
BEFORE UPDATE ON annonces
FOR EACH ROW
BEGIN
    SET NEW.dateModification = NOW();
END//
DELIMITER ;

-- Trigger : Mettre à jour derniereConnexion lors de la connexion
-- (sera utilisé dans login.php)
DELIMITER //
CREATE TRIGGER after_user_login
BEFORE UPDATE ON utilisateurs
FOR EACH ROW
BEGIN
    IF NEW.derniereConnexion > OLD.derniereConnexion THEN
        SET NEW.derniereConnexion = NOW();
    END IF;
END//
DELIMITER ;

-- ==========================================
-- 4. CRÉATION DE VUES UTILES
-- ==========================================

-- Vue : Annonces complètes avec informations du loueur
CREATE OR REPLACE VIEW vue_annonces_completes AS
SELECT 
    a.*,
    u.prenom AS loueur_prenom,
    u.nom AS loueur_nom,
    u.email AS loueur_email,
    u.telephone AS loueur_telephone,
    u.typeLoueur AS loueur_type,
    c.accesPMR,
    c.eligibleAPL,
    c.statutBoursier,
    c.animauxAcceptes,
    c.parkingDisponible,
    c.meuble
FROM annonces a
JOIN utilisateurs u ON a.idLoueur = u.id
LEFT JOIN criteres_logement c ON c.idAnnonce = a.id
WHERE a.statut = 'active';

-- Vue : Candidatures avec détails complets
CREATE OR REPLACE VIEW vue_candidatures_completes AS
SELECT 
    c.*,
    u_etudiant.prenom AS etudiant_prenom,
    u_etudiant.nom AS etudiant_nom,
    u_etudiant.email AS etudiant_email,
    u_etudiant.telephone AS etudiant_telephone,
    a.titre AS annonce_titre,
    a.ville AS annonce_ville,
    a.prixMensuel AS annonce_prix,
    u_loueur.prenom AS loueur_prenom,
    u_loueur.nom AS loueur_nom,
    u_loueur.email AS loueur_email
FROM candidatures c
JOIN utilisateurs u_etudiant ON c.idEtudiant = u_etudiant.id
JOIN annonces a ON c.idAnnonce = a.id
JOIN utilisateurs u_loueur ON a.idLoueur = u_loueur.id;

-- Vue : Favoris avec détails des annonces
CREATE OR REPLACE VIEW vue_favoris_complets AS
SELECT 
    f.*,
    a.titre,
    a.ville,
    a.prixMensuel,
    a.typeLogement,
    a.superficie,
    a.dateDisponibilite,
    u.prenom AS loueur_prenom,
    u.nom AS loueur_nom
FROM favoris f
JOIN annonces a ON f.idAnnonce = a.id
JOIN utilisateurs u ON a.idLoueur = u.id
WHERE a.statut = 'active';

-- Vue : Statistiques des loueurs
CREATE OR REPLACE VIEW vue_stats_loueurs AS
SELECT 
    u.id,
    u.prenom,
    u.nom,
    u.typeLoueur,
    COUNT(DISTINCT a.id) AS nb_annonces,
    COUNT(DISTINCT CASE WHEN a.statut = 'active' THEN a.id END) AS nb_annonces_actives,
    COUNT(DISTINCT c.id) AS nb_candidatures,
    COUNT(DISTINCT CASE WHEN c.statut = 'acceptee' THEN c.id END) AS nb_candidatures_acceptees
FROM utilisateurs u
LEFT JOIN annonces a ON a.idLoueur = u.id
LEFT JOIN candidatures c ON c.idAnnonce = a.id
WHERE u.role = 'loueur'
GROUP BY u.id, u.prenom, u.nom, u.typeLoueur;

-- ==========================================
-- 5. PROCÉDURES STOCKÉES UTILES
-- ==========================================

-- Procédure : Rechercher des annonces selon critères
DELIMITER //
CREATE PROCEDURE rechercher_annonces(
    IN p_ville VARCHAR(100),
    IN p_prix_max DECIMAL(10,2),
    IN p_type_logement VARCHAR(50)
)
BEGIN
    SELECT 
        a.*,
        u.prenom AS loueur_prenom,
        u.nom AS loueur_nom,
        u.telephone AS loueur_telephone
    FROM annonces a
    JOIN utilisateurs u ON a.idLoueur = u.id
    WHERE 
        a.statut = 'active'
        AND (p_ville IS NULL OR a.ville LIKE CONCAT('%', p_ville, '%'))
        AND (p_prix_max IS NULL OR a.prixMensuel <= p_prix_max)
        AND (p_type_logement IS NULL OR a.typeLogement = p_type_logement)
    ORDER BY a.dateCreation DESC;
END//
DELIMITER ;

-- Procédure : Obtenir le nombre de candidatures d'un étudiant
DELIMITER //
CREATE PROCEDURE nb_candidatures_etudiant(
    IN p_etudiant_id INT,
    OUT p_total INT,
    OUT p_en_attente INT,
    OUT p_acceptees INT
)
BEGIN
    SELECT 
        COUNT(*),
        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END),
        SUM(CASE WHEN statut = 'acceptee' THEN 1 ELSE 0 END)
    INTO p_total, p_en_attente, p_acceptees
    FROM candidatures
    WHERE idEtudiant = p_etudiant_id;
END//
DELIMITER ;

-- ==========================================
-- 6. DONNÉES DE TEST (Optionnel)
-- ==========================================

-- Insertion d'un étudiant de test
INSERT INTO utilisateurs (prenom, nom, email, motDePasse, role, villeRecherche, budget) 
VALUES ('Jean', 'Dupont', 'jean.dupont@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'etudiant', 'Paris', 800.00);

-- Insertion d'un loueur de test
INSERT INTO utilisateurs (prenom, nom, email, motDePasse, role, telephone, typeLoueur) 
VALUES ('Marie', 'Martin', 'marie.martin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'loueur', '0612345678', 'particulier');

-- Récupérer l'ID du loueur
SET @loueur_id = LAST_INSERT_ID();

-- Insertion d'une annonce de test
INSERT INTO annonces (
    idLoueur, titre, description, adresse, ville, codePostal, 
    typeLogement, prixMensuel, superficie, nombrePieces, 
    dateDisponibilite, contactEmail, contactTelephone
) VALUES (
    @loueur_id,
    'Studio lumineux proche université',
    'Beau studio de 25m² entièrement meublé, proche des transports et commerces. Idéal pour étudiant.',
    '12 rue de la République',
    'Paris',
    '75005',
    'studio',
    650.00,
    25.00,
    1,
    '2024-09-01',
    'marie.martin@test.com',
    '0612345678'
);

-- Récupérer l'ID de l'annonce
SET @annonce_id = LAST_INSERT_ID();

-- Insertion des critères de logement
INSERT INTO criteres_logement (
    idAnnonce, accesPMR, eligibleAPL, animauxAcceptes, 
    parkingDisponible, meuble
) VALUES (
    @annonce_id, FALSE, TRUE, FALSE, TRUE, TRUE
);

-- ==========================================
-- 7. FONCTION UTILE : Calculer la distance entre deux codes postaux
-- (Simplifié - en production, utiliser une vraie API de géolocalisation)
-- ==========================================

DELIMITER //
CREATE FUNCTION code_postal_proche(
    cp1 VARCHAR(10),
    cp2 VARCHAR(10)
) RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE dept1 VARCHAR(2);
    DECLARE dept2 VARCHAR(2);
    
    SET dept1 = LEFT(cp1, 2);
    SET dept2 = LEFT(cp2, 2);
    
    RETURN dept1 = dept2;
END//
DELIMITER ;

-- ==========================================
-- NOTES D'UTILISATION
-- ==========================================

/*
Exemples d'utilisation des vues :

-- Voir toutes les annonces actives avec détails :
SELECT * FROM vue_annonces_completes WHERE ville = 'Paris';

-- Voir les candidatures d'un étudiant :
SELECT * FROM vue_candidatures_completes WHERE idEtudiant = 1;

-- Voir les statistiques d'un loueur :
SELECT * FROM vue_stats_loueurs WHERE id = 2;

Exemples d'utilisation des procédures :

-- Rechercher des annonces :
CALL rechercher_annonces('Paris', 800, 'studio');

-- Obtenir les stats d'un étudiant :
CALL nb_candidatures_etudiant(1, @total, @attente, @acceptees);
SELECT @total, @attente, @acceptees;
*/