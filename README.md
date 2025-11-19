# DormQuest

DormQuest est une petite application web PHP destinée à mettre en relation des étudiants et des loueurs pour la recherche de logements étudiants.

Ce README présente le projet, sa structure et la procédure rapide pour le lancer en local.

## Description

DormQuest permet aux étudiants de chercher et candidater à des annonces de logements, et aux loueurs de publier et gérer des annonces. L'application utilise PHP côté serveur, des pages HTML/CSS/JS côté client, et une base de données (MySQL/MariaDB) pour stocker les utilisateurs et les annonces.

## Fonctionnalités principales

- Inscription / connexion (étudiant / loueur)
- Gestion de sessions et rôles
- Tableau de bord pour étudiants et loueurs
- Upload de photos de profil
- Gestion simple des annonces (listing, favoris, candidatures) — en développement

## Structure du projet

Racine du projet (fichiers et dossiers importants) :

- `index.php` : page d'accueil
- `login.php`, `register.php`, `logout.php` : pages d'authentification
- `dashboard-etudiant.php`, `dashboard-loueur.php` : tableaux de bord selon le rôle
- `annonces.php`, `profil.php`, `contact.php`,`favoris.php` : pages applicatives
- `includes/` : fonctions partagées (ex. `auth.php`, `db.php`, `header.php`, `footer.php`)
- `config/` : fichiers de configuration (ex. `config.php`, `auth.php`)
- `css/`, `js/`, `images/` : assets front-end
- `uploads/profiles/` : emplacements des photos de profil uploadées
- `README/` : notes de développement (récaps, documentation interne)

## Prérequis

- XAMPP (Apache + PHP + MySQL) ou équivalent (WAMP, Laragon)
- PHP 7.4+ recommandé
- Base de données MySQL/MariaDB

## Installation et mise en place locale

1. Copier le dossier `dormQuest` dans le répertoire `htdocs` de XAMPP (ex. `C:\xampp\htdocs\dormQuest`).
2. Démarrer Apache et MySQL depuis le panneau XAMPP.
3. Créer une base de données MySQL pour le projet (par ex. `dormquest_db`) et importer le schéma si vous en disposez.
4. Mettre à jour les informations de connexion à la base dans le fichier de configuration :

   - Si le projet utilise `config/config.php` (ou `includes/db.php`) pour la connexion PDO, ouvrez-le et adaptez `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` selon votre environnement.

5. Assurez-vous que le dossier `uploads/profiles/` existe et soit inscriptible par le serveur web (permissions d'écriture).
6. Ouvrir dans un navigateur : `http://localhost/dormQuest/`.

## Notes sur la sécurité

- Les cookies « remember me » doivent être bien protégés en production (HTTPS, flags Secure/HttpOnly). Les tokens doivent être stockés de manière sécurisée (hash côté serveur).
- En production, activez HTTPS et vérifiez les en-têtes de sécurité (CSP, X-Frame-Options, etc.).
- Validez et échappez systématiquement les entrées/sorties (ex : htmlspecialchars pour l'affichage).

## Développement & contribution

- Le code est organisé de façon simple pour un projet PHP classique sans framework. Pour contribuer :
  1. Clonez le dépôt dans votre environnement local.
  2. Ajoutez les migrations / schéma SQL si nécessaire.
  3. Ouvrez une branche pour votre fonctionnalité et faites une Pull Request.

## Dépannage rapide

- Erreur de fichier inclus (ex. `Failed opening required 'includes/auth.php'`) : vérifiez les chemins d'inclusion et, si besoin, remplacez les includes relatifs par des includes basés sur `__DIR__` (ex. `require_once __DIR__ . '/includes/auth.php';`) pour garantir la résolution correcte des chemins.
- Problèmes de permissions d'upload : vérifiez les droits sur `uploads/`.

## Contact

Pour toute question, utilisez `contact.php` ou consultez les notes dans `README/login_recap.md` pour des précisions sur la gestion des sessions et de l'authentification.

---


