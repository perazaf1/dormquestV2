<?php
// ============================================
// TRAITEMENT.PHP - GESTION DES CANDIDATURES
// ============================================

// Démarrer la session
session_start();

// Inclure le fichier de configuration de la base de données
require_once "config.php";

// Définir le charset
header('Content-Type: text/html; charset=utf-8');

// ============================================
// VÉRIFIER QUE LE FORMULAIRE EST SOUMIS
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: candidature.html');
    exit('Accès non autorisé');
}

// ============================================
// FONCTION DE NETTOYAGE DES DONNÉES
// ============================================
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// ============================================
// RÉCUPÉRATION ET NETTOYAGE DES DONNÉES
// ============================================
$errors = [];
$success = false;

// Informations personnelles
$nom = cleanInput($_POST['nom'] ?? '');
$prenom = cleanInput($_POST['prenom'] ?? '');
$email = cleanInput($_POST['email'] ?? '');
$telephone = cleanInput($_POST['telephone'] ?? '');
$date_naissance = cleanInput($_POST['date_naissance'] ?? '');
$nationalite = cleanInput($_POST['nationalite'] ?? '');
$adresse = cleanInput($_POST['adresse'] ?? '');

// Informations académiques
$universite = cleanInput($_POST['universite'] ?? '');
$niveau_etude = cleanInput($_POST['niveau_etude'] ?? '');
$filiere = cleanInput($_POST['filiere'] ?? '');
$numero_etudiant = cleanInput($_POST['numero_etudiant'] ?? '');

// Préférences de logement
$type_logement = cleanInput($_POST['type_logement'] ?? '');
$budget = cleanInput($_POST['budget'] ?? '');
$duree_sejour = cleanInput($_POST['duree_sejour'] ?? '');
$date_entree = cleanInput($_POST['date_entree'] ?? '');
$equipements = isset($_POST['equipements']) ? $_POST['equipements'] : [];
$equipements_str = implode(', ', array_map('cleanInput', $equipements));

// Informations complémentaires
$motivation = cleanInput($_POST['motivation'] ?? '');
$commentaires = cleanInput($_POST['commentaires'] ?? '');
$accept_conditions = isset($_POST['accept_conditions']) ? 1 : 0;

// ============================================
// VALIDATIONS CÔTÉ SERVEUR
// ============================================

// Validation du nom et prénom
if (empty($nom) || strlen($nom) < 2) {
    $errors[] = "Le nom est invalide";
}
if (empty($prenom) || strlen($prenom) < 2) {
    $errors[] = "Le prénom est invalide";
}

// Validation de l'email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'adresse email est invalide";
}

// Validation du téléphone (format français)
$telephone_clean = preg_replace('/[^0-9]/', '', $telephone);
if (empty($telephone) || !preg_match('/^0[1-9][0-9]{8}$/', $telephone_clean)) {
    $errors[] = "Le numéro de téléphone est invalide";
}

// Validation de la date de naissance
if (empty($date_naissance)) {
    $errors[] = "La date de naissance est obligatoire";
} else {
    $birthDate = new DateTime($date_naissance);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    
    if ($age < 16 || $age > 100) {
        $errors[] = "L'âge doit être entre 16 et 100 ans";
    }
}

// Validation du budget
if (empty($budget) || $budget < 200 || $budget > 5000) {
    $errors[] = "Le budget doit être entre 200€ et 5000€";
}

// Validation de la date d'entrée
if (empty($date_entree)) {
    $errors[] = "La date d'entrée est obligatoire";
} else {
    $entreeDate = new DateTime($date_entree);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($entreeDate < $today) {
        $errors[] = "La date d'entrée ne peut pas être dans le passé";
    }
}

// Validation de la motivation
if (empty($motivation) || strlen($motivation) < 50) {
    $errors[] = "La lettre de motivation doit contenir au moins 50 caractères";
}

// Validation des conditions
if ($accept_conditions !== 1) {
    $errors[] = "Vous devez accepter les conditions générales";
}

// ============================================
// GESTION DES FICHIERS UPLOADÉS
// ============================================
$upload_dir = 'uploads/';
$uploaded_files = [];

