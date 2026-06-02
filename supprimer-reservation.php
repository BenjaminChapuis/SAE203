<?php
session_start();
require_once 'connexion.php';

// Si pas connecté → login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$id_reservation = (int) $_GET['id'];
$id_utilisateur = $_SESSION['id'];

// Vérification que la réservation appartient bien à l'utilisateur connecté
// (sécurité importante : évite qu'un utilisateur supprime la réservation d'un autre)
$req_verif = $db->prepare("SELECT id_reservation FROM reservations WHERE id_reservation = :id AND utilisateurs_id_utilisateur = :id_user");
$req_verif->execute([
    'id'      => $id_reservation,
    'id_user' => $id_utilisateur
]);
$resa = $req_verif->fetch();

if (!$resa) {
    // La réservation n'existe pas ou n'appartient pas à cet utilisateur
    header('Location: mes-reservations.php');
    exit;
}

// Suppression
$req_delete = $db->prepare("DELETE FROM reservations WHERE id_reservation = :id");
$req_delete->execute(['id' => $id_reservation]);

// Redirection vers mes réservations
header('Location: mes-reservations.php');
exit;
?>
