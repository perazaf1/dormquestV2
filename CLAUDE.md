# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

DormQuest is a PHP-based student housing platform connecting students with landlords. The application runs on XAMPP (Apache + PHP + MySQL) and uses a traditional server-side rendered architecture with progressive enhancement via JavaScript.

## Database Setup

**Create the database:**
```bash
# Start XAMPP Apache and MySQL services first
# Then create the database via phpMyAdmin or MySQL CLI
mysql -u root -p
CREATE DATABASE dormquest;
USE dormquest;
SOURCE sql/db.sql;
```

**Apply schema updates:**
```bash
# If additional migrations exist
mysql -u root -p dormquest < sql/database_improvements.sql
```

**Database credentials** are configured in:
- `includes/db.php` (direct PDO connection)
- `config/config.php` (centralized constants)

Default XAMPP credentials: `host=localhost, user=root, password=''`

## Development Workflow

**Starting the application:**
```bash
# Ensure XAMPP Apache and MySQL are running
# Access via: http://localhost/dormQuestV2/
```

**No build step required** - PHP is interpreted server-side. Changes are immediately visible after page refresh.

**File permissions for uploads:**
```bash
# Ensure upload directories are writable
chmod -R 777 uploads/profiles/
chmod -R 777 uploads/annonces/
```

## Architecture

### Core Configuration Pattern

The application uses a three-tier configuration system:

1. **`config/config.php`** - Central configuration with constants, utility functions, and security settings
   - Defines `ACCESS_ALLOWED` constant - all pages requiring config must define this first
   - Contains global helper functions: `url()`, `redirect()`, `e()`, `format_prix()`, `format_date()`
   - Security functions: `generate_csrf_token()`, `verify_csrf_token()`, `is_valid_image()`
   - Business constants: `TYPES_LOGEMENT`, `TYPES_LOUEUR`, `STATUTS_ANNONCE`, `STATUTS_CANDIDATURE`

2. **`includes/db.php`** - Database connection singleton
   - Creates global `$pdo` object with PDO::ERRMODE_EXCEPTION
   - UTF8MB4 charset for emoji support

3. **`includes/functions.php`** - Reusable database query functions
   - Centralizes all common SQL queries to avoid code duplication
   - 52+ functions organized by feature (users, listings, photos, favorites, applications)
   - Provides clean, testable abstraction over raw SQL
   - See "Database Query Functions" section below for details

### Authentication System

**Session management** (`includes/auth.php`):
- Session-based authentication with role-based access control (RBAC)
- Two user roles: `etudiant` (student) and `loueur` (landlord)
- Session data structure:
  ```php
  $_SESSION['user_id']       // int
  $_SESSION['user_role']     // 'etudiant' | 'loueur'
  $_SESSION['user_prenom']   // string
  $_SESSION['user_nom']      // string
  $_SESSION['user_email']    // string
  $_SESSION['user_photo']    // string (path)
  $_SESSION['login_time']    // timestamp
  $_SESSION['csrf_token']    // string
  ```

**Key auth functions:**
- `is_logged_in()` - Check if user authenticated
- `require_login()` - Force authentication or redirect to login.php
- `require_role($role)` - Force specific role or redirect
- `require_etudiant()` / `require_loueur()` - Role-specific guards
- `get_user_id()`, `get_user_role()`, `get_user_fullname()`, `get_user_photo()` - Session accessors
- `refresh_session($pdo)` - Reload user data from DB into session
- `logout()` - Destroy session and cookies

**"Remember me" functionality:**
- Uses `remember_token` cookie (30 days default)
- Token verification happens in `login.php`

### Page Structure Pattern

All user-facing pages follow this structure:

```php
<?php
session_start();
define('ACCESS_ALLOWED', true);
require_once 'config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Role-based protection (if needed)
require_etudiant(); // or require_loueur()

// Page-specific logic using database functions
$isLoggedIn = is_logged_in();
$userType = get_user_role();
$annonces = get_all_annonces_actives($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page Title - DormQuest</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Page content -->

    <?php include 'includes/footer.php'; ?>
    <script src="js/specific-page.js"></script>
</body>
</html>
```

### API Endpoints Pattern

API files in `api/` directory handle AJAX requests:

