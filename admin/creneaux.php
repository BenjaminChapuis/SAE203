<?php
session_start();
require_once '../connexion.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$req = $db->query("
    SELECT s.id_salle, s.nom_salle, s.jauge_max,
           c.id_creneau, c.date_expo, c.heure_debut, c.heure_fin,
           COALESCE(SUM(r.nb_personnes), 0) as places_prises,
           s.jauge_max - COALESCE(SUM(r.nb_personnes), 0) as places_restantes
    FROM salles s
    CROSS JOIN creneaux c
    LEFT JOIN reservations r ON r.salles_id_salle = s.id_salle AND r.creneaux_id_creneau = c.id_creneau
    GROUP BY s.id_salle, c.id_creneau
    ORDER BY c.date_expo, c.heure_debut, s.nom_salle
");
$donnees = $req->fetchAll();

$par_date = [];
foreach ($donnees as $d) {
    $date = $d['date_expo'];
    $creneau = substr($d['heure_debut'],0,5) . ' → ' . substr($d['heure_fin'],0,5);
    $par_date[$date][$creneau][] = $d;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Admin Créneaux</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../admin_style.css">
</head>
<body>
<?php include '../header.php'; ?>
<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <p class="sidebar-title">Admin</p>
    <a href="dashboard.php" class="sidebar-link">📊 Tableau de bord</a>
    <a href="reservations.php" class="sidebar-link">📋 Réservations</a>
    <a href="creneaux.php" class="sidebar-link active">🕐 Créneaux</a>
    <a href="creer-reservation.php" class="sidebar-link">➕ Créer réservation</a>
    <a href="../logout.php" class="sidebar-logout">← Déconnexion</a>
  </aside>
  <main class="admin-content">
    <h1 class="admin-title">Disponibilité des créneaux</h1>
    <div class="admin-section">
      <div class="legende">
        <div class="legende-item"><div class="legende-dot dispo"></div> Disponible (4+ places)</div>
        <div class="legende-item"><div class="legende-dot warn"></div> Quasi complet (1-3 places)</div>
        <div class="legende-item"><div class="legende-dot full"></div> Complet</div>
      </div>
      <?php foreach ($par_date as $date => $creneaux): ?>
        <h2 class="date-titre"><?php echo ucfirst(strftime('%A %d %B %Y', strtotime($date))); ?></h2>
        <div class="creneau-grid">
          <div class="creneau-header">Horaire</div>
          <div class="creneau-header">Salle 001</div>
          <div class="creneau-header">Salle 002</div>
          <div class="creneau-header">Salle 005</div>
          <div class="creneau-header">Salle 021</div>
        </div>
        <?php foreach ($creneaux as $horaire => $salles): ?>
          <div class="creneau-grid">
            <div class="creneau-horaire"><?php echo $horaire; ?></div>
            <?php foreach ($salles as $s): ?>
              <?php $restantes = $s['places_restantes']; $classe = $restantes <= 0 ? 'full' : ($restantes <= 3 ? 'warn' : 'dispo'); ?>
              <div class="creneau-cell <?php echo $classe; ?>">
                <span class="places"><?php echo max(0, $restantes); ?></span>
                <span class="label">place<?php echo $restantes > 1 ? 's' : ''; ?> dispo</span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  </main>
</div>
<?php include '../footer.php'; ?>
</body>
</html>