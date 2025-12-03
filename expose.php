<?php
$conn = mysqli_connect("localhost", "user", "password", "ma_base");

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

$result = mysqli_query($conn, "SELECT * FROM users");
mysqli_close($conn);
?>




<?php
$conn = new mysqli("localhost", "user", "password", "ma_base");

if ($conn->connect_error) {
    die("Erreur : " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM users");
$conn->close();
?>



<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ma_base", "user", "password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => 1]);
    $data = $stmt->fetch();
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

