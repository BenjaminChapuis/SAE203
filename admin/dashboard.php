<?php
session_start();
require_once '../connexion.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$total_reservations = $db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$total_inscrits     = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'visiteur'")->fetchColumn();
$total_places       = $db->query("SELECT SUM(nb_personnes) FROM reservations")->fetchColumn() ?? 0;
$total_buffet       = $db->query("SELECT COUNT(*) FROM reservations WHERE participe_buffet = 1")->fetchColumn();

$req_dispo = $db->query("
    SELECT s.nom_salle, c.date_expo, c.heure_debut, c.heure_fin,
           s.jauge_max,
           COALESCE(SUM(r.nb_personnes), 0) as places_prises,
           s.jauge_max - COALESCE(SUM(r.nb_personnes), 0) as places_restantes
    FROM salles s
    CROSS JOIN creneaux c
    LEFT JOIN reservations r ON r.salles_id_salle = s.id_salle AND r.creneaux_id_creneau = c.id_creneau
    GROUP BY s.id_salle, c.id_creneau
    ORDER BY c.date_expo, c.heure_debut, s.nom_salle
");
$dispos = $req_dispo->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Admin</title>
  <link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="../admin_style.css">
</head>
<body>
<?php include '../header.php'; ?>
<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <p class="sidebar-title">Admin</p>
    <a href="dashboard.php" class="sidebar-link active">📊 Tableau de bord</a>
    <a href="reservations.php" class="sidebar-link">📋 Réservations</a>
    <a href="creneaux.php" class="sidebar-link">🕐 Créneaux</a>
    <a href="creer-reservation.php" class="sidebar-link">➕ Créer réservation</a>
    <a href="../logout.php" class="sidebar-logout">← Déconnexion</a>
  </aside>
  <main class="admin-content">
    <h1 class="admin-title">Tableau de bord</h1>
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-number"><?php echo $total_reservations; ?></div><div class="stat-label">Réservations</div></div>
      <div class="stat-card"><div class="stat-number"><?php echo $total_inscrits; ?></div><div class="stat-label">Inscrits</div></div>
      <div class="stat-card"><div class="stat-number"><?php echo $total_places; ?></div><div class="stat-label">Places réservées</div></div>
      <div class="stat-card"><div class="stat-number"><?php echo $total_buffet; ?></div><div class="stat-label">Buffet jeudi</div></div>
    </div>
    <div class="admin-section">
      <h2>Places disponibles par salle et créneau</h2>
      <table>
        <thead>
          <tr><th>Date</th><th>Horaire</th><th>Salle</th><th>Places prises</th><th>Places restantes</th><th>Statut</th></tr>
        </thead>
        <tbody>
          <?php foreach ($dispos as $d): ?>
            <?php $restantes = $d['places_restantes']; $classe = $restantes == 0 ? 'full' : ($restantes <= 3 ? 'warn' : 'ok'); ?>
            <tr>
              <td><?php echo date('d/m/Y', strtotime($d['date_expo'])); ?></td>
              <td><?php echo substr($d['heure_debut'],0,5); ?> → <?php echo substr($d['heure_fin'],0,5); ?></td>
              <td>Salle <?php echo htmlspecialchars($d['nom_salle']); ?></td>
              <td><?php echo $d['places_prises']; ?> / <?php echo $d['jauge_max']; ?></td>
              <td><?php echo $restantes; ?></td>
              <td><span class="badge-dispo <?php echo $classe; ?>"><?php echo $classe == 'full' ? 'Complet' : ($classe == 'warn' ? 'Quasi complet' : 'Disponible'); ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php include '../footer.php'; ?>
</body>
</html>