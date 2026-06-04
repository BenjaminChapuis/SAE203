<?php
session_start();
require_once '../connexion.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message_succes = "";
$message_erreur = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom       = htmlspecialchars($_POST['nom']);
    $prenom    = htmlspecialchars($_POST['prenom']);
    $email     = htmlspecialchars($_POST['email']);
    $categorie = $_POST['categorie'];
    $id_salle  = (int) $_POST['salle'];
    $id_creneau = (int) $_POST['creneau'];
    $nb_places = (int) $_POST['places'];
    $buffet    = isset($_POST['buffet']) ? 1 : 0;

    try {
        // Vérifier ou créer l'utilisateur
        $req_verif = $db->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = :email");
        $req_verif->execute(['email' => $email]);
        $user_existant = $req_verif->fetch();

        if ($user_existant) {
            $id_utilisateur = $user_existant['id_utilisateur'];
        } else {
            // Créer le compte avec un mot de passe temporaire
            $mdp_temp = password_hash('Ellusion2026!', PASSWORD_DEFAULT);
            $req_insert = $db->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, categorie) VALUES (:nom, :prenom, :email, :mdp, :categorie)");
            $req_insert->execute([
                'nom' => $nom, 'prenom' => $prenom,
                'email' => $email, 'mdp' => $mdp_temp,
                'categorie' => $categorie
            ]);
            $id_utilisateur = $db->lastInsertId();
        }

        // Vérifier doublon salle
        $req_doublon = $db->prepare("SELECT id_reservation FROM reservations WHERE utilisateurs_id_utilisateur = :id_user AND salles_id_salle = :id_salle");
        $req_doublon->execute(['id_user' => $id_utilisateur, 'id_salle' => $id_salle]);
        if ($req_doublon->fetch()) {
            throw new Exception("Cet utilisateur a déjà une réservation pour cette salle.");
        }

        // Vérifier jauge
        $req_jauge = $db->prepare("SELECT COALESCE(SUM(nb_personnes), 0) as total FROM reservations WHERE salles_id_salle = :salle AND creneaux_id_creneau = :creneau");
        $req_jauge->execute(['salle' => $id_salle, 'creneau' => $id_creneau]);
        $jauge = $req_jauge->fetch();
        $restantes = 12 - $jauge['total'];

        if (($jauge['total'] + $nb_places) > 12) {
            throw new Exception("Plus assez de places. Il reste $restantes place(s) sur ce créneau.");
        }

        // Créer la réservation
        $req_resa = $db->prepare("INSERT INTO reservations (date_reservation, participe_buffet, nb_personnes, utilisateurs_id_utilisateur, creneaux_id_creneau, salles_id_salle) VALUES (:date_resa, :buffet, :places, :id_user, :id_creneau, :id_salle)");
        $req_resa->execute([
            'date_resa'  => date('Y-m-d H:i:s'),
            'buffet'     => $buffet,
            'places'     => $nb_places,
            'id_user'    => $id_utilisateur,
            'id_creneau' => $id_creneau,
            'id_salle'   => $id_salle
        ]);

        $message_succes = "Réservation créée pour $prenom $nom ($email) !";
        if (!$user_existant) {
            $message_succes .= " Compte créé avec le mot de passe temporaire : Ellusion2026!";
        }

    } catch (Exception $e) {
        $message_erreur = $e->getMessage();
    }
}

$salles   = $db->query("SELECT * FROM salles")->fetchAll();
$creneaux = $db->query("SELECT * FROM creneaux ORDER BY date_expo, heure_debut")->fetchAll();

