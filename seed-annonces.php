<?php
/**
 * Script de génération de données fictives pour DormQuest
 * Génère des loueurs et des annonces avec photos réelles
 *
 * ATTENTION: Ce script va ajouter des données à votre base de données
 * Usage: http://localhost/dormQuestV2/seed-annonces.php
 */

session_start();
define('ACCESS_ALLOWED', true);
require_once 'config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once 'includes/functions.php';

// Augmenter le temps d'exécution (10 minutes)
set_time_limit(600);
ini_set('max_execution_time', 600);

// Configuration
$NB_LOUEURS = 30;
$NB_ANNONCES = 120;
$NB_PHOTOS_PAR_ANNONCE = rand(3, 6); // Entre 3 et 6 photos par annonce

// Données de seed
$prenoms = ['Jean', 'Marie', 'Pierre', 'Sophie', 'Luc', 'Emma', 'Thomas', 'Camille', 'Nicolas', 'Julie', 'Alexandre', 'Léa', 'Antoine', 'Clara', 'Maxime', 'Sarah', 'Hugo', 'Manon', 'Louis', 'Chloé'];
$noms = ['Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Richard', 'Petit', 'Durand', 'Leroy', 'Moreau', 'Simon', 'Laurent', 'Lefebvre', 'Michel', 'Garcia', 'David', 'Bertrand', 'Roux', 'Vincent', 'Fournier'];

$grandesVilles = ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Bordeaux', 'Lille', 'Nice', 'Nantes'];
$villesUniversitaires = ['Grenoble', 'Rennes', 'Montpellier', 'Strasbourg', 'Dijon', 'Reims', 'Caen', 'Limoges', 'Poitiers', 'Clermont-Ferrand'];
$petitesVilles = ['Annecy', 'Chambéry', 'La Rochelle', 'Pau', 'Brest', 'Besançon', 'Perpignan', 'Troyes'];

$villes = array_merge($grandesVilles, $villesUniversitaires, $petitesVilles);

$typesLogement = ['studio', 'colocation', 'residence_etudiante', 'chambre_habitant'];
$typesLoueur = ['particulier', 'agence', 'organisme', 'crous'];

$titresStudio = [
    'Studio lumineux centre-ville',
    'Studio moderne avec balcon',
    'Joli studio rénové',
    'Studio étudiant meublé',
    'Studio proche université',
    'Beau studio tout équipé',
    'Studio calme et confortable'
];

$titresColocation = [
    'Colocation conviviale 3 chambres',
    'Grande colocation avec jardin',
    'Colocation moderne et équipée',
    'Colocation étudiants centre-ville',
    'Colocation spacieuse et lumineuse',
    'Appart en coloc proche transports',
    'Belle colocation rénovée'
];

$titresResidence = [
    'Résidence étudiante tout confort',
    'Logement résidence services inclus',
    'Studio résidence sécurisée',
    'Résidence neuve proche campus',
    'Logement résidence avec laverie',
    'Studio résidence bien situé',
    'Résidence moderne toutes commodités'
];

$titresChambre = [
    'Chambre chez l\'habitant',
    'Chambre meublée maison calme',
    'Belle chambre dans maison familiale',
    'Chambre confortable chez l\'habitant',
    'Grande chambre avec salle de bain privée',
    'Chambre étudiante chez l\'habitant',
    'Jolie chambre meublée'
];

$descriptions = [
    "Idéal pour étudiant, ce logement offre tout le confort nécessaire. Proche des transports en commun et des commerces.",
    "Logement lumineux et bien agencé, parfait pour la vie étudiante. Cuisine équipée, salle de bain moderne.",
    "Dans un quartier calme et résidentiel, ce logement saura vous séduire par son charme et sa fonctionnalité.",
    "Entièrement meublé et équipé, emménagez avec vos valises ! Quartier dynamique avec toutes commodités à proximité.",
    "Logement refait à neuf, décoration soignée. Idéalement situé pour accéder facilement à l'université.",
    "Cadre de vie agréable dans un immeuble bien entretenu. Parfait pour se concentrer sur ses études.",
    "Espace de vie optimisé avec rangements. Connexion internet haut débit incluse.",
    "Logement chaleureux et accueillant, dans un environnement studieux. Proche bibliothèque universitaire."
];

// Mots-clés Unsplash pour des photos réelles d'appartements/logements
$unsplashKeywords = [
    'apartment-interior',
    'modern-apartment',
    'studio-apartment',
    'cozy-bedroom',
    'kitchen-interior',
    'living-room',
    'student-room',
    'bright-apartment'
];

