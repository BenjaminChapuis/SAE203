<?php
// 1. On se connecte à la base de données XAMPP
require_once 'connexion.php';

// Variables pour afficher un message à l'utilisateur
$message_succes = "";
$message_erreur = "";

// 2. Si l'utilisateur a cliqué sur le bouton "Envoyer et Valider"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération des champs textes avec htmlspecialchars pour la sécurité (éviter les failles XSS)
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $mdp_clair = $_POST['mot_de_passe'];
    $categorie = $_POST['categorie']; // "etu", "ens", "visit", etc.
    
    // Hachage du mot de passe (très important pour les critères d'évaluation)
    $mdp_hache = password_hash($mdp_clair, PASSWORD_DEFAULT);

    // Récupération des nombres et des listes déroulantes
    $places = (int) $_POST['places'];
    $id_salle = (int) $_POST['salle'];
    $id_creneau = (int) $_POST['creneau'];
    
    // Gestion de la case à cocher pour le buffet : si cochée = 1, sinon = 0
    $buffet = isset($_POST['buffet']) ? 1 : 0; 

    try {
        // --- ÉTAPE 1 : ENREGISTRER OU RÉCUPÉRER L'UTILISATEUR ---
        // On vérifie d'abord si cet email existe déjà dans la table utilisateurs
        $req_verif = $db->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = :email");
        $req_verif->execute(['email' => $email]);
        $utilisateur_existant = $req_verif->fetch();

        if ($utilisateur_existant) {
            // S'il existe, on récupère juste son numéro (ID)
            $id_utilisateur = $utilisateur_existant['id_utilisateur'];
        } else {
            // S'il est nouveau, on le crée dans la base
            $req_insert_user = $db->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, categorie) VALUES (:nom, :prenom, :email, :mdp, :categorie)");
            $req_insert_user->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'mdp' => $mdp_hache,
                'categorie' => $categorie
            ]);
            // On récupère le numéro (ID) que XAMPP vient de lui attribuer
            $id_utilisateur = $db->lastInsertId();
        }

        // --- ÉTAPE 2 : ENREGISTRER LA RÉSERVATION ---
        // On récupère la date et l'heure exactes au moment du clic
        $date_reservation = date('Y-m-d H:i:s'); 

        // On insère les données dans la table reservations
        $req_insert_resa = $db->prepare("INSERT INTO reservations (date_reservation, participe_buffet, nb_personnes, utilisateurs_id_utilisateur, creneaux_id_creneau, salles_id_salle) VALUES (:date_resa, :buffet, :places, :id_user, :id_creneau, :id_salle)");
        
        $req_insert_resa->execute([
            'date_resa' => $date_reservation,
            'buffet' => $buffet,
            'places' => $places,
            'id_user' => $id_utilisateur, // Relié à l'utilisateur
            'id_creneau' => $id_creneau,   // Relié au créneau choisi
            'id_salle' => $id_salle        // Relié à la salle choisie
        ]);

        // Message vert qui s'affichera sur la page
        $message_succes = "Merci $prenom ! Ton inscription à l'exposition a bien été enregistrée.";

    } catch (PDOException $e) {
        // En cas de problème avec la base de données, on affiche l'erreur en rouge
        $message_erreur = "Erreur technique : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>E-LLUSION - Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="logo">E-LLUSION</div>
        <nav>
            <a href="#">Accueil</a>
            <a href="presentation.php">Présentation</a>
            <a href="#">Contact</a>
            <a href="inscription.php" class="active">Inscription</a>
        </nav>
    </header>

    <main>
        <section class="bandeau-inscription">
            <h1>Inscription à l'exposition E-LLUSION</h1>
            <p class="info-important">18 et 19 Juin 2026</p>
            <p>IUT de Chambéry - Aile C</p>
            <p>28 Avenue du Lac d'Annecy 73370 Bourget-du-Lac</p>
            <p>8h - 18h</p>
            <p class="info-gratuit">Inscription gratuite</p>
        </section>

        <div class="action-reservations">
            <a href="#" class="btn-reservations">Voir / Modifier mes réservations</a>
        </div>

        <section class="section-formulaire">
            <h2>Formulaire d'inscription</h2>

            <?php if (!empty($message_succes)): ?>
                <div class="alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 25px; text-align: center; font-weight: bold;">
                    <?php echo $message_succes; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($message_erreur)): ?>
                <div class="alert-error" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 25px; text-align: center; font-weight: bold;">
                    <?php echo $message_erreur; ?>
                </div>
            <?php endif; ?>

            <form action="inscription.php" method="POST" class="form-inscription">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prenom</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Adresse mail</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="categorie">Vous êtes :</label>
                        <select id="categorie" name="categorie" required>
                            <option value="">Sélectionner...</option>
                            <option value="visit">Visiteur externe</option>
                            <option value="etu">Étudiant</option>
                            <option value="ens">Enseignant</option>
                            <option value="pers">Personnel IUT</option>
                            <option value="pro">Professionnel</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="places">Nombre de places</label>
                        <input type="number" id="places" name="places" min="1" max="10" value="1" required>
                    </div>
                </div>

                <div class="form-row align-center">
                    <div class="form-group checkbox-group">
                        <label for="buffet">Je participe au buffet</label>
                        <input type="checkbox" id="buffet" name="buffet" value="1">
                    </div>
                    <div class="form-group">
                        <label for="salle">Salle</label>
                        <select id="salle" name="salle" required>
                            <option value="">Choisir une salle...</option>
                            <option value="1">Salle TP11</option>
                            <option value="2">Salle TP12</option>
                            <option value="3">Salle TP21</option>
                            <option value="4">Salle TP22</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="creneau">Créneau Horaire</label>
                    <select id="creneau" name="creneau" required>
                        <option value="">Sélectionner un créneau...</option>
                        <option value="1">Matinée : 8h - 10h</option>
                        <option value="2">Matinée : 10h - 12h</option>
                        <option value="3">Après-midi : 14h - 16h</option>
                        <option value="4">Après-midi : 16h - 18h</option>
                    </select>
                </div>

                <div class="form-submit">
                    <button type="submit" class="btn-submit">Envoyer et Valider</button>
                </div>

            </form>
        </section>
    </main>

    <footer>
        <div class="footer-logo">
            <strong>IUT Chambéry</strong><br>
            <span>MMI Chambéry</span>
        </div>
        <div class="footer-nav">
            <a href="#">Accueil</a>
            <a href="presentation.php">Présentation</a>
            <a href="#">Contact</a>
            <a href="inscription.php">Inscription</a>
        </div>
        <div class="footer-titre">
            E-LLUSION<span class="point-rouge">.</span>
        </div>
    </footer>

</body>
</html>