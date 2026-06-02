<?php
session_start();
require_once '../connexion.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Récupérer toutes les salles et créneaux avec places dispo
$req = $db->query("
    SELECT s.id_salle, s.nom_salle, s.jauge_max,
           c.id_creneau, c.date_expo, c.heure_debut, c.heure_fin,
           COALESCE(SUM(r.nb_personnes), 0) as places_prises,
           s.jauge_max - COALESCE(SUM(r.nb_personnes), 0) as places_restantes
    FROM salles s
    CROSS JOIN creneaux c
    LEFT JOIN reservations r ON r.salles_id_salle = s.id_salle 
                             AND r.creneaux_id_creneau = c.id_creneau
    GROUP BY s.id_salle, c.id_creneau
    ORDER BY c.date_expo, c.heure_debut, s.nom_salle
");
$donnees = $req->fetchAll();

// Organiser par date puis créneau
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
  <style>
    .admin-wrapper { display: grid; grid-template-columns: 220px 1fr; min-height: calc(100vh - 57px); }
    .admin-sidebar { background: var(--black); padding: 32px 20px; display: flex; flex-direction: column; gap: 8px; }
    .sidebar-title { font-family: var(--font-pixel); font-size: 13px; color: #666; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px; }
    .sidebar-link  { display: block; padding: 10px 16px; border-radius: var(--radius-sm); color: #aaa; font-size: 14px; font-weight: 500; transition: all 0.2s; }
    .sidebar-link:hover, .sidebar-link.active { background: var(--teal-dark); color: var(--white); }
    .sidebar-logout { margin-top: auto; display: block; padding: 10px 16px; border-radius: var(--radius-sm); color: #c0392b; font-size: 14px; }
    .admin-content { padding: 40px 48px; background: var(--grey-soft); }
    .admin-title   { font-family: var(--font-pixel); font-size: 28px; letter-spacing: 2px; margin-bottom: 28px; }
    .admin-section { background: var(--white); border-radius: var(--radius-md); padding: 28px; margin-bottom: 28px; }
    .admin-section h2 { font-family: var(--font-pixel); font-size: 18px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid var(--teal-light); }
    .date-titre { font-family: var(--font-pixel); font-size: 16px; color: var(--teal-darker); margin: 24px 0 12px; letter-spacing: 1px; }

    /* Grille créneaux */
    .creneau-grid {
      display: grid;
      grid-template-columns: 140px repeat(4, 1fr);
      gap: 8px;
      margin-bottom: 8px;
      align-items: center;
    }

    .creneau-header {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--text-muted);
      padding: 8px;
      text-align: center;
    }

    .creneau-horaire {
      font-size: 13px;
      font-weight: 600;
      color: var(--black);
      padding: 8px;
    }

    .creneau-cell {
      border-radius: var(--radius-sm);
      padding: 12px 8px;
      text-align: center;
      font-size: 13px;
      font-weight: 600;
    }

    .creneau-cell.dispo  { background: #d4edda; color: #155724; }
    .creneau-cell.warn   { background: #fff3cd; color: #856404; }
    .creneau-cell.full   { background: #f8d7da; color: #721c24; }

    .creneau-cell .places {
      font-size: 18px;
      font-weight: 700;
      display: block;
      margin-bottom: 2px;
    }

    .creneau-cell .label {
      font-size: 11px;
      font-weight: 400;
      opacity: 0.8;
    }

    .legende {
      display: flex;
      gap: 20px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }

    .legende-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
    }

    .legende-dot {
      width: 14px;
      height: 14px;
      border-radius: 50%;
    }

    .legende-dot.dispo { background: #28a745; }
    .legende-dot.warn  { background: #ffc107; }
    .legende-dot.full  { background: #dc3545; }
  </style>
</head>
<body>

<?php include '../header.php'; ?>

<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <p class="sidebar-title">Admin</p>
    <a href="dashboard.php" class="sidebar-link">📊 Tableau de bord</a>
    <a href="reservations.php" class="sidebar-link">📋 Réservations</a>
    <a href="creneaux.php" class="sidebar-link active">🕐 Créneaux</a>
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
        <h2 class="date-titre">
          <?php echo ucfirst(strftime('%A %d %B %Y', strtotime($date))); ?>
        </h2>

        <!-- En-tête salles -->
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
              <?php
                $restantes = $s['places_restantes'];
                if ($restantes <= 0)     $classe = 'full';
                elseif ($restantes <= 3) $classe = 'warn';
                else                     $classe = 'dispo';
              ?>
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
