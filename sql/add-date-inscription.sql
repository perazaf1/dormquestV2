-- Ajouter la colonne dateInscription si elle n'existe pas
ALTER TABLE utilisateurs ADD COLUMN IF NOT EXISTS dateInscription DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Pour les utilisateurs existants, définir la date d'inscription à aujourd'hui
UPDATE utilisateurs SET dateInscription = NOW() WHERE dateInscription IS NULL;
