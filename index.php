<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="index_style.css">
</head>
<body>

<?php include 'header.php'; ?>

<!-- HERO -->
<section class="hero">
  <!-- Remplacement du texte "E" par l'affiche officielle -->
  <div class="hero-visual">
    <img src="Affiche_Cynthia_v2.jpg" alt="Affiche officielle de l'exposition E-LLUSION" style="width: 100%; height: 100%;">
  </div>
  
  <div class="hero-content">
    <h1>E-LLUSION<span class="dot-rouge"></span></h1>
    <p class="sous-titre">Exposition d'œuvres interactives multimédia</p>
    <p>Bienvenue dans E-LLUSION, l'exposition numérique qui trouble vos sens et interroge notre rapport à la technologie. À travers des installations interactives et des créations multimédias conçues par les étudiants MMI de l'IUT de Chambéry, devenez l'acteur principal d'un parcours sensoriel captivant. Laissez-vous surprendre et bousculez vos perceptions.</p>
    <div class="hero-chips">
      <span class="hero-chip"> 18 & 19 juin 2026</span>
      <span class="hero-chip"> IUT Chambéry</span>
      <span class="hero-chip"> Gratuit</span>
    </div>
    <a href="inscription.php" class="btn btn-primary" style="margin-top:8px; width:fit-content;">
      S'inscrire à l'exposition
    </a>
  </div>
</section>

<!-- DATES -->
<section class="section-dates">
  <div class="dates-left">
    <div class="dates-badge">18-19/06</div>
    <p>Jeudi 18 juin : 15h → 20h<br>Vendredi 19 juin : 9h30 → 11h</p>
    <p>Venez découvrir les œuvres interactives créées par les étudiants MMI1 de l'IUT de Chambéry.</p>
  </div>
  <div class="dates-visual">
    <img src="img.jpg" alt="image style" style="width: 100%; height: 100%;">  
  </div>
</section>

<!-- LIEU -->
<section class="section-lieu">
  <div class="lieu-titres">
    <h2>Le-Bourget-Du-Lac</h2>
    <h2>IUT de Chambéry — Aile C</h2>
  </div>
  <div class="lieu-content">
    <div class="lieu-visual">
      <img src="iut_chambery.jpg" alt="image de l'iut de chambery" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
    <div class="lieu-text">
      <p>28 Avenue du Lac d'Annecy<br>73370 Bourget-du-Lac<br><br>
      L'exposition se tient dans les salles 001, 002, 005 et 021 de l'Aile C du département MMI.</p>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>