// Créer les dossiers s'ils n'existent pas
$directories = [
    $upload_dir . 'cartes_etudiant/',
    $upload_dir . 'pieces_identite/',
    $upload_dir . 'justificatifs/'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Fonction de validation et upload de fichier
function uploadFile($fileInput, $targetDir, $fieldName) {
    global $errors, $uploaded_files;
    
    if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] === UPLOAD_ERR_NO_FILE) {
        // Fichier non obligatoire ou manquant
        if ($fieldName === 'carte_etudiant' || $fieldName === 'piece_identite') {
            $errors[] = "Le fichier $fieldName est obligatoire";
        }
        return null;
    }
    
    $file = $_FILES[$fileInput];
    
    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Erreur lors de l'upload du fichier $fieldName";
        return null;
    }
    
    // Vérifier la taille (5MB max)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        $errors[] = "Le fichier $fieldName est trop volumineux (max 5MB)";
        return null;
    }
    
    // Vérifier le type MIME
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = "Le format du fichier $fieldName n'est pas autorisé (PDF, JPG, PNG uniquement)";
        return null;
    }
    
    // Générer un nom de fichier unique et sécurisé
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetFile = $targetDir . $filename;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $uploaded_files[$fieldName] = $targetFile;
        return $targetFile;
    } else {
        $errors[] = "Erreur lors de l'enregistrement du fichier $fieldName";
        return null;
    }
}

// Upload des fichiers
$carte_etudiant_path = uploadFile('carte_etudiant', $directories[0], 'carte_etudiant');
$piece_identite_path = uploadFile('piece_identite', $directories[1], 'piece_identite');
$justificatif_revenus_path = uploadFile('justificatif_revenus', $directories[2], 'justificatif_revenus');

