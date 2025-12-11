<?php
// includes/db.php - Connexion à la base de données MySQL
// Ce fichier sert à centraliser la connexion à la base de données pour l'ensemble du projet.

// Configuration de la base de données
$host = 'localhost';  // L'adresse du serveur MySQL, ici 'localhost' car la DB est sur la même machine.
$dbname = 'dormquest'; // Nom de la base de données à laquelle on veut se connecter.
$username = 'root';    // Nom d'utilisateur MySQL, par défaut 'root' sur XAMPP.
$password = '';        // Mot de passe MySQL, vide par défaut sur XAMPP.

// Bloc try/catch pour gérer les exceptions lors de la connexion
try {
    // Création de la connexion PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", // DSN : indique le type de DB, l'hôte, le nom et le charset
        $username,  // Nom d'utilisateur pour se connecter à MySQL
        $password,  // Mot de passe correspondant
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Mode d'erreur : lance une exception en cas de problème (PDo lance une excpetion silencieuse)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Les résultats seront récupérés sous forme de tableau associatif
            PDO::ATTR_EMULATE_PREPARES => false // Désactive l'émulation des requêtes préparées pour utiliser les vraies préparations MySQL
        ]
    );
    
} catch(PDOException $e) {
    // Si une erreur survient lors de la connexion, on la récupère et on stoppe le script
    die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
