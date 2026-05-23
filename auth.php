<?php
// =============================================
// VÉRIFICATION DE SESSION — PROTECTION DES PAGES
// =============================================
session_start();

if (!isset($_SESSION['utilisateur_connecte']) || $_SESSION['utilisateur_connecte'] !== true) {
    header("Location: connexion.php");
    exit;
}
