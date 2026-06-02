<?php
session_start();
require_once 'connexion.php';

$message_succes = "";
$message_erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom       = htmlspecialchars($_POST['nom']);
    $prenom    = htmlspecialchars($_POST['prenom']);
    $email     = htmlspecialchars($_POST['email']);
    $categorie = $_POST['categorie'];
    $places    = (int) $_POST['places'];
    $id_salle  = (int) $_POST['salle'];
    $id_creneau = (int) $_POST['creneau'];
    $buffet    = isset($_POST['buffet']) ? 1 : 0;

    try {
        // Vérifier si l'utilisateur existe déjà
        $req_verif = $db->prepare("SELECT id_utilisateur, mot_de_passe FROM utilisateurs WHERE email = :email");
        $req_verif->execute(['email' => $email]);
        $utilisateur_existant = $req_verif->fetch();

        if ($utilisateur_existant) {
            // Utilisateur existant → on récupère son ID
            $id_utilisateur = $utilisateur_existant['id_utilisateur'];
        } else {
            // Nouvel utilisateur → on crée son compte avec un mot de passe
            if (empty($_POST['mot_de_passe'])) {
                throw new Exception("Veuillez créer un mot de passe pour votre compte.");
            }
            $mdp_hache = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
            $req_insert_user = $db->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, categorie) VALUES (:nom, :prenom, :email, :mdp, :categorie)");
            $req_insert_user->execute([
                'nom'       => $nom,
                'prenom'    => $prenom,
                'email'     => $email,
                'mdp'       => $mdp_hache,
                'categorie' => $categorie
            ]);
            $id_utilisateur = $db->lastInsertId();
        }

        // Vérifier la jauge disponible
        $req_jauge = $db->prepare("SELECT SUM(nb_personnes) as total FROM reservations WHERE salles_id_salle = :salle AND creneaux_id_creneau = :creneau");
        $req_jauge->execute(['salle' => $id_salle, 'creneau' => $id_creneau]);
        $jauge = $req_jauge->fetch();
        $places_prises = $jauge['total'] ?? 0;

        if (($places_prises + $places) > 12) {
            $restantes = 12 - $places_prises;
            throw new Exception("Plus assez de places disponibles. Il reste $restantes place(s) sur ce créneau.");
        }

        // Enregistrer la réservation
        $req_resa = $db->prepare("INSERT INTO reservations (date_reservation, participe_buffet, nb_personnes, utilisateurs_id_utilisateur, creneaux_id_creneau, salles_id_salle) VALUES (:date_resa, :buffet, :places, :id_user, :id_creneau, :id_salle)");
        $req_resa->execute([
            'date_resa'  => date('Y-m-d H:i:s'),
            'buffet'     => $buffet,
            'places'     => $places,
            'id_user'    => $id_utilisateur,
            'id_creneau' => $id_creneau,
            'id_salle'   => $id_salle
        ]);

        // Connecter automatiquement l'utilisateur
        $_SESSION['id']   = $id_utilisateur;
        $_SESSION['nom']  = $nom;
        $_SESSION['role'] = 'visiteur';

        $message_succes = "Merci $prenom ! Ton inscription a bien été enregistrée.";

    } catch (Exception $e) {
        $message_erreur = $e->getMessage();
    }
}

// Récupérer les salles et créneaux depuis la BDD
$salles   = $db->query("SELECT * FROM salles")->fetchAll();
$creneaux = $db->query("SELECT * FROM creneaux ORDER BY date_expo, heure_debut")->fetchAll();
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
  <a href="login.php" class="btn-reservations">Voir mes réservations</a>
</div>

<section class="section-formulaire">
  <h2>Formulaire d'inscription</h2>

  <?php if (!empty($message_succes)): ?>
    <div class="alert-success"><?php echo $message_succes; ?>
      <br><a href="mes-reservations.php" style="color:var(--teal-darker);font-weight:bold;">→ Voir mes réservations</a>
    </div>
  <?php endif; ?>

  <?php if (!empty($message_erreur)): ?>
    <div class="alert-error"><?php echo $message_erreur; ?></div>
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

    <div class="form-group full-width" id="mdp-group">
      <label for="mot_de_passe">Mot de passe <span style="color:var(--text-muted);font-size:12px;">(uniquement pour les nouveaux comptes)</span></label>
      <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Créez un mot de passe">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="categorie">Vous êtes :</label>
        <select id="categorie" name="categorie" required onchange="toggleBuffet()">
          <option value="">Sélectionner...</option>
          <option value="visit">Visiteur·se extérieur</option>
          <option value="etu">Étudiant·e MMI 2 ou 3</option>
          <option value="ens">Enseignant·e</option>
          <option value="pers">Personnel USMB</option>
          <option value="pro">Professionnel·le / partenaire</option>
        </select>
      </div>
      <div class="form-group">
        <label for="places">Nombre de places</label>
        <input type="number" id="places" name="places" min="1" max="12" value="1" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="salle">Salle</label>
        <select id="salle" name="salle" required onchange="updateCreneaux()">
          <option value="">Choisir une salle...</option>
          <?php foreach ($salles as $salle): ?>
            <option value="<?php echo $salle['id_salle']; ?>">
              Salle <?php echo htmlspecialchars($salle['nom_salle']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="creneau">Créneau horaire</label>
        <select id="creneau" name="creneau" required>
          <option value="">Choisissez d'abord une salle</option>
        </select>
      </div>
    </div>

    <p style="font-size:12px;color:var(--text-muted);">⚠ Jauge limitée à 12 personnes par créneau</p>

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
// Créneaux depuis la BDD
const creneaux = <?php echo json_encode($creneaux); ?>;

function updateCreneaux() {
  const select = document.getElementById('creneau');
  select.innerHTML = '<option value="">Sélectionner un créneau...</option>';
  creneaux.forEach(c => {
    const date = new Date(c.date_expo).toLocaleDateString('fr-FR', {weekday:'long', day:'numeric', month:'long'});
    const opt = document.createElement('option');
    opt.value = c.id_creneau;
    opt.textContent = date + ' – ' + c.heure_debut.substring(0,5) + ' → ' + c.heure_fin.substring(0,5);
    select.appendChild(opt);
  });
}

function toggleBuffet() {
  const cat   = document.getElementById('categorie').value;
  const group = document.getElementById('buffet-group');
  group.style.display = (cat === 'etu' || cat === '') ? 'none' : 'flex';
}
</script>

</body>
</html>