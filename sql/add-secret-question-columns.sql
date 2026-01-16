-- Migration: add secret question columns for password reset by secret question
-- Run this in your MySQL/MariaDB console or via phpMyAdmin
ALTER TABLE utilisateurs
  ADD COLUMN IF NOT EXISTS secret_question VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS secret_answer_hash VARCHAR(255) NULL;
