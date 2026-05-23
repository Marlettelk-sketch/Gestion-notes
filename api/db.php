<?php
// =============================================
// CONNEXION À LA BASE DE DONNÉES (Aiven + Vercel)
// =============================================

$host     = getenv('DB_HOST');
$port     = getenv('DB_PORT') ?: '3306';
$dbname   = getenv('DB_NAME');
$user     = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

// Vérification que les variables d'environnement sont bien définies
if (!$host || !$dbname || !$user || !$password) {
    die("Erreur : variables d'environnement de la base de données manquantes. Vérifiez vos paramètres Vercel.");
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Nécessaire pour Aiven (connexion SSL obligatoire)
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]);

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>