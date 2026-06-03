<?php
session_start();
require_once 'connexion.php';

$message_succes = "";
$message_erreur = "";
$connecte = isset($_SESSION['id']);

// Récupérer infos utilisateur connecté
if ($connecte) {
    $req_user = $db->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = :id");
    $req_user->execute(['id' => $_SESSION['id']]);
    $user_info = $req_user->fetch();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom       = htmlspecialchars($_POST['nom']);
    $prenom    = htmlspecialchars($_POST['prenom']);
    $email     = htmlspecialchars($_POST['email']);
    $categorie = $_POST['categorie'];
    $buffet    = isset($_POST['buffet']) ? 1 : 0;

    // Tableaux pour multi-réservations
    $salles   = $_POST['salle'];
    $creneaux = $_POST['creneau'];
    $places   = $_POST['places'];

    try {
        // Vérifier ou créer l'utilisateur
        $req_verif = $db->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = :email");
        $req_verif->execute(['email' => $email]);
        $utilisateur_existant = $req_verif->fetch();

        if ($utilisateur_existant) {
            $id_utilisateur = $utilisateur_existant['id_utilisateur'];
        } else {
            if (empty($_POST['mot_de_passe'])) {
                throw new Exception("Veuillez créer un mot de passe pour votre compte.");
            }
            $mdp_hache = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
            $req_insert_user = $db->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, categorie) VALUES (:nom, :prenom, :email, :mdp, :categorie)");
            $req_insert_user->execute([
                'nom' => $nom, 'prenom' => $prenom,
                'email' => $email, 'mdp' => $mdp_hache, 'categorie' => $categorie
            ]);
            $id_utilisateur = $db->lastInsertId();
        }

        // Vérifier les doublons de salle dans la même soumission
        if (count($salles) !== count(array_unique($salles))) {
            throw new Exception("Vous avez sélectionné la même salle plusieurs fois.");
        }

        // Traiter chaque réservation
        $erreurs = [];
        $succès  = 0;

        foreach ($salles as $i => $id_salle) {
            $id_salle   = (int) $id_salle;
            $id_creneau = (int) $creneaux[$i];
            $nb_places  = (int) $places[$i];

            // Vérifier doublon salle pour cet utilisateur
            $req_doublon = $db->prepare("SELECT id_reservation FROM reservations WHERE utilisateurs_id_utilisateur = :id_user AND salles_id_salle = :id_salle");
            $req_doublon->execute(['id_user' => $id_utilisateur, 'id_salle' => $id_salle]);
            if ($req_doublon->fetch()) {
                $erreurs[] = "Salle $id_salle : vous avez déjà une réservation pour cette salle.";
                continue;
            }

            // Vérifier jauge
            $req_jauge = $db->prepare("SELECT COALESCE(SUM(nb_personnes), 0) as total FROM reservations WHERE salles_id_salle = :salle AND creneaux_id_creneau = :creneau");
            $req_jauge->execute(['salle' => $id_salle, 'creneau' => $id_creneau]);
            $jauge = $req_jauge->fetch();
            $restantes = 12 - $jauge['total'];

            if (($jauge['total'] + $nb_places) > 12) {
                $erreurs[] = "Salle $id_salle : plus assez de places (il reste $restantes place(s)).";
                continue;
            }

            // Insérer la réservation
            $req_resa = $db->prepare("INSERT INTO reservations (date_reservation, participe_buffet, nb_personnes, utilisateurs_id_utilisateur, creneaux_id_creneau, salles_id_salle) VALUES (:date_resa, :buffet, :places, :id_user, :id_creneau, :id_salle)");
            $req_resa->execute([
                'date_resa' => date('Y-m-d H:i:s'),
                'buffet'    => $buffet,
                'places'    => $nb_places,
                'id_user'   => $id_utilisateur,
                'id_creneau'=> $id_creneau,
                'id_salle'  => $id_salle
            ]);
            $succès++;
        }

        $_SESSION['id']     = $id_utilisateur;
        $_SESSION['nom']    = $nom;
        $_SESSION['prenom'] = $prenom;
        $_SESSION['email']  = $email;
        $_SESSION['role']   = 'visiteur';

        if ($succès > 0 && empty($erreurs)) {
            $message_succes = "Merci $prenom ! Tes $succès réservation(s) ont bien été enregistrées.";
        } elseif ($succès > 0 && !empty($erreurs)) {
            $message_succes = "$succès réservation(s) enregistrée(s). Attention : " . implode(' | ', $erreurs);
        } else {
            $message_erreur = implode('<br>', $erreurs);
        }

    } catch (Exception $e) {
        $message_erreur = $e->getMessage();
    }
}

