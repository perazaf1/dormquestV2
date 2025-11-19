<?php
// includes/db.php - Connexion à la base de données MySQL

// Configuration de la base de données
$host = 'localhost';
$dbname = 'dormquest'; // Nom de ta base de données
$username = 'root';    // Utilisateur MySQL par défaut sur XAMPP
$password = '';        // Mot de passe vide par défaut sur XAMPP

try {
    // Création de la connexion PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
} catch(PDOException $e) {
    // En production, ne jamais afficher les détails de l'erreur
    // Ici c'est pour le développement
    die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
}
?>