// ============================================
// SI PAS D'ERREURS, INSÉRER DANS LA BASE
// ============================================
if (empty($errors)) {
    try {
        // Préparer la requête SQL
        $sql = "INSERT INTO candidatures (
            nom, prenom, email, telephone, date_naissance, nationalite, adresse,
            universite, niveau_etude, filiere, numero_etudiant,
            type_logement, budget, duree_sejour, date_entree, equipements,
            motivation, commentaires,
            carte_etudiant, piece_identite, justificatif_revenus,
            date_candidature, statut
        ) VALUES (
            :nom, :prenom, :email, :telephone, :date_naissance, :nationalite, :adresse,
            :universite, :niveau_etude, :filiere, :numero_etudiant,
            :type_logement, :budget, :duree_sejour, :date_entree, :equipements,
            :motivation, :commentaires,
            :carte_etudiant, :piece_identite, :justificatif_revenus,
            NOW(), 'en_attente'
        )";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind des paramètres
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':date_naissance', $date_naissance);
        $stmt->bindParam(':nationalite', $nationalite);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':universite', $universite);
        $stmt->bindParam(':niveau_etude', $niveau_etude);
        $stmt->bindParam(':filiere', $filiere);
        $stmt->bindParam(':numero_etudiant', $numero_etudiant);
        $stmt->bindParam(':type_logement', $type_logement);
        $stmt->bindParam(':budget', $budget);
        $stmt->bindParam(':duree_sejour', $duree_sejour);
        $stmt->bindParam(':date_entree', $date_entree);
        $stmt->bindParam(':equipements', $equipements_str);
        $stmt->bindParam(':motivation', $motivation);
        $stmt->bindParam(':commentaires', $commentaires);
        $stmt->bindParam(':carte_etudiant', $carte_etudiant_path);
        $stmt->bindParam(':piece_identite', $piece_identite_path);
        $stmt->bindParam(':justificatif_revenus', $justificatif_revenus_path);
        
        // Exécuter la requête
        if ($stmt->execute()) {
            $candidature_id = $pdo->lastInsertId();
            $success = true;
            
            // ============================================
            // ENVOI D'EMAIL DE CONFIRMATION
            // ============================================
            $to = $email;
            $subject = "Confirmation de votre candidature - Logement Étudiant";
            $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #137C8B, #7A90A4); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #137C8B; }
                    .footer { text-align: center; margin-top: 30px; color: #777; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Candidature Reçue !</h1>
                    </div>
                    <div class='content'>
                        <p>Bonjour <strong>$prenom $nom</strong>,</p>
                        <p>Nous avons bien reçu votre candidature pour un logement étudiant.</p>
                        
                        <div class='info'>
                            <h3>Récapitulatif de votre candidature :</h3>
                            <p><strong>Numéro de candidature :</strong> #$candidature_id</p>
                            <p><strong>Type de logement :</strong> $type_logement</p>
                            <p><strong>Budget :</strong> $budget €/mois</p>
                            <p><strong>Date d'entrée souhaitée :</strong> $date_entree</p>
                        </div>
                        
                        <p>Notre équipe va examiner votre dossier dans les plus brefs délais. Vous recevrez une réponse sous 5 à 7 jours ouvrables.</p>
                        
                        <p><strong>Statut actuel :</strong> En attente de traitement</p>
                        
                        <p>Si vous avez des questions, n'hésitez pas à nous contacter à <a href='mailto:contact@logement-etudiant.fr'>contact@logement-etudiant.fr</a></p>
                        
                        <p>Cordialement,<br><strong>L'équipe Logement Étudiant</strong></p>
                    </div>
                    <div class='footer'>
                        <p>Cet email est envoyé automatiquement, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Logement Étudiant <noreply@logement-etudiant.fr>" . "\r\n";
            
            // Envoyer l'email
            mail($to, $subject, $message, $headers);
            
            // ============================================
            // NOTIFICATION ADMIN (optionnel)
            // ============================================
            $admin_email = "admin@logement-etudiant.fr";
            $admin_subject = "Nouvelle candidature reçue - #$candidature_id";
            $admin_message = "
            <html>
            <body>
                <h2>Nouvelle candidature reçue</h2>
                <p><strong>Candidat :</strong> $prenom $nom</p>
                <p><strong>Email :</strong> $email</p>
                <p><strong>Téléphone :</strong> $telephone</p>
                <p><strong>Université :</strong> $universite</p>
                <p><strong>Type de logement :</strong> $type_logement</p>
                <p><strong>Budget :</strong> $budget €</p>
                <p><a href='http://votresite.com/admin/candidatures.php?id=$candidature_id'>Voir la candidature complète</a></p>
            </body>
            </html>
            ";
            
            mail($admin_email, $admin_subject, $admin_message, $headers);
            
            // Redirection vers page de succès
            header('Location: succes.html?id=' . $candidature_id);
            exit();
            
        } else {
            $errors[] = "Erreur lors de l'enregistrement de la candidature";
        }
        
    } catch (PDOException $e) {
        $errors[] = "Erreur de base de données : " . $e->getMessage();
        error_log("Erreur PDO : " . $e->getMessage()); // Log l'erreur
    }
}

// ============================================
// AFFICHAGE DES ERREURS
// ============================================
if (!empty($errors)) {
    // Supprimer les fichiers uploadés en cas d'erreur
    foreach ($uploaded_files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    echo '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur - Candidature</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #B8CBD0, #f8f9fa);
                padding: 20px;
            }
            .container {
                max-width: 600px;
                margin: 50px auto;
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 8px 32px rgba(19, 124, 139, 0.2);
            }
            h1 {
                color: #e74c3c;
                text-align: center;
            }
            .errors {
                background: #ffe5e5;
                border-left: 4px solid #e74c3c;
                padding: 20px;
                margin: 20px 0;
                border-radius: 8px;
            }
            .errors ul {
                margin: 10px 0;
                padding-left: 20px;
            }
            .errors li {
                margin: 8px 0;
                color: #c0392b;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: #137C8B;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                margin-top: 20px;
                text-align: center;
            }
            .btn:hover {
                background: #0f6270;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>⚠ Erreur(s) détectée(s)</h1>
            <div class="errors">
                <strong>Veuillez corriger les erreurs suivantes :</strong>
                <ul>';
    
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    
    echo '      </ul>
            </div>
            <a href="candidature.html" class="btn">← Retour au formulaire</a>
        </div>
    </body>
    </html>';
    exit();
}
?>