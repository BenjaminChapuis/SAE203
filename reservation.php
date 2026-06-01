<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Ma réservation</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="reservation-section">
  <h1>RESERVATION</h1>

  <div class="reservation-recap">
    <p>Nom : ...</p>
    <p>Salle : ...</p>
    <p>Date : ...</p>
    <p>Horaire : ...</p>
    <p>Nombre de personne : ...</p>
  </div>

  <div class="reservation-actions">
    <a href="modifier.php" class="btn-resa">modifier ma reservation</a>
    <a href="supprimer.php" class="btn-resa danger">supprimer ma reservation</a>
  </div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>