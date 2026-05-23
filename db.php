<?php
$host     = getenv('PGHOST') ?: 'localhost';
$dbname   = getenv('PGDATABASE') ?: 'gestion_notes';
$user     = getenv('PGUSER') ?: 'root';
$password = getenv('PGPASSWORD') ?: '';
$port     = getenv('PGPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}
?>