// Récupérer salles et créneaux depuis BDD
$salles_db   = $db->query("SELECT * FROM salles")->fetchAll();
$creneaux_db = $db->query("SELECT * FROM creneaux ORDER BY date_expo, heure_debut")->fetchAll();

// Places dispo
$req_dispo = $db->query("SELECT salles_id_salle, creneaux_id_creneau, 12 - COALESCE(SUM(nb_personnes), 0) as places_restantes FROM reservations GROUP BY salles_id_salle, creneaux_id_creneau");
$dispo_raw = $req_dispo->fetchAll();
$dispo = [];
foreach ($dispo_raw as $d) {
    $dispo[$d['salles_id_salle']][$d['creneaux_id_creneau']] = (int)$d['places_restantes'];
}

// Salles déjà réservées
$salles_deja_reservees = [];
if ($connecte) {
    $req_reservees = $db->prepare("SELECT salles_id_salle FROM reservations WHERE utilisateurs_id_utilisateur = :id");
    $req_reservees->execute(['id' => $_SESSION['id']]);
    foreach ($req_reservees->fetchAll() as $r) {
        $salles_deja_reservees[] = $r['salles_id_salle'];
    }
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
  <a href="login.php" class="btn-reservations">Voir mes réservations</a>
</div>

<section class="section-formulaire">
  <h2>Formulaire d'inscription</h2>

  <?php if (!empty($message_succes)): ?>
    <div class="alert-success"><?php echo $message_succes; ?>
      <br><a href="mes-reservations.php" style="color:#44ffaa;font-weight:bold;">→ Voir mes réservations</a>
    </div>
  <?php endif; ?>

  <?php if (!empty($message_erreur)): ?>
    <div class="alert-error"><?php echo $message_erreur; ?></div>
  <?php endif; ?>

  <form action="inscription.php" method="POST" class="form-inscription">

    <!-- INFOS PERSONNELLES -->
    <?php if ($connecte): ?>
      <!-- Utilisateur connecté : champs pré-remplis et cachés -->
      <div style="background:rgba(0,255,204,0.06);border:1px solid rgba(0,255,204,0.2);border-radius:12px;padding:16px 20px;margin-bottom:8px;font-size:14px;color:var(--text-muted);">
        👤 Connecté en tant que <strong style="color:var(--neon-dim);"><?php echo htmlspecialchars($user_info['prenom'] . ' ' . $user_info['nom']); ?></strong>
        &nbsp;·&nbsp; <a href="logout.php" style="color:#ff8899;font-size:13px;">Se déconnecter</a>
      </div>
      <input type="hidden" name="nom"       value="<?php echo htmlspecialchars($user_info['nom']); ?>">
      <input type="hidden" name="prenom"    value="<?php echo htmlspecialchars($user_info['prenom']); ?>">
      <input type="hidden" name="email"     value="<?php echo htmlspecialchars($user_info['email']); ?>">
      <input type="hidden" name="categorie" value="<?php echo htmlspecialchars($user_info['categorie']); ?>">
    <?php else: ?>
      <!-- Nouvel utilisateur : formulaire complet -->
      <div class="form-row">
        <div class="form-group">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" required placeholder="Votre nom"
                 value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
        </div>
        <div class="form-group">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" required placeholder="Votre prénom"
                 value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
        </div>
      </div>

      <div class="form-group full-width">
        <label for="email">Adresse mail</label>
        <input type="email" id="email" name="email" required placeholder="votre@email.fr"
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      </div>

      <div class="form-group full-width">
        <label for="mot_de_passe">Mot de passe <span style="color:var(--text-muted);font-size:12px;">(compte créé automatiquement)</span></label>
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
          <label>&nbsp;</label>
          <div class="form-group-check" id="buffet-group" style="display:none;">
            <input type="checkbox" id="buffet" name="buffet" value="1">
            <label for="buffet">Je participe au buffet du jeudi à 18h30</label>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- RÉSERVATIONS MULTIPLES -->
    <div style="margin-top:8px;">
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">
        Vous pouvez réserver plusieurs salles en même temps. Chaque salle ne peut être réservée qu'une seule fois.
      </p>

      <div id="reservations-container">
        <!-- Bloc réservation 1 -->
        <div class="resa-bloc" data-index="0">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <p style="font-family:var(--font-pixel);font-size:13px;color:var(--neon-dim);">Réservation #1</p>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Salle</label>
              <select name="salle[]" class="select-salle" required onchange="updateCreneaux(this, 0)">
                <option value="">Choisir une salle...</option>
                <?php foreach ($salles_db as $salle): ?>
                  <?php $deja = in_array($salle['id_salle'], $salles_deja_reservees); ?>
                  <option value="<?php echo $salle['id_salle']; ?>" <?php echo $deja ? 'disabled' : ''; ?>>
                    Salle <?php echo htmlspecialchars($salle['nom_salle']); ?>
                    <?php echo $deja ? '(déjà réservée)' : ''; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Nombre de places</label>
              <input type="number" name="places[]" min="1" max="12" value="1" required>
            </div>
          </div>
          <div class="form-group full-width">
            <label>Créneau horaire</label>
            <select name="creneau[]" class="select-creneau" required onchange="updatePlacesInfo(this, 0)">
              <option value="">Choisissez d'abord une salle</option>
            </select>
          </div>
          <div class="places-info" style="display:none;font-size:13px;padding:10px 16px;border-radius:8px;margin-top:-8px;"></div>
        </div>
      </div>

      <!-- Bouton ajouter une salle -->
      <button type="button" onclick="ajouterSalle()" class="btn btn-outline" style="margin-top:16px;font-size:13px;padding:9px 24px;">
        + Ajouter une autre salle
      </button>
    </div>

    <div class="form-submit" style="margin-top:28px;">
      <button type="submit" class="btn btn-primary" style="padding:13px 48px;font-size:15px;">
        Envoyer et Valider
      </button>
    </div>

  </form>
</section>

<?php include 'footer.php'; ?>

<script>
const creneauxData = <?php echo json_encode($creneaux_db); ?>;
const dispoData    = <?php echo json_encode($dispo); ?>;
const sallesData   = <?php echo json_encode($salles_db); ?>;
const sallesDeja   = <?php echo json_encode($salles_deja_reservees); ?>;

let nbBlocs = 1;

function getSallesUtilisees() {
  const selects = document.querySelectorAll('.select-salle');
  const utilisees = [];
  selects.forEach(s => { if (s.value) utilisees.push(s.value); });
  return utilisees;
}

function updateTousSalleSelects() {
  const utilisees = getSallesUtilisees();
  document.querySelectorAll('.select-salle').forEach(select => {
    const valeurActuelle = select.value;
    Array.from(select.options).forEach(opt => {
      if (!opt.value) return;
      const deja = sallesDeja.includes(parseInt(opt.value));
      const utiliseeParAutre = utilisees.includes(opt.value) && opt.value !== valeurActuelle;
      opt.disabled = deja || utiliseeParAutre;
    });
  });
}

function updateCreneaux(selectSalle, index) {
  updateTousSalleSelects();
  const idSalle    = selectSalle.value;
  const bloc       = selectSalle.closest('.resa-bloc');
  const selectCren = bloc.querySelector('.select-creneau');
  const infoDiv    = bloc.querySelector('.places-info');

  selectCren.innerHTML = '<option value="">-- Sélectionner un créneau --</option>';
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
      opt.textContent = date + ' – ' + c.heure_debut.substring(0,5) + ' → ' + c.heure_fin.substring(0,5) + ' (' + restantes + ' place' + (restantes > 1 ? 's' : '') + ')';
    }
    selectCren.appendChild(opt);
  });
}

