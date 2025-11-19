-- ==========================================
-- DormQuest - Données de test pour les annonces
-- À exécuter après avoir créé un compte loueur
-- ==========================================

USE dormquest;

-- IMPORTANT : Remplace X par l'ID de ton compte loueur
-- Tu peux le trouver en faisant : SELECT id FROM utilisateurs WHERE email = 'ton-email@example.com';

-- Variable pour l'ID du loueur (à modifier)
SET @loueur_id = 8; -- CHANGE CETTE VALEUR PAR TON ID LOUEUR

-- ==========================================
-- ANNONCE 1 : Studio à Paris
-- ==========================================
INSERT INTO annonces (
    idLoueur, titre, description, adresse, ville, codePostal,
    typeLogement, prixMensuel, superficie, nombrePieces,
    colocationPossible, empreinteEnergie, dateDisponibilite,
    contactEmail, contactTelephone, statut
) VALUES (
    @loueur_id,
    'Studio lumineux proche Sorbonne',
    'Charmant studio de 25m² situé au cœur du Quartier Latin, à deux pas de la Sorbonne. Entièrement meublé et équipé (lit double, bureau, kitchenette équipée). Immeuble ancien avec ascenseur. Chauffage individuel. Idéal pour étudiant.',
    '12 rue de la Sorbonne',
    'Paris',
    '75005',
    'studio',
    750.00,
    25.00,
    1,
    FALSE,
    'D',
    '2024-09-01',
    'contact@dormquest.fr',
    '0612345678',
    'active'
);
SET @annonce1 = LAST_INSERT_ID();
-- Critères pour l'annonce 1
INSERT INTO criteres_logement (idAnnonce, accesPMR, eligibleAPL, animauxAcceptes, parkingDisponible, meuble)
VALUES (@annonce1, FALSE, TRUE, FALSE, FALSE, TRUE);

-- ==========================================
-- ANNONCE 2 : Colocation à Lyon
-- ==========================================
INSERT INTO annonces (
    idLoueur, titre, description, adresse, ville, codePostal,
    typeLogement, prixMensuel, superficie, nombrePieces,
    colocationPossible, empreinteEnergie, dateDisponibilite,
    contactEmail, contactTelephone, statut
) VALUES (
    @loueur_id,
    'Colocation sympa 3 chambres - Lyon Part-Dieu',
    'Appartement T4 de 85m² en colocation pour 3 étudiants. Proche de toutes commodités et des transports (métro à 5min). Cuisine équipée, salle de bain + WC séparés. Chambre disponible de 15m². Ambiance studieuse et conviviale garantie !',
    '45 rue de la Villette',
    'Lyon',
    '69003',
    'colocation',
    450.00,
    15.00,
    1,
    TRUE,
    'C',
    '2024-09-15',
    'contact@dormquest.fr',
    '0612345678',
    'active'
);
SET @annonce2 = LAST_INSERT_ID();
INSERT INTO criteres_logement (idAnnonce, accesPMR, eligibleAPL, animauxAcceptes, parkingDisponible, meuble)
VALUES (@annonce2, FALSE, TRUE, FALSE, TRUE, TRUE);

-- ==========================================
-- ANNONCE 3 : T2 à Toulouse
-- ==========================================
INSERT INTO annonces (
    idLoueur, titre, description, adresse, ville, codePostal,
    typeLogement, prixMensuel, superficie, nombrePieces,
    colocationPossible, empreinteEnergie, dateDisponibilite,
    contactEmail, contactTelephone, statut
) VALUES (
    @loueur_id,
    'T2 moderne - Université Paul Sabatier',
    'Appartement T2 récent de 45m² à 10 minutes de l''Université Paul Sabatier. Cuisine américaine équipée, salle de bain avec baignoire. Balcon. Résidence sécurisée avec digicode et interphone. Parking privé. Charges comprises (eau, chauffage).',
    '8 avenue de la Science',
    'Toulouse',
    '31400',
    'T2',
    650.00,
    45.00,
    2,
    FALSE,
    'B',
    '2024-08-20',
    'contact@dormquest.fr',
    '0612345678',
    'active'
);
SET @annonce3 = LAST_INSERT_ID();
INSERT INTO criteres_logement (idAnnonce, accesPMR, eligibleAPL, statutBoursier, animauxAcceptes, parkingDisponible, meuble)
VALUES (@annonce3, TRUE, TRUE, TRUE, FALSE, TRUE, TRUE);

