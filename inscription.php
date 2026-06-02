<?php
$message_succes = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nom    = htmlspecialchars($_POST['nom']);
  $prenom = htmlspecialchars($_POST['prenom']);
  $message_succes = "Merci $prenom $nom, votre inscription a bien été enregistrée !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Inscription</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="bandeau-inscription">
  <h1>Inscription à l'exposition E-LLUSION</h1>
  <p>18 et 19 Juin 2026 &nbsp;·&nbsp; IUT de Chambéry - Aile C</p>
  <p>28 Avenue du Lac d'Annecy, 73370 Bourget-du-Lac</p>
  <p>Inscription gratuite</p>
</div>

<div class="action-reservations">
  <a href="reservation.php" class="btn-reservations">Voir mes réservations</a>
</div>

<section class="section-formulaire">
  <h2>Formulaire d'inscription</h2>

  <?php if (!empty($message_succes)): ?>
    <div class="alert-success"><?php echo $message_succes; ?></div>
  <?php endif; ?>

  <form action="inscription.php" method="POST" class="form-inscription">

    <div class="form-row">
      <div class="form-group">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" required placeholder="Votre nom">
      </div>
      <div class="form-group">
        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" required placeholder="Votre prénom">
      </div>
    </div>

    <div class="form-group full-width">
      <label for="email">Adresse mail</label>
      <input type="email" id="email" name="email" required placeholder="votre@email.fr">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="tel">Téléphone</label>
        <input type="tel" id="tel" name="tel" placeholder="XX.XX.XX.XX.XX">
      </div>
      <div class="form-group">
        <label for="places">Nombre de places</label>
        <input type="number" id="places" name="places" min="1" max="12" value="1" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="categorie">Qui êtes-vous ?</label>
        <select id="categorie" name="categorie" required onchange="toggleBuffet()">
          <option value="">-- Sélectionner --</option>
          <option value="enseignant">Enseignant·e</option>
          <option value="etudiant_mmi">Étudiant·e MMI 2 ou 3</option>
          <option value="personnel">Personnel USMB</option>
          <option value="pro">Professionnel·le / partenaire</option>
          <option value="visiteur">Visiteur·se extérieur</option>
        </select>
      </div>
      <div class="form-group">
        <label for="salle">Salle</label>
        <select id="salle" name="salle" required onchange="updateCreneaux()">
          <option value="">-- Choisir --</option>
          <option value="001">Salle 001</option>
          <option value="002">Salle 002</option>
          <option value="005">Salle 005</option>
          <option value="021">Salle 021</option>
        </select>
      </div>
    </div>

    <div class="form-group full-width">
      <label for="creneau">Créneau horaire</label>
      <select id="creneau" name="creneau" required>
        <option value="">-- Choisissez d'abord une salle --</option>
      </select>
      <small style="color:var(--text-muted);font-size:12px;margin-top:4px;">⚠ Jauge limitée à 12 personnes par créneau</small>
    </div>

    <div class="form-group-check" id="buffet-group" style="display:none;">
      <input type="checkbox" id="buffet" name="buffet" value="1">
      <label for="buffet">Je participe au buffet du jeudi à 18h30</label>
    </div>

    <div class="form-submit">
      <button type="submit" class="btn btn-primary" style="padding:13px 48px;font-size:15px;">
        Envoyer et Valider
      </button>
    </div>

  </form>
</section>

<?php include 'footer.php'; ?>

<script>
  const creneauxJeudi   = ["Jeudi 18 juin – 15h00","Jeudi 18 juin – 15h30","Jeudi 18 juin – 16h00","Jeudi 18 juin – 16h30","Jeudi 18 juin – 17h00","Jeudi 18 juin – 17h30","Jeudi 18 juin – 18h00","Jeudi 18 juin – 19h00","Jeudi 18 juin – 19h30","Jeudi 18 juin – 20h00"];
  const creneauxVendredi = ["Vendredi 19 juin – 9h30","Vendredi 19 juin – 10h00","Vendredi 19 juin – 10h30","Vendredi 19 juin – 11h00"];

  function updateCreneaux() {
    const salle  = document.getElementById('salle').value;
    const select = document.getElementById('creneau');
    select.innerHTML = '';
    if (!salle) { select.innerHTML = '<option>-- Choisissez d\'abord une salle --</option>'; return; }
    select.innerHTML = '<option value="">-- Sélectionner un créneau --</option>';
    [...creneauxJeudi, ...creneauxVendredi].forEach(c => {
      const opt = document.createElement('option');
      opt.value = c; opt.textContent = c;
      select.appendChild(opt);
    });
  }

  function toggleBuffet() {
    const cat   = document.getElementById('categorie').value;
    const group = document.getElementById('buffet-group');
    group.style.display = (cat === 'etudiant_mmi' || cat === '') ? 'none' : 'flex';
  }
</script>

</body>
</html>