function updatePlacesInfo(selectCren, index) {
  const bloc    = selectCren.closest('.resa-bloc');
  const idSalle = bloc.querySelector('.select-salle').value;
  const idCren  = selectCren.value;
  const infoDiv = bloc.querySelector('.places-info');

  if (!idSalle || !idCren) { infoDiv.style.display = 'none'; return; }

  const restantes = dispoData[idSalle] && dispoData[idSalle][idCren] !== undefined
    ? dispoData[idSalle][idCren] : 12;

  infoDiv.style.display = 'block';
  if (restantes <= 0) {
    infoDiv.style.cssText = 'display:block;font-size:13px;padding:10px 16px;border-radius:8px;margin-top:-8px;background:rgba(255,34,68,0.08);border:1px solid rgba(255,34,68,0.25);color:#ff8899;';
    infoDiv.textContent = '❌ Ce créneau est complet.';
  } else if (restantes <= 3) {
    infoDiv.style.cssText = 'display:block;font-size:13px;padding:10px 16px;border-radius:8px;margin-top:-8px;background:rgba(255,200,0,0.08);border:1px solid rgba(255,200,0,0.25);color:#ffcc44;';
    infoDiv.textContent = '⚠ Il ne reste que ' + restantes + ' place' + (restantes > 1 ? 's' : '') + ' !';
  } else {
    infoDiv.style.cssText = 'display:block;font-size:13px;padding:10px 16px;border-radius:8px;margin-top:-8px;background:rgba(0,255,100,0.06);border:1px solid rgba(0,255,100,0.2);color:#44ffaa;';
    infoDiv.textContent = '✅ ' + restantes + ' places disponibles.';
  }
}

