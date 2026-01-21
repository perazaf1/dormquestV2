<?php
// ============================================================================
// API pour gérer les favoris (Ajouter/Retirer)
// Fichier : api/toggle-favori.php
// ============================================================================

// Démarrer une session PHP pour pouvoir accéder aux informations de connexion de l'utilisateur
session_start();

// Indiquer au navigateur que cette page renvoie du JSON (format de données structuré)
// Cela permet au JavaScript de traiter correctement la réponse
header('Content-Type: application/json');

// Inclure les fichiers nécessaires pour vérifier l'authentification et accéder à la base de données
require_once __DIR__ . '/../includes/db.php';        // Connexion à la base de données ($pdo)
require_once __DIR__ . '/../includes/functions.php'; // Fonctions de base de données
require_once __DIR__ . '/../includes/auth.php';      // Fonctions de vérification de connexion

// ============================================================================
// ÉTAPE 1 : Vérifications de sécurité
// ============================================================================

// Vérifier que l'utilisateur est bien connecté ET qu'il a le rôle "étudiant"
// Seuls les étudiants peuvent ajouter des annonces à leurs favoris
if (!is_logged_in() || !is_etudiant()) {
    // Si la vérification échoue, on renvoie une réponse JSON avec une erreur
    echo json_encode([
        'success' => false,  // Indique que l'opération a échoué
        'error' => 'Vous devez être connecté en tant qu\'étudiant'
    ]);
    exit(); // Arrêter l'exécution du script ici
}

// ============================================================================
// ÉTAPE 2 : Récupération et validation des données envoyées
// ============================================================================

// Récupérer les données JSON envoyées par le JavaScript (fetch)
// file_get_contents('php://input') lit le corps brut de la requête HTTP
// json_decode() transforme le JSON en tableau PHP utilisable
$input = json_decode(file_get_contents('php://input'), true);

// Vérifier que les données obligatoires sont présentes dans la requête
// On a besoin de l'ID de l'annonce et de l'action à effectuer (ajouter ou retirer)
if (!isset($input['annonce_id']) || !isset($input['action'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Données manquantes'
    ]);
    exit(); // Arrêter si les données sont incomplètes
}

// Extraire et sécuriser les données reçues
$annonce_id = intval($input['annonce_id']);  // Convertir en entier pour éviter les injections SQL
$action = $input['action'];                   // L'action demandée : 'add' ou 'remove'
$etudiant_id = get_user_id();                 // Récupérer l'ID de l'étudiant connecté

// ============================================================================
// ÉTAPE 3 : Traitement de l'action demandée
// ============================================================================

try {
    // Bloc try-catch pour gérer les erreurs de base de données
    
    // CAS 1 : Ajouter aux favoris
    if ($action === 'add') {

        // Préparer une requête SQL sécurisée pour ajouter le favori
        // INSERT IGNORE = n'ajoute que si la combinaison étudiant/annonce n'existe pas déjà
        // Cela évite les doublons dans la table favoris
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO favoris (idEtudiant, idAnnonce, dateAjout)
            VALUES (?, ?, NOW())
        ");

        // Exécuter la requête en remplaçant les ? par les vraies valeurs
        // Les ? empêchent les injections SQL (méthode sécurisée)
        $stmt->execute([$etudiant_id, $annonce_id]);

        // Vérifier si le favori a vraiment été ajouté (rowCount > 0 signifie qu'une ligne a été insérée)
        if ($stmt->rowCount() > 0) {
            // Le favori a été ajouté, créer une notification pour le loueur

            // Récupérer les informations de l'annonce et du loueur
            $annonce = get_annonce_by_id($pdo, $annonce_id);

            if ($annonce) {
                // Récupérer les informations de l'étudiant qui a ajouté le favori
                $etudiant = get_user_by_id($pdo, $etudiant_id);

                if ($etudiant) {
                    // Créer le titre et le message de la notification
                    $titre = "Nouvelle mise en favoris de votre annonce";
                    $message = htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) .
                               " a ajouté votre annonce \"" . htmlspecialchars($annonce['titre']) . "\" à ses favoris.";

                    // Préparer les données JSON avec les informations complètes
                    $donneesJson = [
                        'etudiant_id' => $etudiant['id'],
                        'etudiant_prenom' => $etudiant['prenom'],
                        'etudiant_nom' => $etudiant['nom'],
                        'etudiant_email' => $etudiant['email'],
                        'annonce_id' => $annonce['id'],
                        'annonce_titre' => $annonce['titre'],
                        'date_ajout' => date('Y-m-d H:i:s')
                    ];

                    // Créer la notification pour le loueur
                    create_notification(
                        $pdo,
                        $annonce['idLoueur'],  // ID du loueur qui recevra la notification
                        $titre,
                        $message,
                        'favori',              // Type de notification
                        $annonce_id,           // ID de l'annonce concernée
                        null,                  // Pas de candidature
                        $donneesJson           // Données supplémentaires
                    );
                }
            }
        }

        // Renvoyer une réponse JSON de succès
        echo json_encode([
            'success' => true,
            'message' => 'Annonce ajoutée aux favoris',
            'action' => 'added'  // Permet au JavaScript de savoir ce qui s'est passé
        ]);

    } 
    // CAS 2 : Retirer des favoris
    elseif ($action === 'remove') {
        
        // Préparer une requête pour supprimer le favori de la base de données
        // On supprime uniquement la ligne qui correspond à cet étudiant ET cette annonce
        $stmt = $pdo->prepare("
            DELETE FROM favoris 
            WHERE idEtudiant = ? AND idAnnonce = ?
        ");
        
        // Exécuter la suppression
        $stmt->execute([$etudiant_id, $annonce_id]);
        
        // Renvoyer une réponse JSON de succès
        echo json_encode([
            'success' => true,
            'message' => 'Annonce retirée des favoris',
            'action' => 'removed'  // Permet au JavaScript de mettre à jour l'interface
        ]);
        
    } 
    // CAS 3 : Action non reconnue
    else {
        // Si l'action n'est ni 'add' ni 'remove', c'est une erreur
        echo json_encode([
            'success' => false,
            'error' => 'Action invalide'
        ]);
    }
    
} catch (PDOException $e) {
    // Si une erreur de base de données se produit (connexion perdue, erreur SQL, etc.)
    // On capture l'exception et on renvoie un message d'erreur
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Capture toute autre erreur
    echo json_encode([
        'success' => false,
        'error' => 'Erreur : ' . $e->getMessage()
    ]);
}

// ============================================================================
// RÉSUMÉ DU FONCTIONNEMENT :
// ============================================================================
// 1. Vérifier que l'utilisateur est un étudiant connecté
// 2. Récupérer les données JSON envoyées par le navigateur
// 3. Valider que toutes les données nécessaires sont présentes
// 4. Selon l'action demandée ('add' ou 'remove') :
//    - Ajouter ou retirer l'annonce des favoris dans la base de données
// 5. Renvoyer une réponse JSON indiquant si l'opération a réussi
// 6. Gérer les erreurs potentielles à chaque étape
// ============================================================================
?>