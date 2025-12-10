-- Script de nettoyage de la base de données
-- Supprime les colonnes ajoutées pour la réinitialisation de mot de passe

-- Exécuter ce script dans phpMyAdmin ou via MySQL CLI

ALTER TABLE `utilisateurs`
DROP COLUMN IF EXISTS `reset_token_hash`,
DROP COLUMN IF EXISTS `reset_token_expires_at`;