-- ==========================================
-- ANNONCE 4 : Résidence étudiante à Bordeaux
-- ==========================================
INSERT INTO annonces (
    idLoueur, titre, description, adresse, ville, codePostal,
    typeLogement, prixMensuel, superficie, nombrePieces,
    colocationPossible, empreinteEnergie, dateDisponibilite,
    contactEmail, contactTelephone, statut
) VALUES (
    @loueur_id,
    'Studio en résidence étudiante - Bordeaux Centre',
    'Studio de 20m² dans résidence étudiante récente et sécurisée. Salle de sport, laverie, salle de travail commune. Kitchenette et salle de bain privative. Tout inclus : internet, électricité, eau. Proche tram ligne B.',
    'Résidence Campus Victoire, 12 cours Pasteur',
    'Bordeaux',
    '33000',
    'residence_etudiante',
    550.00,
    20.00,
    1,
    FALSE,
    'A',
    '2024-09-01',
    'contact@dormquest.fr',
    '0612345678',
    'active'
);

SET @annonce4 = LAST_INSERT_ID();

INSERT INTO criteres_logement (idAnnonce, accesPMR, eligibleAPL, statutBoursier, animauxAcceptes, parkingDisponible, meuble)
VALUES (@annonce4, TRUE, TRUE, TRUE, FALSE, FALSE, TRUE);

-- ==========================================
-- ANNONCE 5 : Chambre chez l'habitant à Lille
-- ==========================================
INSERT INTO annonces (
    idLoueur, titre, description, adresse, ville, codePostal,
    typeLogement, prixMensuel, superficie, nombrePieces,
    colocationPossible, empreinteEnergie, dateDisponibilite,
    contactEmail, contactTelephone, statut
) VALUES (
    @loueur_id,
    'Chambre meublée chez l''habitant - Vieux Lille',
    'Chambre de 12m² dans maison familiale chaleureuse. Accès à la cuisine, salle de bain partagée. Internet inclus. Petit-déjeuner possible en option. Quartier historique du Vieux Lille, très bien desservi. Ambiance familiale et bienveillante.',
    '23 rue de la Monnaie',
    'Lille',
    '59000',
    'chambre_habitant',
    350.00,
    12.00,
    1,
    FALSE,
    'E',
    '2024-09-01',
    'contact@dormquest.fr',
    '0612345678',
    'active'
);

SET @annonce5 = LAST_INSERT_ID();

INSERT INTO criteres_logement (idAnnonce, accesPMR, eligibleAPL, animauxAcceptes, parkingDisponible, meuble)
VALUES (@annonce5, FALSE, FALSE, FALSE, FALSE, TRUE);

-- ==========================================
-- ANNONCE 6 : Studio ARCHIVÉ (pour tester le filtre)
-- ==========================================
INSERT INTO annonces (
    idLoueur, titre, description, adresse, ville, codePostal,
    typeLogement, prixMensuel, superficie, nombrePieces,
    colocationPossible, empreinteEnergie, dateDisponibilite,
    contactEmail, contactTelephone, statut
) VALUES (
    @loueur_id,
    'Studio rénové - Montpellier (LOUÉ)',
    'Studio de 22m² entièrement rénové proche de la fac de médecine. Cette annonce est désormais archivée car le logement a été loué.',
    '5 rue du Faubourg Boutonnet',
    'Montpellier',
    '34000',
    'studio',
    580.00,
    22.00,
    1,
    FALSE,
    'C',
    '2024-07-01',
    'contact@dormquest.fr',
    '0612345678',
    'archivee'
);

-- ==========================================
-- AJOUT DE CANDIDATURES FICTIVES
-- ==========================================

-- D'abord, crée quelques étudiants de test (ou utilise des ID existants)
-- Remplace les @etudiant_id par de vrais ID d'étudiants de ta base

-- Candidature pour l'annonce 1
INSERT INTO candidatures (idEtudiant, idAnnonce, message, statut)
VALUES 
    (1, @annonce1, 'Bonjour, je suis intéressé par ce studio. Je suis en M1 de droit à la Sorbonne. Sérieux et non-fumeur.', 'en_attente'),
    (1, @annonce2, 'Je recherche une colocation sympa pour la rentrée. J''adore cuisiner et je suis plutôt calme !', 'en_attente');

-- Ajouter des favoris
INSERT INTO favoris (idEtudiant, idAnnonce)
VALUES 
    (1, @annonce1),
    (1, @annonce3),
    (1, @annonce4);

-- ==========================================
-- VÉRIFICATION
-- ==========================================

-- Afficher toutes les annonces créées
SELECT 
    id,
    titre,
    ville,
    prixMensuel,
    statut,
    DATE_FORMAT(dateCreation, '%d/%m/%Y') as date_creation
FROM annonces 
WHERE idLoueur = @loueur_id
ORDER BY dateCreation DESC;

-- Afficher le résumé
SELECT 
    COUNT(*) as total_annonces,
    SUM(CASE WHEN statut = 'active' THEN 1 ELSE 0 END) as annonces_actives,
    SUM(CASE WHEN statut = 'archivee' THEN 1 ELSE 0 END) as annonces_archivees
FROM annonces 
WHERE idLoueur = @loueur_id;