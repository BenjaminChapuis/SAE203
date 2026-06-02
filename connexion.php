<?php
// 1. Configuration des identifiants de connexion (Spécial XAMPP)
define('HOST', 'localhost');
define('DB_NAME', 'exposition_mmi'); // Le nom exact de ta base phpMyAdmin
define('USER', 'root');              // Identifiant par défaut sous XAMPP
define('PASS', '');                  // TOUJOURS VIDE sous XAMPP Windows !

try {
    // 2. Tentative de connexion avec PDO et configuration UTF-8
    $db = new PDO("mysql:host=" . HOST . ";dbname=" . DB_NAME . ";charset=utf8", USER, PASS);
    
    // 3. Activation des alertes en cas d'erreur SQL
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Message de test (on le supprimera dès que ça marchera)
    // echo "Connexion réussie à la base de données de l'exposition !";

} catch (PDOException $e) {
    // Si un truc plante, XAMPP nous dira exactement quoi via ce message
    echo "Erreur de connexion : " . $e->getMessage();
    die();
}