```php
<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Check authentication
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

// Verify CSRF for mutations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
        exit;
    }
}

// Business logic using database functions
try {
    $user = get_user_by_id($pdo, get_user_id());
    $annonces = get_annonces_by_loueur($pdo, get_user_id());
    echo json_encode(['success' => true, 'data' => $annonces]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

### Database Schema

**Main tables:**
- `utilisateurs` - Users (students and landlords)
  - Role: `etudiant` | `loueur`
  - Type loueur: `particulier` | `agence` | `organisme` | `crous`
  - Fields: id, prenom, nom, email, motDePasse, role, photoDeProfil, telephone, villeRecherche, budget, dateInscription

- `annonces` - Housing listings
  - Type logement: `studio` | `colocation` | `residence_etudiante` | `chambre_habitant`
  - Statut: `active` | `archivee`
  - Fields: id, idLoueur, titre, description, adresse, ville, typeLogement, prixMensuel, superficie, statut

- `photos_annonces` - Listing photos (max 8 per listing)
- `criteres_logement` - Housing criteria (PMR access, APL eligible, furnished, etc.)
- `favoris` - Student favorites (many-to-many: students ↔ listings)
- `candidatures` - Student applications
  - Statut: `en_attente` | `acceptee` | `refusee` | `annulee`

**Foreign key cascades:**
- All relations use `ON DELETE CASCADE`
- Deleting a user deletes their listings, applications, favorites
- Deleting a listing deletes its photos, criteria, favorites, applications

### Database Query Functions

**Location:** `includes/functions.php`

This file centralizes all common database queries to eliminate code duplication and improve maintainability. Instead of writing raw SQL in every file, use these pre-built functions.

**Benefits:**
- ✅ No code duplication (DRY principle)
- ✅ Fix bugs once, fixed everywhere
- ✅ Easier for team members to use (no SQL knowledge required)
- ✅ More readable code
- ✅ Consistent query patterns
- ✅ Easier to test

**Usage pattern:**
```php
// Instead of this:
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Use this:
$user = get_user_by_id($pdo, $id);
```

#### User Functions

```php
get_user_by_id($pdo, $id)                          // Get user by ID
get_user_by_email($pdo, $email)                    // Get user by email
email_exists($pdo, $email, $excludeUserId = null)  // Check if email exists
get_user_photo($pdo, $userId)                      // Get profile photo path
update_user_photo($pdo, $userId, $photoPath)       // Update profile photo
delete_user_photo($pdo, $userId)                   // Remove profile photo
```

#### Annonce (Listing) Functions

```php
get_all_annonces_actives($pdo)                     // Get all active listings
get_annonce_by_id($pdo, $id)                       // Get listing with landlord info
get_annonces_by_loueur($pdo, $loueurId, $statut = null) // Get landlord's listings
count_annonces_by_loueur($pdo, $loueurId, $statut = null) // Count listings
search_annonces($pdo, $filters = [])               // Search with filters
update_annonce_statut($pdo, $annonceId, $statut)  // Change status (active/archivee)
delete_annonce($pdo, $annonceId)                   // Delete listing
```

**Search filters example:**
```php
$filters = [
    'ville' => 'Paris',
    'typeLogement' => 'studio',
    'prixMin' => 400,
    'prixMax' => 800
];
$annonces = search_annonces($pdo, $filters);
```

#### Photo Functions

```php
get_photos_annonce($pdo, $annonceId)               // Get all photos for listing
count_photos_annonce($pdo, $annonceId)             // Count photos
add_photo_annonce($pdo, $annonceId, $cheminPhoto)  // Add new photo
delete_photo_annonce($pdo, $photoId)               // Remove photo (returns path)
```

#### Criteria Functions

```php
get_criteres_annonce($pdo, $annonceId)             // Get housing criteria
```

#### Favorites Functions

```php
is_favori($pdo, $etudiantId, $annonceId)           // Check if favorited
get_favoris_by_etudiant($pdo, $etudiantId)         // Get student's favorites
count_favoris_by_etudiant($pdo, $etudiantId)       // Count favorites
add_favori($pdo, $etudiantId, $annonceId)          // Add to favorites
remove_favori($pdo, $etudiantId, $annonceId)       // Remove from favorites
```

#### Application (Candidature) Functions

```php
get_candidatures_by_etudiant($pdo, $etudiantId)    // Get student's applications
get_candidatures_by_loueur($pdo, $loueurId)        // Get landlord's applications
count_candidatures_by_etudiant($pdo, $etudiantId, $statut = null) // Count applications
has_candidature($pdo, $etudiantId, $annonceId)     // Check if already applied
update_candidature_statut($pdo, $candidatureId, $statut) // Update status
```

**Real-world examples:**

```php
// Before: 8 lines of SQL
$stmt = $pdo->prepare("
    SELECT a.*, u.prenom, u.nom
    FROM annonces a
    JOIN utilisateurs u ON a.idLoueur = u.id
    WHERE a.statut = 'active'
    ORDER BY a.dateCreation DESC
");
$stmt->execute();
$annonces = $stmt->fetchAll();

// After: 1 line
$annonces = get_all_annonces_actives($pdo);

// Before: Checking email in register
$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $errors[] = "Email déjà utilisé";
}

// After: Readable and reusable
if (email_exists($pdo, $email)) {
    $errors[] = "Email déjà utilisé";
}

