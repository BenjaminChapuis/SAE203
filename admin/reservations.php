<?php
session_start();
require_once '../connexion.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// ── SUPPRESSION ──────────────────────────────────
if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    $db->prepare("DELETE FROM reservations WHERE id_reservation = ?")->execute([$id]);
    header('Location: reservations.php');
    exit;
}

// ── FILTRAGE ─────────────────────────────────────
$search = htmlspecialchars($_GET['search'] ?? '');
$filtre_salle = (int) ($_GET['salle'] ?? 0);

$sql = "
    SELECT r.id_reservation, r.nb_personnes, r.participe_buffet, r.date_reservation,
           u.nom, u.prenom, u.email, u.categorie,
           s.nom_salle,
           c.date_expo, c.heure_debut, c.heure_fin
    FROM reservations r
    JOIN utilisateurs u ON r.utilisateurs_id_utilisateur = u.id_utilisateur
    JOIN salles s ON r.salles_id_salle = s.id_salle
    JOIN creneaux c ON r.creneaux_id_creneau = c.id_creneau
    WHERE 1=1
";

$params = [];

if (!empty($search)) {
    $sql .= " AND (u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

if ($filtre_salle > 0) {
    $sql .= " AND s.id_salle = :salle";
    $params['salle'] = $filtre_salle;
}

$sql .= " ORDER BY c.date_expo, c.heure_debut";

$req = $db->prepare($sql);
$req->execute($params);
$reservations = $req->fetchAll();
$salles = $db->query("SELECT * FROM salles")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Admin Réservations</title>
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
    .filtres { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
    .filtres input, .filtres select { padding: 9px 16px; border: 2px solid var(--grey-mid); border-radius: var(--radius-sm); font-size: 14px; font-family: var(--font-body); outline: none; background: var(--grey-soft); }
    .filtres input:focus, .filtres select:focus { border-color: var(--teal-dark); }
    .filtres button { padding: 9px 20px; background: var(--teal-dark); color: var(--white); border: none; border-radius: var(--radius-sm); cursor: pointer; font-size: 14px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { background: var(--teal-light); padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--teal-darker); font-weight: 700; }
    td { padding: 10px 12px; border-bottom: 1px solid var(--grey-mid); }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--grey-soft); }
    .btn-sm { padding: 5px 12px; border-radius: var(--radius-pill); font-size: 12px; font-weight: 600; cursor: pointer; border: none; }
    .btn-sm-edit   { background: var(--teal-light); color: var(--teal-darker); }
    .btn-sm-delete { background: #fde8e8; color: #c0392b; }
    .count-result  { font-size: 13px; color: var(--text-muted); margin-bottom: 14px; }
  </style>
</head>
<body>

<?php include '../header.php'; ?>

<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <p class="sidebar-title">Admin</p>
    <a href="dashboard.php" class="sidebar-link">📊 Tableau de bord</a>
    <a href="reservations.php" class="sidebar-link active">📋 Réservations</a>
    <a href="creneaux.php" class="sidebar-link">🕐 Créneaux</a>
    <a href="../logout.php" class="sidebar-logout">← Déconnexion</a>
  </aside>

  <main class="admin-content">
    <h1 class="admin-title">Réservations</h1>

    <div class="admin-section">
      <h2>Filtrer les réservations</h2>
      <form method="GET" action="reservations.php">
        <div class="filtres">
          <input type="text" name="search" placeholder="Nom, prénom ou email..."
                 value="<?php echo $search; ?>">
          <select name="salle">
            <option value="0">Toutes les salles</option>
            <?php foreach ($salles as $salle): ?>
              <option value="<?php echo $salle['id_salle']; ?>"
                <?php echo ($filtre_salle == $salle['id_salle']) ? 'selected' : ''; ?>>
                Salle <?php echo htmlspecialchars($salle['nom_salle']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Rechercher</button>
          <a href="reservations.php" style="padding:9px 16px;color:var(--text-muted);font-size:14px;">
            Réinitialiser
          </a>
        </div>
      </form>

      <p class="count-result"><?php echo count($reservations); ?> réservation(s) trouvée(s)</p>

      <?php if (empty($reservations)): ?>
        <p style="color:var(--text-muted);text-align:center;padding:24px;">Aucune réservation trouvée.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Nom</th>
              <th>Email</th>
              <th>Catégorie</th>
              <th>Salle</th>
              <th>Date</th>
              <th>Horaire</th>
              <th>Places</th>
              <th>Buffet</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reservations as $r): ?>
              <tr>
                <td><?php echo $r['id_reservation']; ?></td>
                <td><?php echo htmlspecialchars($r['prenom'] . ' ' . $r['nom']); ?></td>
                <td><?php echo htmlspecialchars($r['email']); ?></td>
                <td><?php echo htmlspecialchars($r['categorie']); ?></td>
                <td>Salle <?php echo htmlspecialchars($r['nom_salle']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($r['date_expo'])); ?></td>
                <td><?php echo substr($r['heure_debut'],0,5) . ' → ' . substr($r['heure_fin'],0,5); ?></td>
                <td><?php echo $r['nb_personnes']; ?></td>
                <td><?php echo $r['participe_buffet'] ? '✅' : '—'; ?></td>
                <td style="display:flex;gap:6px;">
                  <a href="modifier-admin.php?id=<?php echo $r['id_reservation']; ?>" class="btn-sm btn-sm-edit">Modifier</a>
                  <a href="reservations.php?supprimer=<?php echo $r['id_reservation']; ?>"
                     class="btn-sm btn-sm-delete"
                     onclick="return confirm('Supprimer cette réservation ?')">Supprimer</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php include '../footer.php'; ?>

</body>
</html>