/**
 * Télécharge une photo depuis Unsplash et la sauvegarde localement
 */
function download_unsplash_photo($keyword, $width = 800, $height = 600) {
    $upload_dir = __DIR__ . '/uploads/annonces/';

    // Créer le dossier si nécessaire
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // URL Unsplash Source (service gratuit qui retourne une image aléatoire)
    $unsplash_url = "https://source.unsplash.com/{$width}x{$height}/?" . urlencode($keyword);

    try {
        // Configuration du contexte pour timeout plus court
        $context = stream_context_create([
            'http' => [
                'timeout' => 10, // Timeout de 10 secondes
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        // Télécharger l'image avec timeout
        $image_data = @file_get_contents($unsplash_url, false, $context);

        if ($image_data === false || strlen($image_data) < 1000) {
            return null;
        }

        // Générer un nom de fichier unique
        $filename = 'seed_' . uniqid() . '_' . time() . '.jpg';
        $filepath = $upload_dir . $filename;

        // Sauvegarder l'image
        if (file_put_contents($filepath, $image_data)) {
            return 'uploads/annonces/' . $filename;
        }
    } catch (Exception $e) {
        // En cas d'erreur, retourner null
        return null;
    }

    return null;
}

/**
 * Génère un numéro de téléphone fictif français
 */
function generer_telephone() {
    $prefixes = ['06', '07'];
    $prefix = $prefixes[array_rand($prefixes)];
    return $prefix . rand(10000000, 99999999);
}

// Début de la génération
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Génération de données - DormQuest</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #27ae60; }
        .error { color: #e74c3c; }
        .info { color: #3498db; }
        .progress { background: #ecf0f1; padding: 10px; margin: 10px 0; border-radius: 5px; }
        h1 { color: #2c3e50; }
        .summary { background: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0; }
    </style>
</head>
<body>
<h1>🏠 Génération de données fictives DormQuest</h1>";

try {
    $start_time = microtime(true);
    $pdo->beginTransaction();

    // Étape 1: Générer les loueurs
    echo "<div class='progress'><strong>Étape 1/3:</strong> Génération de $NB_LOUEURS loueurs...</div>";
    $loueurs_ids = [];

    for ($i = 0; $i < $NB_LOUEURS; $i++) {
        $prenom = $prenoms[array_rand($prenoms)];
        $nom = $noms[array_rand($noms)];
        $email = strtolower($prenom . '.' . $nom . $i . '@example.com');
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $telephone = generer_telephone();
        $typeLoueur = $typesLoueur[array_rand($typesLoueur)];

        $stmt = $pdo->prepare("
            INSERT INTO utilisateurs (prenom, nom, email, motDePasse, role, telephone, typeLoueur, dateInscription)
            VALUES (?, ?, ?, ?, 'loueur', ?, ?, NOW())
        ");

        $stmt->execute([$prenom, $nom, $email, $password, $telephone, $typeLoueur]);
        $loueurs_ids[] = $pdo->lastInsertId();

        if (($i + 1) % 10 == 0) {
            echo "<p class='info'>✓ " . ($i + 1) . " loueurs créés...</p>";
            flush();
            ob_flush();
        }
    }

    echo "<p class='success'>✓ $NB_LOUEURS loueurs créés avec succès!</p>";

    // Étape 2: Générer les annonces
    echo "<div class='progress'><strong>Étape 2/3:</strong> Génération de $NB_ANNONCES annonces...</div>";
    $annonces_ids = [];

    for ($i = 0; $i < $NB_ANNONCES; $i++) {
        $idLoueur = $loueurs_ids[array_rand($loueurs_ids)];
        $ville = $villes[array_rand($villes)];
        $typeLogement = $typesLogement[array_rand($typesLogement)];

        // Choisir un titre selon le type
        switch ($typeLogement) {
            case 'studio':
                $titre = $titresStudio[array_rand($titresStudio)];
                $prixMin = 350;
                $prixMax = 700;
                $superficieMin = 18;
                $superficieMax = 30;
                break;
            case 'colocation':
                $titre = $titresColocation[array_rand($titresColocation)];
                $prixMin = 300;
                $prixMax = 600;
                $superficieMin = 60;
                $superficieMax = 120;
                break;
            case 'residence_etudiante':
                $titre = $titresResidence[array_rand($titresResidence)];
                $prixMin = 400;
                $prixMax = 800;
                $superficieMin = 20;
                $superficieMax = 35;
                break;
            case 'chambre_habitant':
                $titre = $titresChambre[array_rand($titresChambre)];
                $prixMin = 250;
                $prixMax = 500;
                $superficieMin = 12;
                $superficieMax = 25;
                break;
        }

        $titre .= " - $ville";
        $description = $descriptions[array_rand($descriptions)];
        $prixMensuel = rand($prixMin, $prixMax);
        $superficie = rand($superficieMin, $superficieMax);
        $adresse = rand(1, 150) . ' ' . ['rue', 'avenue', 'boulevard', 'place'][array_rand(['rue', 'avenue', 'boulevard', 'place'])] . ' ' . $noms[array_rand($noms)];

        // Ajout de l'annonce
        $stmt = $pdo->prepare("
            INSERT INTO annonces (idLoueur, titre, description, adresse, ville, typeLogement, prixMensuel, superficie, statut, dateCreation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->execute([$idLoueur, $titre, $description, $adresse, $ville, $typeLogement, $prixMensuel, $superficie]);
        $annonceId = $pdo->lastInsertId();
        $annonces_ids[] = $annonceId;

        // Ajouter des critères aléatoires
        $stmt = $pdo->prepare("
            INSERT INTO criteres_logement (idAnnonce, accesPMR, eligibleAPL, meuble, animauxAcceptes, parkingDisponible)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $annonceId,
            rand(0, 1),
            rand(0, 1),
            rand(0, 1),
            rand(0, 1),
            rand(0, 1)
        ]);

        if (($i + 1) % 20 == 0) {
            echo "<p class='info'>✓ " . ($i + 1) . " annonces créées...</p>";
            flush();
            ob_flush();
        }
    }

    echo "<p class='success'>✓ $NB_ANNONCES annonces créées avec succès!</p>";

    // Étape 3: Télécharger et ajouter les photos
    echo "<div class='progress'><strong>Étape 3/3:</strong> Téléchargement des photos depuis Unsplash (cela peut prendre quelques minutes)...</div>";

    $total_photos = 0;
    $photos_par_annonce = [];

    foreach ($annonces_ids as $index => $annonceId) {
        $nb_photos = rand(3, 5);  // Réduit à 3-5 photos
        $photos_par_annonce[$annonceId] = 0;
        $tentatives = 0;
        $max_tentatives = $nb_photos + 2; // Quelques tentatives supplémentaires en cas d'échec

        for ($j = 0; $j < $nb_photos && $tentatives < $max_tentatives; $j++) {
            $keyword = $unsplashKeywords[array_rand($unsplashKeywords)];
            $photo_path = download_unsplash_photo($keyword);
            $tentatives++;

            if ($photo_path) {
                $stmt = $pdo->prepare("INSERT INTO photos_annonces (idAnnonce, cheminPhoto) VALUES (?, ?)");
                $stmt->execute([$annonceId, $photo_path]);
                $total_photos++;
                $photos_par_annonce[$annonceId]++;
            } else {
                // En cas d'échec, réessayer avec cette photo
                $j--;
            }

            // Petit délai pour ne pas surcharger Unsplash (réduit à 0.1 seconde)
            usleep(100000);
        }

        if (($index + 1) % 10 == 0) {
            $temps_ecoule = round(microtime(true) - $start_time, 2);
            echo "<p class='info'>✓ Photos ajoutées pour " . ($index + 1) . " annonces ($total_photos photos) - {$temps_ecoule}s écoulées</p>";
            flush();
            ob_flush();
        }
    }

    echo "<p class='success'>✓ $total_photos photos téléchargées et ajoutées!</p>";

    $pdo->commit();

    // Résumé final
    echo "
    <div class='summary'>
        <h2>✅ Génération terminée avec succès!</h2>
        <ul>
            <li><strong>Loueurs créés:</strong> $NB_LOUEURS</li>
            <li><strong>Annonces créées:</strong> $NB_ANNONCES</li>
            <li><strong>Photos ajoutées:</strong> $total_photos</li>
        </ul>
        <p><strong>Identifiants de connexion loueurs:</strong></p>
        <ul>
            <li>Email: prenom.nom0@example.com (à prenom.nom" . ($NB_LOUEURS - 1) . "@example.com)</li>
            <li>Mot de passe: <code>password123</code></li>
        </ul>
        <p style='margin-top: 20px;'>
            <a href='annonces.php' style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Voir les annonces</a>
            <a href='index.php' style='background: #2ecc71; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Retour à l'accueil</a>
        </p>
    </div>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "<p class='error'>❌ Erreur: " . $e->getMessage() . "</p>";
    echo "<p>Trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}

echo "</body></html>";
