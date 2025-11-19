-- Table 1 : utilisateurs
CREATE TABLE utilisateurs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  prenom VARCHAR(100) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  motDePasse VARCHAR(255) NOT NULL,
  role ENUM('etudiant', 'loueur') NOT NULL,
  photoDeProfil VARCHAR(255),
  telephone VARCHAR(20),
  villeRecherche VARCHAR(100), -- Pour étudiants uniquement
  budget DECIMAL(10,2), -- Pour étudiants uniquement
  typeLoueur ENUM('particulier', 'agence', 'organisme', 'crous'), -- Pour loueurs uniquement
  dateInscription DATETIME DEFAULT CURRENT_TIMESTAMP,
  derniereConnexion DATETIME
);

-- Table 2 : annonces
CREATE TABLE annonces (
  id INT PRIMARY KEY AUTO_INCREMENT,
  idLoueur INT NOT NULL,
  titre VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  adresse VARCHAR(255) NOT NULL,
  ville VARCHAR(100) NOT NULL,
  codePostal VARCHAR(10) NOT NULL,
  typeLogement ENUM('studio', 'colocation', 'residence_etudiante', 'chambre_habitant') NOT NULL,
  prixMensuel DECIMAL(10,2) NOT NULL,
  superficie DECIMAL(6,2),
  nombrePieces INT,
  colocationPossible BOOLEAN DEFAULT FALSE,
  empreinteEnergie ENUM('A', 'B', 'C', 'D', 'E', 'F', 'G'),
  dateDisponibilite DATE,
  contactEmail VARCHAR(255) NOT NULL,
  contactTelephone VARCHAR(20),
  dateCreation DATETIME DEFAULT CURRENT_TIMESTAMP,
  dateModification DATETIME,
  statut ENUM('active', 'archivee') DEFAULT 'active',
  FOREIGN KEY (idLoueur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  INDEX idx_ville (ville),
  INDEX idx_codePostal (codePostal),
  INDEX idx_prix (prixMensuel)
);




-- Table 3 : photos_annonces
CREATE TABLE photos_annonces (
  id INT PRIMARY KEY AUTO_INCREMENT,
  idAnnonce INT NOT NULL,
  cheminPhoto VARCHAR(255) NOT NULL,
  ordre INT DEFAULT 0,
  dateAjout DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (idAnnonce) REFERENCES annonces(id) ON DELETE CASCADE,
  INDEX idx_annonce (idAnnonce)
);

-- Table 4 : criteres_logement
CREATE TABLE criteres_logement (
  id INT PRIMARY KEY AUTO_INCREMENT,
  idAnnonce INT UNIQUE NOT NULL,
  accesPMR BOOLEAN DEFAULT FALSE,
  eligibleAPL BOOLEAN DEFAULT FALSE,
  statutBoursier BOOLEAN DEFAULT FALSE,
  animauxAcceptes BOOLEAN DEFAULT FALSE,
  parkingDisponible BOOLEAN DEFAULT FALSE,
  meuble BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (idAnnonce) REFERENCES annonces(id) ON DELETE CASCADE
);

-- Table 5 : favoris
CREATE TABLE favoris (
  id INT PRIMARY KEY AUTO_INCREMENT,
  idEtudiant INT NOT NULL,
  idAnnonce INT NOT NULL,
  dateAjout DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (idEtudiant) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (idAnnonce) REFERENCES annonces(id) ON DELETE CASCADE,
  UNIQUE KEY unique_favori (idEtudiant, idAnnonce),
  INDEX idx_etudiant (idEtudiant)
);

-- Table 6 : candidatures
CREATE TABLE candidatures (
  id INT PRIMARY KEY AUTO_INCREMENT,
  idEtudiant INT NOT NULL,
  idAnnonce INT NOT NULL,
  statut ENUM('en_attente', 'acceptee', 'refusee', 'annulee') DEFAULT 'en_attente',
  message TEXT,
  dateEnvoi DATETIME DEFAULT CURRENT_TIMESTAMP,
  dateReponse DATETIME,
  FOREIGN KEY (idEtudiant) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (idAnnonce) REFERENCES annonces(id) ON DELETE CASCADE,
  INDEX idx_etudiant (idEtudiant),
  INDEX idx_annonce (idAnnonce),
  INDEX idx_statut (statut)
);




