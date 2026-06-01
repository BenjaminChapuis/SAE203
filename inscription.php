<?php
// ==========================================================================
// LOGIQUE PHP : Traitement ultra-simple du formulaire
// ==========================================================================
$message_succes = "";

// On vérifie si le formulaire a été soumis en méthode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // On récupère les données saisies par sécurité
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    
    // On crée un petit message de validation personnalisé à afficher plus bas
    $message_succes = "Merci $prenom $nom, votre inscription a bien été enregistrée !";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>E-LLUSION - Inscription</title>
    <!-- On réutilise la même feuille de style pour garder la cohérence du site -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- EN-TÊTE DU SITE -->
    <header>
        <div class="logo">E-LLUSION</div>
        <nav>
            <a href="#">Accueil</a>
            <a href="presentation.php">Présentation</a>
            <a href="#">Contact</a>
            <a href="inscription.php" class="active">Inscription</a>
        </nav>
    </header>

    <!-- CONTENU PRINCIPAL -->
    <main>
        
        <!-- EN-TÊTE EN BANDEAU MENTHE (Infos Pratiques) -->
        <section class="bandeau-inscription">
            <h1>Inscription à l'exposition E-LLUSION</h1>
            <p class="info-important">18 et 19 Juin 2026</p>
            <p>IUT de Chambéry - Aile C</p>
            <p>28 Avenue du Lac d'Annecy 73370 Bourget-du-Lac</p>
            <p>8h - 18h</p>
            <p class="info-gratuit">Inscription gratuite</p>
        </section>

        <!-- BOUTON CENTRAL : VOIR MES RÉSERVATIONS -->
        <div class="action-reservations">
            <a href="#" class="btn-reservations">Voir mes réservations</a>
        </div>

        <!-- ZONE DU FORMULAIRE D'INSCRIPTION -->
        <section class="section-formulaire">
            <h2>Formulaire d'inscription</h2>

            <!-- Si le formulaire est validé, PHP affiche ce bloc de succès -->
            <?php if (!empty($message_succes)): ?>
                <div class="alert-success"><?php echo $message_succes; ?></div>
            <?php endif; ?>

            <form action="inscription.php" method="POST" class="form-inscription">
                
                <!-- Ligne 1 : Nom et Prénom -->
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

                <!-- Ligne 2 : Adresse mail (Pleine largeur) -->
                <div class="form-group full-width">
                    <label for="email">Adresse mail</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <!-- Ligne 3 : Téléphone et Nombre de places -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="tel">Tel</label>
                        <input type="tel" id="tel" name="tel">
                    </div>
                    <div class="form-group">
                        <label for="places">Nombre de place</label>
                        <input type="number" id="places" name="places" min="1" max="10" value="1" required>
                    </div>
                </div>

                <!-- Ligne 4 : Buffet et Choix de la Salle -->
                <div class="form-row align-center">
                    <div class="form-group checkbox-group">
                        <label for="buffet">Je participe au buffet</label>
                        <input type="checkbox" id="buffet" name="buffet" value="oui">
                    </div>
                    <div class="form-group">
                        <label for="salle">Salle</label>
                        <select id="salle" name="salle" required>
                            <option value="">Choisir une salle...</option>
                            <option value="TP11">Salle TP11</option>
                            <option value="TP12">Salle TP12</option>
                            <option value="TP21">Salle TP21</option>
                            <option value="TP22">Salle TP22</option>
                        </select>
                    </div>
                </div>

                <!-- Ligne 5 : Créneau Horaire (Pleine largeur) -->
                <div class="form-group full-width">
                    <label for="creneau">Créneau Horaire</label>
                    <select id="creneau" name="creneau" required>
                        <option value="">Sélectionner un créneau...</option>
                        <option value="8h-10h">Matinée : 8h - 10h</option>
                        <option value="10h-12h">Matinée : 10h - 12h</option>
                        <option value="14h-16h">Après-midi : 14h - 16h</option>
                        <option value="16h-18h">Après-midi : 16h - 18h</option>
                    </select>
                </div>

                <!-- Bouton d'envoi -->
                <div class="form-submit">
                    <button type="submit" class="btn-submit">Envoyer et Valider</button>
                </div>

            </form>
        </section>
    </main>

    <!-- PIED DE PAGE (Identique aux autres pages) -->
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