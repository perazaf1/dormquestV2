# DormQuest

**Plateforme de mise en relation étudiants-loueurs pour la recherche de logements étudiants.**

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Apache-FB7A24?logo=xampp&logoColor=white)

---

## Aperçu

DormQuest permet aux **étudiants** de rechercher et candidater à des annonces de logements, et aux **loueurs** (particuliers, agences, organismes, CROUS) de publier et gérer leurs offres. L'application utilise une architecture PHP classique côté serveur avec une base de données MySQL/MariaDB.

---

## Fonctionnalités

### Étudiants
- Recherche d'annonces avec filtres (ville, type, prix, superficie)
- Système de favoris pour sauvegarder les annonces
- Candidatures aux logements avec suivi du statut
- Tableau de bord personnalisé
- Gestion du profil et photo

### Loueurs
- Publication d'annonces avec photos multiples (jusqu'à 8)
- Gestion des candidatures reçues (accepter/refuser)
- Archivage et suppression d'annonces
- Tableau de bord avec statistiques
- Notifications des nouvelles candidatures

### Système
- Authentification avec sessions et "Se souvenir de moi"
- Réinitialisation de mot de passe par email
- Question secrète de récupération
- Protection CSRF sur tous les formulaires
- Rôles : `etudiant` et `loueur`

---

## Prérequis

| Outil | Version |
|-------|---------|
| PHP | 7.4+ |
| MySQL / MariaDB | 5.7+ |
| Apache | 2.4+ |
| XAMPP / WAMP / Laragon | Dernière version |

---

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/votre-repo/dormquestV2.git
```

Ou copier le dossier dans `C:\xampp\htdocs\dormQuestV2`

### 2. Créer la base de données

```sql
CREATE DATABASE dormquest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importer le schéma SQL

```bash
mysql -u root -p dormquest < sql/db.sql
```

Appliquer les migrations si nécessaire :
```bash
mysql -u root -p dormquest < sql/database_improvements.sql
mysql -u root -p dormquest < sql/add-secret-question-columns.sql
```

### 4. Configurer la connexion

Éditer `config/config.php` :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dormquest');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Configurer les permissions

```bash
# Windows (via explorateur) : Autoriser l'écriture sur uploads/
# Linux/Mac :
chmod -R 755 uploads/
```

### 6. Lancer l'application

1. Démarrer Apache et MySQL via XAMPP
2. Accéder à `http://localhost/dormQuestV2/`

---

## Structure du projet

```
dormQuestV2/
├── api/                    # Endpoints AJAX (JSON)
│   ├── candidature-action.php
│   ├── get-annonces.php
│   ├── postuler.php
│   ├── toggle-favori.php
│   └── ...
├── config/
│   └── config.php          # Configuration centralisée
├── css/                    # Styles par page
├── includes/
│   ├── auth.php            # Authentification & sessions
│   ├── db.php              # Connexion PDO
│   ├── functions.php       # 50+ fonctions utilitaires
│   ├── header.php          # En-tête commun
│   └── footer.php          # Pied de page commun
├── js/                     # JavaScript par page
├── sql/                    # Schémas et migrations
├── uploads/
│   ├── annonces/           # Photos des annonces
│   └── profiles/           # Photos de profil
├── index.php               # Page d'accueil
├── annonces.php            # Liste des annonces
├── annonce.php             # Détail d'une annonce
├── dashboard-etudiant.php  # Tableau de bord étudiant
├── dashboard-loueur.php    # Tableau de bord loueur
├── login.php               # Connexion
├── register.php            # Inscription
└── ...
```

---

## Types de logements

| Code | Description |
|------|-------------|
| `studio` | Studio |
| `colocation` | Colocation |
| `residence_etudiante` | Résidence étudiante |
| `chambre_habitant` | Chambre chez l'habitant |

---

## Types de loueurs

| Code | Description |
|------|-------------|
| `particulier` | Particulier |
| `agence` | Agence immobilière |
| `organisme` | Organisme de logement |
| `crous` | CROUS |

---

## API Endpoints

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/get-annonces.php` | GET | Liste des annonces avec filtres |
| `/api/get-villes.php` | GET | Autocomplétion des villes |
| `/api/postuler.php` | POST | Soumettre une candidature |
| `/api/toggle-favori.php` | POST | Ajouter/retirer un favori |
| `/api/candidature-action.php` | POST | Accepter/refuser candidature |
| `/api/send-contact.php` | POST | Envoyer un message de contact |

---

## Sécurité

- **CSRF** : Tokens générés par session, vérifiés sur chaque POST
- **XSS** : Échappement HTML via `htmlspecialchars()` (fonction `e()`)
- **SQL Injection** : Requêtes préparées PDO exclusivement
- **Mots de passe** : Hash bcrypt via `password_hash()`
- **Upload** : Validation MIME type + extension + taille max

### En production

- Activer HTTPS obligatoire
- Définir `Secure` et `HttpOnly` sur les cookies
- Configurer les headers CSP, X-Frame-Options
- Désactiver l'affichage des erreurs PHP

---

## Configuration Email

Pour la réinitialisation de mot de passe, configurer dans `config/config.php` :

```php
define('MAIL_FROM', 'noreply@dormquest.com');
define('MAIL_FROM_NAME', 'DormQuest');
define('SITE_URL', 'http://localhost/dormquestV2');
```

Voir [CONFIGURATION-EMAIL.md](CONFIGURATION-EMAIL.md) pour plus de détails.

---

## Dépannage

| Problème | Solution |
|----------|----------|
| `Failed opening required 'includes/...'` | Utiliser `__DIR__` dans les includes |
| Erreur d'upload | Vérifier permissions sur `uploads/` |
| Session perdue | Vérifier que `session_start()` est appelé |
| Erreur CSRF | Regénérer le token et réessayer |
| Email non envoyé | Vérifier la configuration SMTP |

---

## Contribution

1. Forker le projet
2. Créer une branche (`git checkout -b feature/ma-fonctionnalite`)
3. Commiter (`git commit -m 'Ajout de ma fonctionnalité'`)
4. Pusher (`git push origin feature/ma-fonctionnalite`)
5. Ouvrir une Pull Request

---

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

---

**DormQuest** - Trouvez le logement parfait pour vos études !
