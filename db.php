<?php
// =============================================
// CONNEXION À LA BASE DE DONNÉES
// =============================================

$host     = 'sql210.infinityfree.com';
$dbname   = 'if0_41992281_XXX';
$user     = 'if0_41992281';
$password = 'WALxqqYFL6';

$pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $user,
    $password
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
?>