function ajouterSalle() {
  if (nbBlocs >= 4) {
    alert('Vous ne pouvez réserver que 4 salles maximum (une par salle).');
    return;
  }

  nbBlocs++;
  const container = document.getElementById('reservations-container');
  const div = document.createElement('div');
  div.className = 'resa-bloc';
  div.dataset.index = nbBlocs - 1;
  div.style.cssText = 'margin-top:20px;padding-top:20px;border-top:1px solid rgba(0,255,204,0.12);';

  let optionsSalles = '<option value="">Choisir une salle...</option>';
  sallesData.forEach(s => {
    const deja = sallesDeja.includes(s.id_salle);
    optionsSalles += `<option value="${s.id_salle}" ${deja ? 'disabled' : ''}>Salle ${s.nom_salle}${deja ? ' (déjà réservée)' : ''}</option>`;
  });

  div.innerHTML = `
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <p style="font-family:'Share Tech Mono',monospace;font-size:13px;color:#00bbaa;">Réservation #${nbBlocs}</p>
      <button type="button" onclick="supprimerBloc(this)" style="background:rgba(255,34,68,0.08);border:1px solid rgba(255,34,68,0.25);color:#ff8899;border-radius:50px;padding:4px 14px;font-size:12px;cursor:pointer;">
        Supprimer
      </button>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Salle</label>
        <select name="salle[]" class="select-salle" required onchange="updateCreneaux(this, ${nbBlocs-1})">
          ${optionsSalles}
        </select>
      </div>
      <div class="form-group">
        <label>Nombre de places</label>
        <input type="number" name="places[]" min="1" max="12" value="1" required>
      </div>
    </div>
    <div class="form-group full-width">
      <label>Créneau horaire</label>
      <select name="creneau[]" class="select-creneau" required onchange="updatePlacesInfo(this, ${nbBlocs-1})">
        <option value="">Choisissez d'abord une salle</option>
      </select>
    </div>
    <div class="places-info" style="display:none;"></div>
  `;

  container.appendChild(div);
  updateTousSalleSelects();
}

function supprimerBloc(btn) {
  btn.closest('.resa-bloc').remove();
  nbBlocs--;
  updateTousSalleSelects();
  // Renumeroter
  document.querySelectorAll('.resa-bloc').forEach((bloc, i) => {
    const titre = bloc.querySelector('p');
    if (titre) titre.textContent = 'Réservation #' + (i + 1);
  });
}

function toggleBuffet() {
  const cat   = document.getElementById('categorie').value;
  const group = document.getElementById('buffet-group');
  if (group) group.style.display = (cat === 'etu' || cat === '') ? 'none' : 'flex';
}
</script>

</body>
</html>