// Places dispo
$req_dispo = $db->query("SELECT salles_id_salle, creneaux_id_creneau, 12 - COALESCE(SUM(nb_personnes), 0) as places_restantes FROM reservations GROUP BY salles_id_salle, creneaux_id_creneau");
$dispo_raw = $req_dispo->fetchAll();
$dispo = [];
foreach ($dispo_raw as $d) {
    $dispo[$d['salles_id_salle']][$d['creneaux_id_creneau']] = (int)$d['places_restantes'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Créer une réservation</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../admin_style.css">
  <style>
    .admin-wrapper { display: grid; grid-template-columns: 220px 1fr; min-height: calc(100vh - 57px); }
    .admin-sidebar { background: #080c10; border-right: 1px solid rgba(0,255,204,0.12); padding: 32px 20px; display: flex; flex-direction: column; gap: 6px; }
    .sidebar-title { font-family: var(--font-pixel); font-size: 11px; color: #6a9a94; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px; }
    .sidebar-link { display: block; padding: 10px 16px; border-radius: 10px; color: #6a9a94; font-size: 14px; font-weight: 500; transition: all 0.2s; border: 1px solid transparent; }
    .sidebar-link:hover { color: #00ffcc; background: rgba(0,255,204,0.05); border-color: rgba(0,255,204,0.12); }
    .sidebar-link.active { color: #00ffcc; background: rgba(0,255,204,0.08); border-color: rgba(0,255,204,0.28); }
    .sidebar-logout { margin-top: auto; display: block; padding: 10px 16px; border-radius: 10px; color: rgba(255,34,68,0.7); font-size: 14px; transition: all 0.2s; }
    .sidebar-logout:hover { color: #ff2244; background: rgba(255,34,68,0.06); }
    .admin-content { padding: 40px 48px; background: #0b0f13; }
    .admin-title { font-family: var(--font-pixel); font-size: 26px; letter-spacing: 2px; color: #00bbaa; margin-bottom: 32px; }
    .admin-section { background: #111820; border: 1px solid rgba(0,255,204,0.12); border-radius: 18px; padding: 28px; margin-bottom: 24px; }
    .admin-section h2 { font-family: var(--font-pixel); font-size: 16px; color: #00bbaa; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid rgba(0,255,204,0.12); letter-spacing: 1px; }
    .places-info { font-size: 13px; padding: 10px 16px; border-radius: 8px; margin-top: 4px; display: none; }
  </style>
</head>
<body>

<?php include '../header.php'; ?>

<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <p class="sidebar-title">Admin</p>
    <a href="dashboard.php" class="sidebar-link">📊 Tableau de bord</a>
    <a href="reservations.php" class="sidebar-link">📋 Réservations</a>
    <a href="creneaux.php" class="sidebar-link">🕐 Créneaux</a>
    <a href="creer-reservation.php" class="sidebar-link active">➕ Créer réservation</a>
    <a href="../logout.php" class="sidebar-logout">← Déconnexion</a>
  </aside>

  <main class="admin-content">
    <h1 class="admin-title">Créer une réservation</h1>

    <?php if (!empty($message_succes)): ?>
      <div class="alert-success"><?php echo $message_succes; ?></div>
    <?php endif; ?>
    <?php if (!empty($message_erreur)): ?>
      <div class="alert-error"><?php echo $message_erreur; ?></div>
    <?php endif; ?>

    <div class="admin-section">
      <h2>Informations du visiteur</h2>
      <form action="creer-reservation.php" method="POST" class="form-inscription">

        <div class="form-row">
          <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" required placeholder="Nom du visiteur"
                   value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
          </div>
          <div class="form-group">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" required placeholder="Prénom du visiteur"
                   value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
          </div>
        </div>

        <div class="form-group full-width">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required placeholder="email@exemple.fr"
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="categorie">Catégorie</label>
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
            <select id="creneau" name="creneau" required onchange="updatePlacesInfo()">
              <option value="">Choisissez d'abord une salle</option>
            </select>
          </div>
        </div>

        <div id="places-info" class="places-info"></div>

        <div class="form-group-check" id="buffet-group" style="display:none;">
          <input type="checkbox" id="buffet" name="buffet" value="1">
          <label for="buffet">Participe au buffet du jeudi à 18h30</label>
        </div>

        <div class="form-submit">
          <button type="submit" class="btn btn-primary" style="padding:13px 48px;font-size:15px;">
            Créer la réservation
          </button>
          <a href="reservations.php" class="btn-resa" style="margin-left:16px;">Annuler</a>
        </div>

      </form>
    </div>
  </main>
</div>

<?php include '../footer.php'; ?>

<script>
const creneauxData = <?php echo json_encode($creneaux); ?>;
const dispoData    = <?php echo json_encode($dispo); ?>;

function updateCreneaux() {
  const idSalle  = document.getElementById('salle').value;
  const select   = document.getElementById('creneau');
  const infoDiv  = document.getElementById('places-info');

  select.innerHTML = '<option value="">-- Sélectionner un créneau --</option>';
  infoDiv.style.display = 'none';

  if (!idSalle) return;

  creneauxData.forEach(c => {
    const restantes = dispoData[idSalle] && dispoData[idSalle][c.id_creneau] !== undefined
      ? dispoData[idSalle][c.id_creneau] : 12;
    const date = new Date(c.date_expo).toLocaleDateString('fr-FR', {weekday:'long', day:'numeric', month:'long'});
    const opt  = document.createElement('option');
    opt.value  = c.id_creneau;
    if (restantes <= 0) {
      opt.disabled = true;
      opt.style.color = '#ff8899';
      opt.textContent = date + ' – ' + c.heure_debut.substring(0,5) + ' → ' + c.heure_fin.substring(0,5) + ' (COMPLET)';
    } else {
      opt.textContent = date + ' – ' + c.heure_debut.substring(0,5) + ' → ' + c.heure_fin.substring(0,5) + ' (' + restantes + ' places)';
    }
    select.appendChild(opt);
  });
}

function updatePlacesInfo() {
  const idSalle   = document.getElementById('salle').value;
  const idCreneau = document.getElementById('creneau').value;
  const infoDiv   = document.getElementById('places-info');

  if (!idSalle || !idCreneau) { infoDiv.style.display = 'none'; return; }

  const restantes = dispoData[idSalle] && dispoData[idSalle][idCreneau] !== undefined
    ? dispoData[idSalle][idCreneau] : 12;

  infoDiv.style.display = 'block';
  if (restantes <= 0) {
    infoDiv.style.cssText = 'display:block;font-size:13px;padding:10px 16px;border-radius:8px;margin-top:4px;background:rgba(255,34,68,0.08);border:1px solid rgba(255,34,68,0.25);color:#ff8899;';
    infoDiv.textContent = '❌ Ce créneau est complet.';
  } else if (restantes <= 3) {
    infoDiv.style.cssText = 'display:block;font-size:13px;padding:10px 16px;border-radius:8px;margin-top:4px;background:rgba(255,200,0,0.08);border:1px solid rgba(255,200,0,0.25);color:#ffcc44;';
    infoDiv.textContent = '⚠ Il ne reste que ' + restantes + ' place(s) !';
  } else {
    infoDiv.style.cssText = 'display:block;font-size:13px;padding:10px 16px;border-radius:8px;margin-top:4px;background:rgba(0,255,100,0.06);border:1px solid rgba(0,255,100,0.2);color:#44ffaa;';
    infoDiv.textContent = '✅ ' + restantes + ' places disponibles.';
  }
}

function toggleBuffet() {
  const cat   = document.getElementById('categorie').value;
  const group = document.getElementById('buffet-group');
  group.style.display = (cat === 'etu' || cat === '') ? 'none' : 'flex';
}
</script>

</body>
</html>