// Counting with optional filter
$total = count_annonces_by_loueur($pdo, $loueurId);        // All listings
$active = count_annonces_by_loueur($pdo, $loueurId, 'active'); // Active only
```

### Frontend Architecture

**CSS organization:**
- `css/style.css` - Global styles with CSS variables (`:root`)
- `css/annonces.css`, etc. - Page-specific styles
- Inline `<style>` blocks in `includes/header.php` and `includes/footer.php`

**JavaScript organization:**
- `js/main.js` - Global utilities
- `js/[page].js` - Page-specific functionality
- Inline `<script>` blocks for component-specific logic (e.g., mobile menu in header)

**No build tools** - Vanilla JavaScript with ES5/ES6 mix. No transpilation or bundling.

### File Upload Handling

**Upload locations:**
- Profile photos: `uploads/profiles/`
- Listing photos: `uploads/annonces/`

**Validation (see config.php):**
- Max size: 2MB (`MAX_FILE_SIZE`)
- Allowed types: `image/jpeg`, `image/png`, `image/jpg`
- Server-side validation with `is_valid_image($file)`

**Filename generation:**
- Use `generate_unique_filename($extension)` to prevent collisions
- Format: `{uniqid}_{timestamp}.{ext}`

### Security Considerations

**CSRF Protection:**
- Generate token: `generate_csrf_token()` (stores in `$_SESSION['csrf_token']`)
- Verify token: `verify_csrf_token($token)`
- Include in forms: `csrf_field()` outputs hidden input

**XSS Prevention:**
- Always use `e($string)` for output escaping (alias for `htmlspecialchars`)
- Example: `echo e($user['nom']);`

**SQL Injection Prevention:**
- Always use prepared statements with PDO
- Never concatenate user input into queries

**Password Security:**
- Hash with `password_hash($password, PASSWORD_DEFAULT)`
- Verify with `password_verify($input, $hash)`

**Debug mode:**
- Set `DEBUG_MODE` in config.php
- **Must be false in production** to hide error messages

## Common Tasks

### Adding a new page

1. Create `new-page.php` in root
2. Follow page structure pattern (session, includes, auth checks)
3. Create `css/new-page.css` if needed
4. Create `js/new-page.js` if needed
5. Add navigation link in `includes/header.php` if appropriate

### Adding an API endpoint

1. Create `api/new-endpoint.php`
2. Follow API pattern (JSON response, auth check, CSRF verification)
3. Include `functions.php` and use database functions instead of raw SQL
4. Call from frontend with `fetch()` including CSRF token
5. Handle response in JavaScript

### Using database functions

Instead of writing raw SQL queries:

```php
// DON'T write this in your files
$stmt = $pdo->prepare("SELECT * FROM annonces WHERE idLoueur = ?");
$stmt->execute([$loueurId]);
$annonces = $stmt->fetchAll();

// DO use the function from functions.php
require_once 'includes/functions.php';
$annonces = get_annonces_by_loueur($pdo, $loueurId);
```

**If the function you need doesn't exist:**
1. Add it to `includes/functions.php` with proper documentation
2. Follow the naming pattern: `verb_noun_by_property()` (e.g., `get_user_by_email`)
3. Use it everywhere in your code

### Modifying user session data

After updating user data in database:
```php
require_once 'includes/auth.php';
refresh_session($pdo); // Reloads session from DB
```

### Working with dates

- Database: `DATETIME` columns in MySQL format `Y-m-d H:i:s`
- Display: Use `format_date($date)` for `d/m/Y` or `format_datetime($datetime)` for `d/m/Y à H:i`
- Timezone: Europe/Paris (set in config.php)

### Role-based features

**For students only:**
- Favorites system (`favoris.php`, `api/toggle-favori.php`)
- Applications (`candidatures.php`, `api/candidature-action.php`)
- Budget and search preferences in profile

**For landlords only:**
- Create listings (`create-annonce.php`)
- Manage listings (`dashboard-loueur.php`)
- View applications
- Type loueur field (particulier/agence/organisme/crous)

## Important Notes

- **Path resolution:** Use `__DIR__` for includes to avoid relative path issues
- **Session initialization:** Always call `session_start()` at the top of every page before any output
- **Constants access:** Define `ACCESS_ALLOWED` before including `config/config.php`
- **Database queries:** Always prefer using functions from `includes/functions.php` over writing raw SQL
  - Avoids code duplication
  - Easier maintenance (fix once, fixed everywhere)
  - More readable and consistent
  - Add new functions to this file when you write reusable queries
- **Database transactions:** Not currently used, but consider for complex multi-table operations
- **Error handling:** In DEBUG_MODE, errors display. In production, use try-catch and log errors
- **No ORM:** Direct PDO usage throughout, abstracted through `functions.php`
- **No routing:** Each PHP file maps to a URL (e.g., `annonces.php` → `/dormQuestV2/annonces.php`)
- **Image validation:** Server-side only. Always validate MIME type and extension with `is_valid_image()`
