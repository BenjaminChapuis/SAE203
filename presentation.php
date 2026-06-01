<?php
// ==========================================================================
// LOGIQUE PHP : Gestion du changement de salle
// ==========================================================================

// 1. On regarde quelle salle a été cliquée dans l'URL. 
// Si aucune salle n'est détectée (quand on arrive sur la page), on met 'TP11' par défaut.
$salle_active = isset($_GET['salle']) ? $_GET['salle'] : 'TP11';

// 2. On crée un tableau de données très simple avec le contenu de chaque salle.
// Cela évite de répéter du code HTML pour chaque salle !
$donnees_salles = [
    'TP11' => [
        'titre_salle' => 'salle (...) TP11',
        'oeuvre1_nom' => 'oeuvre 1 (nom)',
        'oeuvre1_desc' => 'Texte de description pour l\'œuvre 1 en salle TP11. Cette installation propose une interaction directe entre le spectateur et son double virtuel.',
        'oeuvre2_nom' => 'oeuvre 2 (nom)',
        'oeuvre2_desc' => 'Texte de description pour l\'œuvre 2 en salle TP11. Une expérience visuelle unique jouant avec les perspectives de l\'exposition E-LLUSION.'
    ],
    'TP12' => [
        'titre_salle' => 'salle (...) TP12',
        'oeuvre1_nom' => 'oeuvre 3 (nom)',
        'oeuvre1_desc' => 'Découvrez l\'œuvre 3 en salle TP12. Une création numérique interactive qui explore la modification de nos sens.',
        'oeuvre2_nom' => 'oeuvre 4 (nom)',
        'oeuvre2_desc' => 'Description de l\'œuvre 4 en salle TP12. Ce projet met en scène un carrousel d\'images altérant notre perception.'
    ],
    'TP21' => [
        'titre_salle' => 'salle (...) TP21',
        'oeuvre1_nom' => 'oeuvre 5 (nom)',
        'oeuvre1_desc' => 'Bienvenue en TP21 pour l\'œuvre 5. Une installation sonore immersive développée à l\'IUT de Chambéry.',
        'oeuvre2_nom' => 'oeuvre 6 (nom)',
        'oeuvre2_desc' => 'Voici l\'œuvre 6 (salle TP21). Une expérience visuelle jouant avec la lumière et les reflets numériques.'
    ],
    'TP22' => [
        'titre_salle' => 'salle (...) TP22',
        'oeuvre1_nom' => 'oeuvre 7 (nom)',
        'oeuvre1_desc' => 'Détails de l\'œuvre 7 située dans la salle TP22. Une œuvre collective qui bouscule l\'espace physique.',
        'oeuvre2_nom' => 'oeuvre 8 (nom)',
        'oeuvre2_desc' => 'Dernière étape avec l\'œuvre 8 en salle TP22. Clôture du parcours immersif de l\'Aile C.'
    ]
];

// 3. On extrait les informations de la salle actuellement sélectionnée
$infos_salle = $donnees_salles[$salle_active];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>E-LLUSION - Présentation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="logo">E-LLUSION</div>
        <nav>
            <a href="#">Accueil</a>
            <a href="#" class="active">Présentation</a>
            <a href="#">Contact</a>
            <a href="#">Inscription</a>
        </nav>
    </header>

    <main>
        
        <section class="intro-expo">
            <div class="intro-texte">
                <h1>E-LLUSION</h1>
                <h2>Explication de l'exposition</h2>
                <p>
                    Comment nos objets numériques altèrent-ils notre perception de la réalité ? 
                    Découvrez les installations immersives créées par les étudiants de l'IUT de Chambéry.
                </p>
            </div>
            <div class="intro-infos">
                <p class="date"><strong>18 et 19 Juin 2026</strong></p>
                <p class="lieu">
                    <strong>IUT de Chambéry - Aile C</strong><br>
                    28 Avenue du Lac d'Annecy<br>
                    73370 Bourget-du-Lac
                </p>
            </div>
        </section>

        <section class="choix-salles">
            <a href="presentation.php?salle=TP11" class="btn-salle <?php echo ($salle_active == 'TP11') ? 'active' : ''; ?>">Salle TP11</a>
            <a href="presentation.php?salle=TP12" class="btn-salle <?php echo ($salle_active == 'TP12') ? 'active' : ''; ?>">Salle TP12</a>
            <a href="presentation.php?salle=TP21" class="btn-salle <?php echo ($salle_active == 'TP21') ? 'active' : ''; ?>">Salle TP21</a>
            <a href="presentation.php?salle=TP22" class="btn-salle <?php echo ($salle_active == 'TP22') ? 'active' : ''; ?>">Salle TP22</a>
        </section>

        <section class="detail-salle">
            <h3><?php echo $infos_salle['titre_salle']; ?></h3>

            <div class="row-oeuvre">
                <div class="texte-oeuvre">
                    <p><?php echo $infos_salle['oeuvre1_desc']; ?></p>
                </div>
                <div class="bloc-image">
                    <span class="badge-oeuvre"><?php echo $infos_salle['oeuvre1_nom']; ?></span>
                    <div class="image-placeholder">photo/carrousel</div>
                </div>
            </div>

            <div class="row-oeuvre reverse">
                <div class="bloc-image">
                    <span class="badge-oeuvre"><?php echo $infos_salle['oeuvre2_nom']; ?></span>
                    <div class="image-placeholder">photo/carrousel</div>
                </div>
                <div class="texte-oeuvre">
                    <p><?php echo $infos_salle['oeuvre2_desc']; ?></p>
                </div>
            </div>

        </section>
    </main>

    <footer>
        <div class="footer-logo">
            <strong>IUT Chambéry</strong><br>
            <span>MMI Chambéry</span>
        </div>
        <div class="footer-nav">
            <a href="#">Accueil</a>
            <a href="#">Présentation</a>
            <a href="#">Contact</a>
            <a href="#">Inscription</a>
        </div>
        <div class="footer-titre">
            E-LLUSION<span class="point-rouge">.</span>
        </div>
    </footer>

</body>
</html>