<?php
session_start();
require_once '../connexion.php';

// Vérification admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// ── STATS GÉNÉRALES ──────────────────────────────
$total_reservations = $db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$total_inscrits     = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'visiteur'")->fetchColumn();
$total_places       = $db->query("SELECT SUM(nb_personnes) FROM reservations")->fetchColumn() ?? 0;
$total_buffet       = $db->query("SELECT COUNT(*) FROM reservations WHERE participe_buffet = 1")->fetchColumn();

// ── PLACES DISPO PAR SALLE ET CRÉNEAU ────────────
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
  <style>
    /* ── ADMIN SPECIFIQUE ── */
    .admin-wrapper {
      display: grid;
      grid-template-columns: 220px 1fr;
      min-height: calc(100vh - 57px);
    }

    /* Sidebar */
    .admin-sidebar {
      background: var(--black);
      padding: 32px 20px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .sidebar-title {
      font-family: var(--font-pixel);
      font-size: 13px;
      color: #666;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-bottom: 12px;
    }

    .sidebar-link {
      display: block;
      padding: 10px 16px;
      border-radius: var(--radius-sm);
      color: #aaa;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s;
    }

    .sidebar-link:hover,
    .sidebar-link.active {
      background: var(--teal-dark);
      color: var(--white);
    }

    .sidebar-logout {
      margin-top: auto;
      display: block;
      padding: 10px 16px;
      border-radius: var(--radius-sm);
      color: #c0392b;
      font-size: 14px;
      transition: background 0.2s;
    }

    .sidebar-logout:hover { background: rgba(192,57,43,0.15); }

    /* Contenu principal */
    .admin-content {
      padding: 40px 48px;
      background: var(--grey-soft);
    }

    .admin-title {
      font-family: var(--font-pixel);
      font-size: 28px;
      letter-spacing: 2px;
      margin-bottom: 32px;
    }

    /* Cards stats */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 40px;
    }

    .stat-card {
      background: var(--white);
      border-radius: var(--radius-md);
      padding: 24px;
      text-align: center;
      border-top: 4px solid var(--teal-dark);
    }

    .stat-card .stat-number {
      font-family: var(--font-pixel);
      font-size: 42px;
      color: var(--teal-dark);
      line-height: 1;
      margin-bottom: 8px;
    }

    .stat-card .stat-label {
      font-size: 13px;
      color: var(--text-muted);
      font-weight: 500;
    }

    /* Section */
    .admin-section {
      background: var(--white);
      border-radius: var(--radius-md);
      padding: 28px;
      margin-bottom: 28px;
    }

    .admin-section h2 {
      font-family: var(--font-pixel);
      font-size: 18px;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid var(--teal-light);
    }

    /* Tableau */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    th {
      background: var(--teal-light);
      padding: 10px 14px;
      text-align: left;
      font-weight: 700;
      font-size: 12px;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      color: var(--teal-darker);
    }

    td {
      padding: 10px 14px;
      border-bottom: 1px solid var(--grey-mid);
      color: #333;
    }

    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--grey-soft); }

    /* Badge dispo */
    .badge-dispo {
      display: inline-block;
      padding: 3px 10px;
      border-radius: var(--radius-pill);
      font-size: 12px;
      font-weight: 700;
    }

    .badge-dispo.ok    { background: #d4edda; color: #155724; }
    .badge-dispo.warn  { background: #fff3cd; color: #856404; }
    .badge-dispo.full  { background: #f8d7da; color: #721c24; }

    @media (max-width: 900px) {
      .admin-wrapper { grid-template-columns: 1fr; }
      .admin-sidebar { flex-direction: row; flex-wrap: wrap; padding: 16px; }
      .stats-grid    { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>

<?php include '../header.php'; ?>

<div class="admin-wrapper">

  <!-- SIDEBAR -->
  <aside class="admin-sidebar">
    <p class="sidebar-title">Admin</p>
    <a href="dashboard.php" class="sidebar-link active">📊 Tableau de bord</a>
    <a href="reservations.php" class="sidebar-link">📋 Réservations</a>
    <a href="creneaux.php" class="sidebar-link">🕐 Créneaux</a>
    <a href="../logout.php" class="sidebar-logout">← Déconnexion</a>
  </aside>

  <!-- CONTENU -->
  <main class="admin-content">
    <h1 class="admin-title">Tableau de bord</h1>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number"><?php echo $total_reservations; ?></div>
        <div class="stat-label">Réservations</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $total_inscrits; ?></div>
        <div class="stat-label">Inscrits</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $total_places; ?></div>
        <div class="stat-label">Places réservées</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $total_buffet; ?></div>
        <div class="stat-label">Buffet jeudi</div>
      </div>
    </div>

    <!-- PLACES DISPO -->
    <div class="admin-section">
      <h2>Places disponibles par salle et créneau</h2>
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Horaire</th>
            <th>Salle</th>
            <th>Places prises</th>
            <th>Places restantes</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dispos as $d): ?>
            <?php
              $restantes = $d['places_restantes'];
              if ($restantes == 0)       $classe = 'full';
              elseif ($restantes <= 3)   $classe = 'warn';
              else                       $classe = 'ok';
            ?>
            <tr>
              <td><?php echo date('d/m/Y', strtotime($d['date_expo'])); ?></td>
              <td><?php echo substr($d['heure_debut'],0,5); ?> → <?php echo substr($d['heure_fin'],0,5); ?></td>
              <td>Salle <?php echo htmlspecialchars($d['nom_salle']); ?></td>
              <td><?php echo $d['places_prises']; ?> / <?php echo $d['jauge_max']; ?></td>
              <td><?php echo $restantes; ?></td>
              <td>
                <span class="badge-dispo <?php echo $classe; ?>">
                  <?php
                    if ($classe == 'full') echo 'Complet';
                    elseif ($classe == 'warn') echo 'Quasi complet';
                    else echo 'Disponible';
                  ?>
                </span>
              </td>
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
