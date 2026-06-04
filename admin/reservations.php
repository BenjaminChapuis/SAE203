<?php
session_start();
require_once '../connexion.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    $db->prepare("DELETE FROM reservations WHERE id_reservation = ?")->execute([$id]);
    header('Location: reservations.php');
    exit;
}

$search = htmlspecialchars($_GET['search'] ?? '');
$filtre_salle = (int) ($_GET['salle'] ?? 0);

$sql = "
    SELECT r.id_reservation, r.nb_personnes, r.participe_buffet, r.date_reservation,
           u.nom, u.prenom, u.email, u.categorie,
           s.nom_salle, c.date_expo, c.heure_debut, c.heure_fin
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
<link rel="stylesheet" href="../admin_style.css">
</head>
<body>
<?php include '../header.php'; ?>
<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <p class="sidebar-title">Admin</p>
    <a href="dashboard.php" class="sidebar-link">📊 Tableau de bord</a>
    <a href="reservations.php" class="sidebar-link active">📋 Réservations</a>
    <a href="creneaux.php" class="sidebar-link">🕐 Créneaux</a>
    <a href="creer-reservation.php" class="sidebar-link">➕ Créer réservation</a>
    <a href="../logout.php" class="sidebar-logout">← Déconnexion</a>
  </aside>
  <main class="admin-content">
    <h1 class="admin-title">Réservations</h1>
    <div class="admin-section">
      <h2>Filtrer les réservations</h2>
      <form method="GET" action="reservations.php">
        <div class="filtres">
          <input type="text" name="search" placeholder="Nom, prénom ou email..." value="<?php echo $search; ?>">
          <select name="salle">
            <option value="0">Toutes les salles</option>
            <?php foreach ($salles as $salle): ?>
              <option value="<?php echo $salle['id_salle']; ?>" <?php echo ($filtre_salle == $salle['id_salle']) ? 'selected' : ''; ?>>
                Salle <?php echo htmlspecialchars($salle['nom_salle']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Rechercher</button>
          <a href="reservations.php" style="padding:9px 16px;color:#6a9a94;font-size:14px;">Réinitialiser</a>
        </div>
      </form>
      <p class="count-result"><?php echo count($reservations); ?> réservation(s) trouvée(s)</p>
      <?php if (empty($reservations)): ?>
        <p style="color:#6a9a94;text-align:center;padding:24px;">Aucune réservation trouvée.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>#</th><th>Nom</th><th>Email</th><th>Catégorie</th><th>Salle</th><th>Date</th><th>Horaire</th><th>Places</th><th>Buffet</th><th>Actions</th></tr>
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
                  <a href="reservations.php?supprimer=<?php echo $r['id_reservation']; ?>" class="btn-sm btn-sm-delete" onclick="return confirm('Supprimer cette réservation ?')">Supprimer</a>
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