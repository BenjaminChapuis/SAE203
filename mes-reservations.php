<?php
session_start();
require_once 'connexion.php';

// Si pas connecté → login
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// Si admin → dashboard admin
if ($_SESSION['role'] == 'admin') {
    header('Location: admin/dashboard.php');
    exit;
}

$id_utilisateur = $_SESSION['id'];

// Récupérer les réservations de l'utilisateur avec les infos liées
$req = $db->prepare("
    SELECT r.id_reservation, r.nb_personnes, r.participe_buffet,
           s.nom_salle,
           c.date_expo, c.heure_debut, c.heure_fin
    FROM reservations r
    JOIN salles s ON r.salles_id_salle = s.id_salle
    JOIN creneaux c ON r.creneaux_id_creneau = c.id_creneau
    WHERE r.utilisateurs_id_utilisateur = :id
    ORDER BY c.date_expo, c.heure_debut
");
$req->execute(['id' => $id_utilisateur]);
$reservations = $req->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Mes réservations</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="reservation_style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="reservation-section">
  <h1>Mes réservations</h1>
  <p style="color:var(--text-muted);margin-bottom:32px;">
    Bonjour <strong><?php echo htmlspecialchars($_SESSION['nom']); ?></strong> !
    &nbsp;·&nbsp; <a href="logout.php" style="color:var(--red-dot);">Se déconnecter</a>
  </p>

  <?php if (empty($reservations)): ?>
    <div class="reservation-recap" style="text-align:center;">
      <p>Vous n'avez pas encore de réservation.</p>
      <a href="inscription.php" class="btn btn-primary" style="margin-top:20px;display:inline-block;">
        S'inscrire à l'exposition
      </a>
    </div>

  <?php else: ?>
    <?php foreach ($reservations as $resa): ?>
      <div class="reservation-recap" style="margin-bottom:20px;">
        <p><strong>Salle :</strong> <?php echo htmlspecialchars($resa['nom_salle']); ?></p>
        <p><strong>Date :</strong> <?php echo date('l d F Y', strtotime($resa['date_expo'])); ?></p>
        <p><strong>Horaire :</strong> <?php echo substr($resa['heure_debut'],0,5); ?> → <?php echo substr($resa['heure_fin'],0,5); ?></p>
        <p><strong>Nombre de places :</strong> <?php echo $resa['nb_personnes']; ?></p>
        <p><strong>Buffet :</strong> <?php echo $resa['participe_buffet'] ? 'Oui' : 'Non'; ?></p>
        <div class="reservation-actions" style="margin-top:16px;">
          <a href="modifier-reservation.php?id=<?php echo $resa['id_reservation']; ?>" class="btn-resa">
            Modifier
          </a>
          <a href="supprimer-reservation.php?id=<?php echo $resa['id_reservation']; ?>"
             class="btn-resa danger"
             onclick="return confirm('Voulez-vous vraiment supprimer cette réservation ?')">
            Supprimer
          </a>
        </div>
      </div>
    <?php endforeach; ?>

    <div style="margin-top:28px;">
      <a href="inscription.php" class="btn btn-outline">+ Ajouter une réservation</a>
    </div>
  <?php endif; ?>

</section>

<?php include 'footer.php'; ?>

</body>
</html>
