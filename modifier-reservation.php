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
$message_erreur = "";
$message_succes = "";

// Vérifier que la réservation appartient à l'utilisateur connecté
$req_verif = $db->prepare("
    SELECT r.*, s.nom_salle, c.heure_debut, c.heure_fin, c.date_expo
    FROM reservations r
    JOIN salles s ON r.salles_id_salle = s.id_salle
    JOIN creneaux c ON r.creneaux_id_creneau = c.id_creneau
    WHERE r.id_reservation = :id AND r.utilisateurs_id_utilisateur = :id_user
");
$req_verif->execute(['id' => $id_reservation, 'id_user' => $id_utilisateur]);
$resa = $req_verif->fetch();

// Si la réservation n'existe pas ou n'appartient pas à l'utilisateur
if (!$resa) {
    header('Location: mes-reservations.php');
    exit;
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_salle   = (int) $_POST['salle'];
    $id_creneau = (int) $_POST['creneau'];
    $nb_places  = (int) $_POST['places'];
    $buffet     = isset($_POST['buffet']) ? 1 : 0;

    try {
        // Vérifier la jauge (sans compter la réservation actuelle)
        $req_jauge = $db->prepare("
            SELECT SUM(nb_personnes) as total 
            FROM reservations 
            WHERE salles_id_salle = :salle 
            AND creneaux_id_creneau = :creneau
            AND id_reservation != :id_resa
        ");
        $req_jauge->execute([
            'salle'    => $id_salle,
            'creneau'  => $id_creneau,
            'id_resa'  => $id_reservation
        ]);
        $jauge = $req_jauge->fetch();
        $places_prises = $jauge['total'] ?? 0;

        if (($places_prises + $nb_places) > 12) {
            $restantes = 12 - $places_prises;
            throw new Exception("Plus assez de places. Il reste $restantes place(s) sur ce créneau.");
        }

        // Mise à jour
        $req_update = $db->prepare("
            UPDATE reservations 
            SET salles_id_salle = :salle,
                creneaux_id_creneau = :creneau,
                nb_personnes = :places,
                participe_buffet = :buffet
            WHERE id_reservation = :id
        ");
        $req_update->execute([
            'salle'   => $id_salle,
            'creneau' => $id_creneau,
            'places'  => $nb_places,
            'buffet'  => $buffet,
            'id'      => $id_reservation
        ]);

        header('Location: mes-reservations.php');
        exit;

    } catch (Exception $e) {
        $message_erreur = $e->getMessage();
    }
}

// Récupérer salles et créneaux pour les selects
$salles   = $db->query("SELECT * FROM salles")->fetchAll();
$creneaux = $db->query("SELECT * FROM creneaux ORDER BY date_expo, heure_debut")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Modifier ma réservation</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="reservation_style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="reservation-section">
  <h1>Modifier ma réservation</h1>

  <?php if (!empty($message_erreur)): ?>
    <div class="alert-error"><?php echo $message_erreur; ?></div>
  <?php endif; ?>

  <form action="modifier-reservation.php?id=<?php echo $id_reservation; ?>" method="POST" class="form-inscription">

    <div class="form-row">
      <div class="form-group">
        <label for="salle">Salle</label>
        <select id="salle" name="salle" required>
          <?php foreach ($salles as $salle): ?>
            <option value="<?php echo $salle['id_salle']; ?>"
              <?php echo ($salle['id_salle'] == $resa['salles_id_salle']) ? 'selected' : ''; ?>>
              Salle <?php echo htmlspecialchars($salle['nom_salle']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="creneau">Créneau horaire</label>
        <select id="creneau" name="creneau" required>
          <?php foreach ($creneaux as $creneau): ?>
            <option value="<?php echo $creneau['id_creneau']; ?>"
              <?php echo ($creneau['id_creneau'] == $resa['creneaux_id_creneau']) ? 'selected' : ''; ?>>
              <?php echo date('d/m', strtotime($creneau['date_expo'])); ?>
              – <?php echo substr($creneau['heure_debut'],0,5); ?>
              → <?php echo substr($creneau['heure_fin'],0,5); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="places">Nombre de places</label>
      <input type="number" id="places" name="places" min="1" max="12"
             value="<?php echo $resa['nb_personnes']; ?>" required>
    </div>

    <div class="form-group-check">
      <input type="checkbox" id="buffet" name="buffet" value="1"
             <?php echo $resa['participe_buffet'] ? 'checked' : ''; ?>>
      <label for="buffet">Je participe au buffet du jeudi à 18h30</label>
    </div>

    <div class="reservation-actions" style="margin-top:28px;">
      <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
      <a href="mes-reservations.php" class="btn-resa">Annuler</a>
    </div>

  </form>
</section>

<?php include 'footer.php'; ?>

</body>
</html>
